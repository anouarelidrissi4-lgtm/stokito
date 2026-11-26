<?php

namespace Tests\Feature;

use App\Models\BonLivraison;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlPdfSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function bl($client, string $num): BonLivraison
    {
        $p = $this->product('P-'.$num);
        $bl = BonLivraison::create(['client_id' => $client->id, 'numero' => $num, 'date' => now(), 'show_prices' => true, 'status' => 'delivered',
            'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0, 'total' => 120, 'total_ht' => 100]);
        $bl->items()->create(['product_id' => $p->id, 'quantity' => 1, 'unit_price' => 100, 'tva_rate' => 20]);

        return $bl;
    }

    public function test_selected_delivery_notes_print_in_one_pdf_of_the_company_only(): void
    {
        $this->companyUser('MA');
        $c = $this->client('Martin');
        $a = $this->bl($c, 'BL-00001');
        $b = $this->bl($c, 'BL-00002');

        $html = view('pdf.documents', ['societe' => \App\Models\Societe::current(),
            'docs' => [\App\Support\DocumentPdf::bonLivraisonData($a), \App\Support\DocumentPdf::bonLivraisonData($b)]])->render();
        $this->assertStringContainsString('BL-00001', $html);
        $this->assertStringContainsString('BL-00002', $html);
        $this->assertSame(1, substr_count($html, 'page-break-after'));

        $r = $this->get(route('bon-livraisons.pdf-selection', ['ids' => $a->id.','.$b->id]));
        $r->assertOk();
        $this->assertStringContainsString('application/pdf', $r->headers->get('content-type'));
        $this->get(route('bon-livraisons.pdf', $a->id))->assertOk();

        $this->companyUser('FR');                                   // another company sees none of them
        $this->get(route('bon-livraisons.pdf-selection', ['ids' => $a->id.','.$b->id]))->assertNotFound();
    }

    public function test_selected_quotes_and_invoices_print_in_one_pdf(): void
    {
        $this->companyUser('FR');
        $c = $this->client('Durand');
        $ids = [];
        foreach (['DEV-00001', 'DEV-00002'] as $n) {
            $ids[] = \App\Models\Devis::create(['client_id' => $c->id, 'numero' => $n, 'date' => now(), 'status' => 'sent', 'tva_rate' => 20,
                'total_ht' => 100, 'total_ttc' => 120, 'remise_type' => 'percent', 'remise_value' => 0])->id;
        }
        $this->get(route('devis.pdf-selection', ['ids' => implode(',', $ids)]))->assertOk();
        $f = \App\Models\Facture::create(['client_id' => $c->id, 'numero' => 'FAC-00001', 'date' => now(), 'due_date' => now()->addDays(30), 'tva_rate' => 20,
            'status' => 'envoyee', 'remise_type' => 'percent', 'remise_value' => 0, 'total_ht' => 100, 'total_ttc' => 120, 'montant_paye' => 0]);
        $r = $this->get(route('factures.pdf-selection', ['ids' => $f->id]));
        $r->assertOk();
        $this->assertStringContainsString('application/pdf', $r->headers->get('content-type'));
        $this->get(route('factures.pdf', $f->id))->assertOk();
        $this->get(route('factures.pdf-selection', ['ids' => '999999']))->assertNotFound();
    }
}
