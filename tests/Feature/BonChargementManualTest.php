<?php

namespace Tests\Feature;

use App\Models\BonDeChargement;
use App\Models\BonLivraison;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BonChargementManualTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_slip_for_several_delivery_notes_merges_the_products(): void
    {
        $this->companyUser('MA', ['bons_chargement_enabled' => true]);
        $c = $this->client('Chantier Nord');
        $fer = Product::create(['name' => 'Fer 12', 'reference' => 'F12', 'purchase_price' => 5, 'sale_price' => 10, 'stock_qty' => 100, 'unit' => 'barre']);
        $tube = Product::create(['name' => 'Tube 40', 'reference' => 'T40', 'purchase_price' => 5, 'sale_price' => 10, 'stock_qty' => 100, 'unit' => 'piece']);
        $bls = [];
        foreach ([[['fer', 10], ['tube', 2]], [['fer', 5]]] as $n => $lines) {
            $bl = BonLivraison::create(['client_id' => $c->id, 'numero' => 'BL-0000'.($n + 1), 'date' => now(), 'status' => 'delivered', 'show_prices' => true, 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0]);
            foreach ($lines as [$p, $q]) {
                $prod = $p === 'fer' ? $fer : $tube;
                $bl->items()->create(['product_id' => $prod->id, 'quantity' => $q, 'unit_price' => 10, 'total' => 10 * $q]);
            }
            $bls[] = $bl->id;
        }

        Livewire::test(\App\Livewire\BonsDeChargement\Index::class)
            ->assertSee('Ajouter un bon de chargement')
            ->call('create')->assertSee('Nouveau bon de chargement')->assertSee('BL-00002')
            ->call('save')->assertHasErrors('selectedBonLivraisons')
            ->set('selectedBonLivraisons', $bls)->set('chauffeur', 'Hassan')->set('vehicule', '12345-A-6')
            ->call('save')->assertHasNoErrors()->assertRedirect();

        $bc = BonDeChargement::whereNull('bon_livraison_id')->sole();
        $this->assertSame('Hassan', $bc->chauffeur);
        $this->assertEquals(15, $bc->lignes()->where('product_id', $fer->id)->value('quantite_commandee'));
        $this->assertEquals(2, $bc->lignes()->where('product_id', $tube->id)->value('quantite_commandee'));
        $this->assertSame(2, $bc->bonLivraisons()->count());

        Livewire::test(\App\Livewire\BonsDeChargement\Show::class, ['id' => $bc->id])->assertSee('BL-00001')->assertSee('BL-00002')->assertSee('Chantier Nord');
        $this->get(route('bons-de-chargement.pdf', $bc->id))->assertOk();

        Livewire::test(\App\Livewire\BonsDeChargement\Index::class)->call('delete', $bc->id);
        $this->assertNull(BonDeChargement::find($bc->id));
    }
}
