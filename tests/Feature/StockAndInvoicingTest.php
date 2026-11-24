<?php

namespace Tests\Feature;

use App\Models\BonLivraison;
use App\Models\Devis;
use App\Models\Facture;
use App\Support\StockSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockAndInvoicingTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_invoice_takes_stock_out_once_and_deleting_gives_it_back(): void
    {
        $this->companyUser();
        $p = $this->product('S1', 40);
        $f = Facture::create(['client_id' => $this->client()->id, 'numero' => 'FAC-S1', 'date' => now(), 'tva_rate' => 20, 'status' => 'brouillon', 'remise_type' => 'percent', 'remise_value' => 0]);
        $f->items()->create(['product_id' => $p->id, 'quantity' => 16, 'unit_price' => 10, 'total' => 160]);
        StockSync::facture($f->fresh());
        StockSync::facture($f->fresh()); // saving twice must not take it out twice
        $this->assertEquals(24, (float) $p->fresh()->stock_qty);
        $f->fresh()->delete();
        $this->assertEquals(40, (float) $p->fresh()->stock_qty);
    }

    public function test_invoice_made_from_a_delivery_note_does_not_move_stock_again(): void
    {
        $this->companyUser();
        $p = $this->product('S2', 40);
        $bl = BonLivraison::create(['client_id' => $this->client()->id, 'numero' => 'BL-S2', 'date' => now(), 'status' => 'delivered', 'show_prices' => true, 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0]);
        $bl->items()->create(['product_id' => $p->id, 'quantity' => 10, 'unit_price' => 10]);
        StockSync::bonLivraison($bl->fresh());
        $this->assertEquals(30, (float) $p->fresh()->stock_qty);

        $f = Facture::create(['client_id' => $bl->client_id, 'bon_livraison_id' => $bl->id, 'numero' => 'FAC-S2', 'date' => now(), 'tva_rate' => 20, 'status' => 'brouillon', 'remise_type' => 'percent', 'remise_value' => 0]);
        $f->items()->create(['product_id' => $p->id, 'quantity' => 10, 'unit_price' => 10, 'total' => 100]);
        StockSync::facture($f->fresh());
        $this->assertEquals(30, (float) $p->fresh()->stock_qty);
    }

    public function test_invoice_straight_from_an_accepted_quote(): void
    {
        $this->companyUser();
        $p = $this->product('S3', 50);
        $d = Devis::create(['client_id' => $this->client()->id, 'numero' => 'DEV-S3', 'date' => now(), 'status' => 'accepted', 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0]);
        $d->items()->create(['product_id' => $p->id, 'quantity' => 4, 'unit_price' => 25, 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 10, 'total' => 90]);
        $d->recalculateTotals();

        Livewire::withQueryParams(['devis' => $d->id])->test(\App\Livewire\Factures\Form::class)
            ->assertSet('devis_id', $d->id)
            ->assertSet('items.0.unit_price', 22.5)
            ->call('save');

        $f = Facture::where('devis_id', $d->id)->firstOrFail();
        $this->assertEquals((float) $d->fresh()->total_ttc, (float) $f->total_ttc);
        $this->assertEquals(46, (float) $p->fresh()->stock_qty);
        $this->assertTrue($d->factures()->exists());
    }
}
