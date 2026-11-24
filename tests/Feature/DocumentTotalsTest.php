<?php

namespace Tests\Feature;

use App\Models\Devis;
use App\Models\Facture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentTotalsTest extends TestCase
{
    use RefreshDatabase;

    protected function devisDeuxTaux(): Devis
    {
        $c = $this->client();
        $p1 = $this->product('P1');
        $p2 = $this->product('P2');
        $d = Devis::create(['client_id' => $c->id, 'numero' => 'DEV-T1', 'date' => now(), 'status' => 'accepted', 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 5]);
        $d->items()->create(['product_id' => $p1->id, 'quantity' => 2, 'unit_price' => 100, 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 10, 'total' => 180]);
        $d->items()->create(['product_id' => $p2->id, 'quantity' => 3, 'unit_price' => 50, 'tva_rate' => 5.5, 'remise_type' => 'percent', 'remise_value' => 0, 'total' => 150]);
        $d->recalculateTotals();

        return $d->fresh();
    }

    public function test_quote_with_two_vat_rates_and_discounts(): void
    {
        $this->companyUser();
        $d = $this->devisDeuxTaux();
        $this->assertEquals(313.5, (float) $d->total_ht);
        $this->assertEquals(355.54, (float) $d->total_ttc);
        $this->assertCount(2, $d->vatTotals()['groups']);
    }

    public function test_quote_to_delivery_note_keeps_the_amount_and_discounts(): void
    {
        $this->companyUser();
        $bl = $this->devisDeuxTaux()->toBonLivraison();
        $this->assertEquals(355.54, (float) $bl->fresh()->total);
        $this->assertEquals([90.0, 50.0], $bl->items()->orderBy('id')->pluck('unit_price')->map(fn ($v) => (float) $v)->all());
        $this->assertEquals('percent', $bl->remise_type);
        $this->assertEquals(5, (float) $bl->remise_value);
    }

    public function test_old_lines_without_their_own_rate_use_the_document_rate(): void
    {
        $this->companyUser();
        $p = $this->product('OLD');
        $f = Facture::create(['client_id' => $this->client()->id, 'numero' => 'FAC-OLD', 'date' => now(), 'tva_rate' => 20, 'status' => 'brouillon', 'remise_type' => 'percent', 'remise_value' => 0]);
        $f->items()->create(['product_id' => $p->id, 'quantity' => 2, 'unit_price' => 50, 'total' => 100]); // tva_rate NULL
        $f->recalculateTotals();
        $this->assertEquals(120.0, (float) $f->fresh()->total_ttc);
    }

    public function test_vat_franchise_starts_new_documents_at_zero(): void
    {
        $this->companyUser('FR', ['tva_franchise' => true]);
        Livewire::test(\App\Livewire\Devis\Form::class)->assertSet('tva_rate', 0.0);
    }

    public function test_quote_form_computes_per_rate_totals_live(): void
    {
        $this->companyUser();
        $c = Livewire::test(\App\Livewire\Devis\Form::class)
            ->set('items.0.unit_price', 100)->set('items.0.tva_rate', 20)
            ->call('addItem')->set('items.1.unit_price', 50)->set('items.1.tva_rate', 10)
            ->assertSee('TVA 10 %');
        $this->assertEquals(175.0, $c->instance()->totalTtc);
    }
}
