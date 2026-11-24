<?php

namespace Tests\Feature;

use App\Models\Facture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages(): void
    {
        foreach (['/', '/logiciel-gestion-de-stock-gratuit', '/logiciel-devis-facture-gratuit', '/sitemap.xml', '/up'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_dashboard_with_data(): void
    {
        $this->companyUser();
        $f = Facture::create(['client_id' => $this->client('Rhône Construction')->id, 'numero' => 'FAC-D1', 'date' => now(), 'due_date' => now()->addDays(10), 'tva_rate' => 20, 'status' => 'envoyee', 'remise_type' => 'percent', 'remise_value' => 0]);
        $f->items()->create(['product_id' => $this->product('D1')->id, 'quantity' => 2, 'unit_price' => 50, 'tva_rate' => 20, 'total' => 100]);
        $f->recalculateTotals();

        $this->get('/dashboard')->assertOk()->assertSee('Valeur du stock')->assertSee('Rhône Construction');
    }

    public function test_invoice_pdf_downloads(): void
    {
        $this->companyUser();
        $f = Facture::create(['client_id' => $this->client()->id, 'numero' => 'FAC-P1', 'date' => now(), 'tva_rate' => 20, 'status' => 'brouillon', 'remise_type' => 'percent', 'remise_value' => 0]);
        $f->items()->create(['product_id' => $this->product('PDF1')->id, 'quantity' => 1, 'unit_price' => 10, 'tva_rate' => 5.5, 'total' => 10]);
        $f->recalculateTotals();
        $r = $this->get(route('factures.pdf', $f->id))->assertOk();
        $this->assertStringStartsWith('%PDF', $r->getContent());
    }

    public function test_global_search_finds_documents_and_customers(): void
    {
        $this->companyUser();
        $c = $this->client('Rhône Construction');
        Facture::create(['client_id' => $c->id, 'numero' => 'FAC-G1', 'date' => now(), 'tva_rate' => 20, 'status' => 'brouillon', 'remise_type' => 'percent', 'remise_value' => 0]);
        Livewire::test(\App\Livewire\GlobalSearch::class)->set('q', 'rhône')
            ->assertSee('FAC-G1')->assertSee('Rhône Construction');
    }
}
