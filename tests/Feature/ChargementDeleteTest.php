<?php

namespace Tests\Feature;

use App\Models\BonDeChargement;
use App\Models\BonLivraison;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** The automatic loading slip of a delivery note can be deleted, and is not created again (2026-11-28). */
class ChargementDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_automatic_slip_can_be_deleted_and_stays_deleted(): void
    {
        $this->companyUser('MA', ['bons_chargement_enabled' => true]);
        $c = $this->client('Martin');
        $p = $this->product('FER-10');
        Livewire::test(\App\Livewire\BonLivraisons\Form::class)
            ->set('client_id', $c->id)->set('items.0.product_id', $p->id)->set('items.0.quantity', 10)->set('items.0.unit_price', 5)
            ->call('save')->assertHasNoErrors();
        $bc = BonDeChargement::sole();
        $this->assertNotNull($bc->bon_livraison_id);

        Livewire::test(\App\Livewire\BonsDeChargement\Index::class)->assertSee('Supprimer '.$bc->numero, false)->call('delete', $bc->id);
        $this->assertSame(0, BonDeChargement::count());
        $this->assertTrue((bool) BonLivraison::sole()->chargement_supprime);

        // opening the list or editing the delivery note does not bring it back
        Livewire::test(\App\Livewire\BonsDeChargement\Index::class);
        Livewire::test(\App\Livewire\BonLivraisons\Form::class, ['bonLivraisonId' => BonLivraison::sole()->id])->set('items.0.quantity', 12)->call('save')->assertHasNoErrors();
        $this->assertSame(0, BonDeChargement::count());
    }
}
