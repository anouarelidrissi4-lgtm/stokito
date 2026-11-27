<?php

namespace App\Support;

use App\Models\Facture;
use App\Models\Societe;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdDocumentPdfBuilder;
use horstoeko\zugferd\ZugferdProfiles;
use horstoeko\zugferd\ZugferdXsdValidator;

/**
 * Factur-X (EN 16931 profile) for invoices of companies established in France (2026-11-28): the structured XML
 * (UN/CEFACT CII) embedded in the invoice PDF, ready for the e-invoicing reform (reception 1/9/2026, issuing for
 * SMEs and micro-companies 1/9/2027 through an approved platform).
 *  - seller: SIRET in societe.ice (SIREN = first 9 digits, scheme 0002), VAT number in identifiant_fiscal;
 *    "franchise en base" (art. 293 B CGI) → VAT category E with exemption reason;
 *  - buyer: the single "SIRET / N° TVA" field is read as SIRET/SIREN (digits) or intra-EU VAT number (FR + 11 characters);
 *  - lines: net unit price, line discount as a line allowance, VAT category per line;
 *  - document discount: one allowance per VAT rate, so every total matches the VAT breakdown (EN 16931 rules BR-CO-*).
 */
class FacturX
{
    public static function applicable(?Societe $s = null): bool
    {
        $s ??= Societe::current();

        return strtoupper((string) ($s->country_code ?? '')) === 'FR';
    }

    /** UN/ECE Recommendation 20 code for a Stokito unit ("pièce", "kg", "m"…). */
    public static function unitCode(?string $unit): string
    {
        $u = mb_strtolower(trim((string) $unit));
        $u = \Illuminate\Support\Str::ascii($u);

        return match (true) {
            in_array($u, ['kg', 'kilo', 'kilos', 'kilogramme', 'kilogrammes'], true) => 'KGM',
            in_array($u, ['g', 'gramme', 'grammes'], true) => 'GRM',
            in_array($u, ['t', 'tonne', 'tonnes'], true) => 'TNE',
            in_array($u, ['m', 'ml', 'metre', 'metres', 'metre lineaire'], true) => 'MTR',
            in_array($u, ['m2', 'm²', 'metre carre', 'metres carres'], true) => 'MTK',
            in_array($u, ['m3', 'm³', 'metre cube', 'metres cubes'], true) => 'MTQ',
            in_array($u, ['l', 'litre', 'litres'], true) => 'LTR',
            in_array($u, ['h', 'heure', 'heures'], true) => 'HUR',
            in_array($u, ['j', 'jour', 'jours'], true) => 'DAY',
            in_array($u, ['lot', 'kit', 'ensemble'], true) => 'SET',
            in_array($u, ['piece', 'pieces', 'pc', 'pcs', 'u', 'unite', 'unites'], true) => 'H87',
            default => 'C62',
        };
    }

    /** ['siren' => …, 'vat' => …] read from a free "SIRET / N° TVA" text. */
    public static function identifiants(?string $raw): array
    {
        $v = strtoupper(preg_replace('/[\s.\-]/', '', (string) $raw));
        $out = ['siren' => null, 'vat' => null];
        if (preg_match('/^[A-Z]{2}[0-9A-Z]{2,13}$/', $v)) {
            $out['vat'] = $v;
            if (preg_match('/^FR[0-9A-Z]{2}(\d{9})$/', $v, $m)) {
                $out['siren'] = $m[1];
            }
        } elseif (preg_match('/^\d{14}$/', $v) || preg_match('/^\d{9}$/', $v)) {
            $out['siren'] = substr($v, 0, 9);
        }

        return $out;
    }

    /** French intra-EU VAT number of a SIREN: FR + key (12 + 3 × (SIREN mod 97)) mod 97 + SIREN. */
    public static function tvaIntracom(string $siren): string
    {
        $key = (12 + 3 * ((int) $siren % 97)) % 97;

        return 'FR'.str_pad((string) $key, 2, '0', STR_PAD_LEFT).$siren;
    }

    /** "12 rue X, 75011 Paris" → [street, postcode, city]. */
    private static function adresse(?string $address, ?string $city): array
    {
        $a = trim((string) $address);
        $c = trim((string) $city);
        $cp = null;
        if (preg_match('/\b(\d{5})\b\s*([^\d,\n]*)$/u', $a, $m)) {
            $cp = $m[1];
            $c = $c ?: trim($m[2]);
            $a = trim(preg_replace('/,?\s*\b\d{5}\b\s*[^\d,\n]*$/u', '', $a));
        } elseif (preg_match('/^(\d{5})\s+(.+)$/u', $c, $m)) {
            [$cp, $c] = [$m[1], $m[2]];
        }

        return [str_replace(["\r", "\n"], ', ', $a) ?: null, $cp, $c ?: null];
    }

    private static function r(float $v): float
    {
        return round($v + 0.0, 2);
    }

    public static function builder(Facture $f): ZugferdDocumentBuilder
    {
        $f->loadMissing(['client', 'items.product']);
        $s = Societe::current();
        $franchise = (bool) $s->tva_franchise;
        $currency = strtoupper($s->currency ?: 'EUR');
        $cat = fn (float $rate) => $franchise ? 'E' : ($rate > 0 ? 'S' : 'Z');
        $exemption = $franchise ? 'TVA non applicable, art. 293 B du CGI' : null;
        $exemptionCode = $franchise ? 'VATEX-FR-FRANCHISE' : null;

        $d = ZugferdDocumentBuilder::createNew(ZugferdProfiles::PROFILE_EN16931);
        $d->setDocumentInformation((string) $f->numero, '380', $f->date->toDateTime(), $currency);
        if ($f->objet) {
            $d->addDocumentNote((string) $f->objet);
        }
        if ($s->penalites_taux) {
            $d->addDocumentNote('Pénalités de retard : '.$s->penalites_taux.' % par an.', null, 'PMD');
        }
        if ($s->indemnite_40) {
            $d->addDocumentNote('Indemnité forfaitaire pour frais de recouvrement en cas de retard de paiement : 40 €.', null, 'PMT');
        }
        if ($s->escompte_neant) {
            $d->addDocumentNote('Pas d’escompte pour paiement anticipé.', null, 'AAB');
        }

        // seller
        [$sRue, $sCp, $sVille] = self::adresse($s->address, $s->city);
        $sId = self::identifiants($s->ice);
        $d->setDocumentSeller((string) ($s->name ?: 'Vendeur'));
        $d->setDocumentSellerAddress($sRue, null, null, $sCp, $sVille, strtoupper($s->country_code ?: 'FR'));
        if ($sId['siren']) {
            $d->setDocumentSellerLegalOrganisation($sId['siren'], '0002', (string) $s->name);
        }
        // BT-31 seller VAT number (required for categories S/Z, BR-S-02): the one entered, else the French number derived from the SIREN;
        // a company under "franchise en base" usually has none: its SIRET goes as tax registration (FC), required for category E (BR-E-02)
        $sVat = self::identifiants($s->identifiant_fiscal)['vat'] ?? $sId['vat'];
        if (! $sVat && ! $franchise && $sId['siren']) {
            $sVat = self::tvaIntracom($sId['siren']);
        }
        if ($sVat) {
            $d->addDocumentSellerTaxRegistration('VA', $sVat);
        } elseif ($franchise && ($siret = preg_replace('/\D/', '', (string) $s->ice))) {
            $d->addDocumentSellerTaxRegistration('FC', $siret);
        }
        if ($s->phone || $s->email) {
            $d->setDocumentSellerContact(null, null, $s->phone ?: null, null, $s->email ?: null);
        }

        // buyer
        $c = $f->client;
        $cName = trim(($c->name ?? 'Client').' '.($c->prenom ?? ''));
        [$cRue, $cCp, $cVille] = self::adresse($c->address ?? null, null);
        $cId = self::identifiants($c->ice ?? null);
        $cPays = $cId['vat'] ? (substr($cId['vat'], 0, 2) === 'EL' ? 'GR' : substr($cId['vat'], 0, 2)) : 'FR';
        $d->setDocumentBuyer($cName, $c->code ?? null);
        $d->setDocumentBuyerAddress($cRue, null, null, $cCp, $cVille, $cPays);
        if ($cId['siren']) {
            $d->setDocumentBuyerLegalOrganisation($cId['siren'], '0002', $cName);
        }
        if ($cId['vat']) {
            $d->addDocumentBuyerTaxRegistration('VA', $cId['vat']);
        }

        // delivery
        if ($f->adresse_livraison) {
            [$lRue, $lCp, $lVille] = self::adresse($f->adresse_livraison, null);
            $d->setDocumentShipTo($cName);
            $d->setDocumentShipToAddress($lRue, null, null, $lCp, $lVille, 'FR');
        }
        $d->setDocumentSupplyChainEvent($f->date->toDateTime());

        // payment
        $iban = strtoupper(preg_replace('/\s+/', '', (string) $s->rib));
        if (preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{10,30}$/', $iban)) {
            $d->addDocumentPaymentMean('58', 'Virement', null, null, null, null, $iban, (string) $s->name);
        }
        $d->addDocumentPaymentTerm('Paiement à réception de facture'.($f->due_date ? ', au plus tard le '.$f->due_date->format('d/m/Y') : '').'.',
            ($f->due_date ?? $f->date)->toDateTime());

        // lines
        $byRate = [];
        $lineTotal = 0.0;
        foreach ($f->items->values() as $i => $it) {
            $rate = (float) ($it->tva_rate ?? $f->tva_rate ?? 0);
            $rate = $franchise ? 0.0 : $rate;
            $net = self::r((float) ($it->total ?? ((float) $it->quantity * (float) $it->unit_price)));
            $brut = self::r((float) $it->quantity * (float) $it->unit_price);
            $d->addNewPosition((string) ($i + 1));
            $d->setDocumentPositionProductDetails((string) ($it->product->name ?? 'Article'), null, $it->product->reference ?? null);
            $d->setDocumentPositionNetPrice(self::r((float) $it->unit_price));
            $d->setDocumentPositionQuantity((float) $it->quantity, self::unitCode($it->product->unit ?? null));
            if ($brut - $net > 0.004) {
                $d->addDocumentPositionAllowanceCharge(self::r($brut - $net), false, null, $brut, '95', 'Remise');
            }
            $d->addDocumentPositionTax($cat($rate), 'VAT', $rate, null, $exemption, $exemptionCode);
            $d->setDocumentPositionLineSummation($net);
            $k = DocTotals::key($rate);
            $byRate[$k] = ($byRate[$k] ?? 0) + $net;
            $lineTotal += $net;
        }
        $lineTotal = self::r($lineTotal);

        // VAT breakdown (after the document discount, same rounding as the PDF) and one allowance per rate for that discount
        $lines = [];
        foreach ($byRate as $k => $sum) {
            $lines[] = [$sum, (float) $k];
        }
        $groups = DocTotals::compute($lines, (float) $f->montantRemise())['groups'];
        $basisTotal = 0.0;
        $taxTotal = 0.0;
        $allowanceTotal = 0.0;
        foreach ($groups as $g) {
            $rate = (float) $g['rate'];
            $sum = self::r((float) $byRate[DocTotals::key($rate)]);
            $base = self::r((float) $g['base']);
            $tax = $franchise ? 0.0 : self::r((float) $g['tva']);
            $allow = self::r($sum - $base);
            if ($allow > 0.004) {
                $d->addDocumentAllowanceCharge($allow, false, $cat($rate), 'VAT', $rate, null, null, null, null, null, '95', 'Remise');
                $allowanceTotal += $allow;
            }
            $d->addDocumentTax($cat($rate), 'VAT', $base, $tax, $rate, $exemption, $exemptionCode);
            $basisTotal += $base;
            $taxTotal += $tax;
        }
        $basisTotal = self::r($basisTotal);
        $taxTotal = self::r($taxTotal);
        $grand = self::r($basisTotal + $taxTotal);
        $d->setDocumentSummation($grand, $grand, $lineTotal, 0.0, self::r($allowanceTotal), $basisTotal, $taxTotal);

        return $d;
    }

    public static function xml(Facture $f): string
    {
        return self::builder($f)->getContent();
    }

    /** XSD errors of the generated XML (empty = valid against the EN 16931 schema). */
    public static function erreursXsd(Facture $f): array
    {
        $v = new ZugferdXsdValidator(self::builder($f));
        $v->validate();

        return $v->validationErrors();
    }

    /** The invoice PDF with its Factur-X XML embedded (PDF/A-3). */
    public static function pdf(Facture $f, string $pdf): string
    {
        return ZugferdDocumentPdfBuilder::fromPdfString(self::builder($f), $pdf)->generateDocument()->downloadString();
    }

    /**
     * Bytes of the invoice PDF to hand out: Factur-X for a company in France, the plain PDF otherwise.
     * Never blocks an invoice: if the embedding fails, the plain PDF is returned and the error is logged.
     */
    public static function output(Facture $f, $dompdf): string
    {
        $raw = $dompdf->output();
        if (! self::applicable()) {
            return $raw;
        }
        try {
            return self::pdf($f, $raw);
        } catch (\Throwable $e) {
            report($e);

            return $raw;
        }
    }

    /** HTTP response for the invoice PDF ("attachment" = download, "inline" = open in the browser). */
    public static function response(Facture $f, $dompdf, string $disposition = 'attachment')
    {
        $name = preg_replace('/[^A-Za-z0-9._-]/', '-', (string) $f->numero).'.pdf';

        return response(self::output($f, $dompdf), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$name.'"',
        ]);
    }
}
