<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Facture;
use App\Models\Product;
use App\Support\DocumentPdf;
use App\Support\FacturX;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Factur-X EN 16931 (2026-11-28): XML valid against the schema, totals consistent with the PDF, XML embedded in the PDF. */
class FacturXTest extends TestCase
{
    use RefreshDatabase;

    private function facture(array $societe = []): Facture
    {
        $this->companyUser('FR', array_merge([
            'name' => 'Atelier Durand SARL', 'ice' => '732 829 320 00074', 'identifiant_fiscal' => 'FR44732829320',
            'address' => "12 rue des Lilas\n75011 Paris", 'phone' => '01 23 45 67 89', 'email' => 'contact@durand.test',
            'rib' => 'FR76 3000 6000 0112 3456 7890 189', 'penalites_taux' => 10, 'indemnite_40' => true, 'escompte_neant' => true,
        ], $societe));
        $c = Client::create(['name' => 'Boulangerie Martin', 'ice' => 'FR13542107651', 'address' => '3 place du Marché, 69002 Lyon']);
        $p1 = Product::create(['reference' => 'TOLE-2', 'name' => 'Tôle acier 2 mm', 'unit' => 'kg', 'purchase_price' => 1, 'sale_price' => 2, 'stock_qty' => 500]);
        $p2 = Product::create(['reference' => 'LIV', 'name' => 'Livre technique', 'unit' => 'pièce', 'purchase_price' => 10, 'sale_price' => 20, 'stock_qty' => 50]);
        $f = Facture::create(['client_id' => $c->id, 'numero' => 'FAC-2026-0042', 'date' => '2026-11-20', 'due_date' => '2026-12-20', 'tva_rate' => 20,
            'status' => 'brouillon', 'remise_type' => 'percent', 'remise_value' => 5, 'adresse_livraison' => 'Chantier, 10 avenue Foch, 69006 Lyon']);
        // 2 × 100 − 10 % = 180 at 20 %, 3 × 50 = 150 at 5.5 %, then 5 % on the whole invoice
        $f->items()->create(['product_id' => $p1->id, 'quantity' => 2, 'unit_price' => 100, 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 10, 'total' => 180]);
        $f->items()->create(['product_id' => $p2->id, 'quantity' => 3, 'unit_price' => 50, 'tva_rate' => 5.5, 'remise_type' => 'percent', 'remise_value' => 0, 'total' => 150]);
        $f->recalculateTotals();

        return $f->fresh(['client', 'items.product']);
    }

    private function xpath(string $xml): \DOMXPath
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $x = new \DOMXPath($dom);
        $x->registerNamespace('rsm', 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100');
        $x->registerNamespace('ram', 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100');
        $x->registerNamespace('udt', 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100');

        return $x;
    }

    public function test_identifiers_and_units_are_read(): void
    {
        $this->assertSame(['siren' => '732829320', 'vat' => null], FacturX::identifiants('732 829 320 00074'));
        $this->assertSame(['siren' => '542107651', 'vat' => 'FR13542107651'], FacturX::identifiants('fr 13 542107651'));
        $this->assertSame(['siren' => null, 'vat' => null], FacturX::identifiants(''));
        $this->assertSame('KGM', FacturX::unitCode('Kg'));
        $this->assertSame('H87', FacturX::unitCode('pièce'));
        $this->assertSame('MTK', FacturX::unitCode('m²'));
        $this->assertSame('C62', FacturX::unitCode(null));
    }

    public function test_xml_is_valid_en16931_and_matches_the_invoice_totals(): void
    {
        $f = $this->facture();
        $this->assertSame([], FacturX::erreursXsd($f));

        $x = $this->xpath(FacturX::xml($f));
        $v = fn (string $q) => trim((string) $x->evaluate("string($q)"));
        $sum = '//ram:SpecifiedTradeSettlementHeaderMonetarySummation';

        $this->assertSame('urn:cen.eu:en16931:2017', $v('//ram:GuidelineSpecifiedDocumentContextParameter/ram:ID'));
        $this->assertSame('FAC-2026-0042', $v('//rsm:ExchangedDocument/ram:ID'));
        $this->assertSame('380', $v('//rsm:ExchangedDocument/ram:TypeCode'));
        $this->assertSame('732829320', $v('//ram:SellerTradeParty/ram:SpecifiedLegalOrganization/ram:ID'));
        $this->assertSame('FR44732829320', $v('//ram:SellerTradeParty/ram:SpecifiedTaxRegistration/ram:ID'));
        $this->assertSame('75011', $v('//ram:SellerTradeParty/ram:PostalTradeAddress/ram:PostcodeCode'));
        $this->assertSame('542107651', $v('//ram:BuyerTradeParty/ram:SpecifiedLegalOrganization/ram:ID'));
        $this->assertSame('FR13542107651', $v('//ram:BuyerTradeParty/ram:SpecifiedTaxRegistration/ram:ID'));
        $this->assertSame('FR7630006000011234567890189', $v('//ram:PayeePartyCreditorFinancialAccount/ram:IBANID'));
        $this->assertSame('KGM', $v('(//ram:BilledQuantity)[1]/@unitCode'));

        // 330 net lines, 5 % global discount = 16.50 → 313.50 HT; VAT 171 × 20 % + 142.50 × 5.5 %
        $vt = $f->vatTotals();
        $this->assertEquals(330.0, (float) $v("$sum/ram:LineTotalAmount"));
        $this->assertEquals(16.5, (float) $v("$sum/ram:AllowanceTotalAmount"));
        $this->assertEquals($vt['ht'], (float) $v("$sum/ram:TaxBasisTotalAmount"));
        $this->assertEquals($vt['tva'], (float) $v("$sum/ram:TaxTotalAmount"));
        $this->assertEquals($vt['ttc'], (float) $v("$sum/ram:GrandTotalAmount"));
        $this->assertEquals((float) $f->total_ttc, (float) $v("$sum/ram:DuePayableAmount"));
        $this->assertEquals(2, $x->evaluate('count(//ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax)'));
        $this->assertEquals(20.0, (float) $v('(//ram:SpecifiedTradeAllowanceCharge/ram:ActualAmount)[1]'));   // line discount
        $this->assertBusinessRules($x);
    }

    /** The EN 16931 calculation rules (the schematron ones a receiving platform checks first). */
    private function assertBusinessRules(\DOMXPath $x): void
    {
        $n = fn (string $q, $ctx = null) => (float) $x->evaluate("string($q)", $ctx);
        $sum = '//ram:SpecifiedTradeSettlementHeaderMonetarySummation';
        $lines = 0.0;
        foreach ($x->query('//ram:IncludedSupplyChainTradeLineItem') as $li) {
            $net = $n('.//ram:SpecifiedTradeSettlementLineMonetarySummation/ram:LineTotalAmount', $li);
            $allow = 0.0;
            foreach ($x->query('.//ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge[ram:ChargeIndicator/udt:Indicator="false"]/ram:ActualAmount', $li) as $a) {
                $allow += (float) $a->nodeValue;
            }
            $this->assertEqualsWithDelta($n('.//ram:BilledQuantity', $li) * $n('.//ram:NetPriceProductTradePrice/ram:ChargeAmount', $li) - $allow, $net, 0.001, 'line net');
            $lines += $net;
        }
        $this->assertEqualsWithDelta($lines, $n("$sum/ram:LineTotalAmount"), 0.001, 'BR-CO-10');
        $this->assertEqualsWithDelta($n("$sum/ram:LineTotalAmount") - $n("$sum/ram:AllowanceTotalAmount") + $n("$sum/ram:ChargeTotalAmount"), $n("$sum/ram:TaxBasisTotalAmount"), 0.001, 'BR-CO-13');
        $this->assertEqualsWithDelta($n("$sum/ram:TaxBasisTotalAmount") + $n("$sum/ram:TaxTotalAmount"), $n("$sum/ram:GrandTotalAmount"), 0.001, 'BR-CO-15');
        $tax = 0.0;
        foreach ($x->query('//ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax') as $t) {
            $rate = $n('ram:RateApplicablePercent', $t);
            $cat = $x->evaluate('string(ram:CategoryCode)', $t);
            $lineBase = (float) $x->evaluate("sum(//ram:SpecifiedLineTradeSettlement[ram:ApplicableTradeTax/ram:RateApplicablePercent=$rate and ram:ApplicableTradeTax/ram:CategoryCode='$cat']/ram:SpecifiedTradeSettlementLineMonetarySummation/ram:LineTotalAmount)");
            $docAllow = (float) $x->evaluate("sum(//ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge[ram:CategoryTradeTax/ram:RateApplicablePercent=$rate and ram:CategoryTradeTax/ram:CategoryCode='$cat']/ram:ActualAmount)");
            $this->assertEqualsWithDelta($lineBase - $docAllow, $n('ram:BasisAmount', $t), 0.001, "BR-$cat-08 ($rate %)");
            $this->assertEqualsWithDelta(round($n('ram:BasisAmount', $t) * $rate / 100, 2), $n('ram:CalculatedAmount', $t), 0.001, "BR-$cat-09 ($rate %)");
            $tax += $n('ram:CalculatedAmount', $t);
        }
        $this->assertEqualsWithDelta($tax, $n("$sum/ram:TaxTotalAmount"), 0.001, 'BR-CO-14');
        $this->assertNotSame('', $x->evaluate('string(//ram:SellerTradeParty/ram:SpecifiedTaxRegistration/ram:ID)'), 'BR-S-02 / BR-E-02');
    }

    public function test_franchise_en_base_uses_exempt_category(): void
    {
        $f = $this->facture(['tva_franchise' => true]);
        $this->assertSame([], FacturX::erreursXsd($f));
        $x = $this->xpath(FacturX::xml($f));
        $this->assertSame('E', trim($x->evaluate('string(//ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:CategoryCode)')));
        $this->assertStringContainsString('293 B', $x->evaluate('string(//ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:ExemptionReason)'));
        $this->assertEquals(0.0, (float) $x->evaluate('string(//ram:TaxTotalAmount)'));
        $this->assertBusinessRules($x);
    }

    public function test_seller_vat_number_derived_or_siret_registration(): void
    {
        $this->assertSame('FR44732829320', FacturX::tvaIntracom('732829320'));

        // VAT number left empty: derived from the SIREN
        $f = $this->facture(['identifiant_fiscal' => null]);
        $x = $this->xpath(FacturX::xml($f));
        $this->assertSame('FR44732829320', $x->evaluate('string(//ram:SellerTradeParty/ram:SpecifiedTaxRegistration/ram:ID[@schemeID="VA"])'));
    }

    public function test_franchise_without_vat_number_gives_the_siret(): void
    {
        $f = $this->facture(['tva_franchise' => true, 'identifiant_fiscal' => null]);
        $this->assertSame([], FacturX::erreursXsd($f));
        $x = $this->xpath(FacturX::xml($f));
        $this->assertSame('73282932000074', $x->evaluate('string(//ram:SellerTradeParty/ram:SpecifiedTaxRegistration/ram:ID[@schemeID="FC"])'));
        $this->assertBusinessRules($x);
    }

    public function test_pdf_embeds_the_xml_and_xml_route(): void
    {
        $f = $this->facture();
        $pdf = FacturX::output($f, DocumentPdf::facture($f));
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertStringContainsString('factur-x.xml', $pdf);

        $this->get(route('factures.pdf', $f->id))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $r = $this->get(route('factures.xml', $f->id))->assertOk();
        $this->assertStringContainsString('FAC-2026-0042', $r->getContent());
    }

    public function test_companies_outside_france_keep_the_plain_pdf(): void
    {
        $this->companyUser('MA');
        $this->assertFalse(FacturX::applicable());
        $c = Client::create(['name' => 'Client Maroc']);
        $f = Facture::create(['client_id' => $c->id, 'numero' => 'FAC-MA-1', 'date' => now(), 'tva_rate' => 20, 'status' => 'brouillon', 'remise_type' => 'percent', 'remise_value' => 0]);
        $pdf = FacturX::output($f, DocumentPdf::facture($f));
        $this->assertStringNotContainsString('factur-x.xml', $pdf);
    }
}
