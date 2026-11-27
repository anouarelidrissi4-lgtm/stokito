<?php

namespace Tests\Feature;

use App\Models\BonDeChargement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChargementTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_delivery_note_gets_its_loading_slip_with_loaded_quantities(): void
    {
        $this->companyUser('MA', ['bons_chargement_enabled' => true]);
        $c = $this->client('Martin');
        $p = $this->product('FER-10');
        Livewire::test(\App\Livewire\BonLivraisons\Form::class)
            ->set('client_id', $c->id)->set('items.0.product_id', $p->id)->set('items.0.quantity', 10)->set('items.0.unit_price', 5)
            ->call('save')->assertHasNoErrors();
        $bc = BonDeChargement::with('lignes')->sole();
        $this->assertNotNull($bc->bon_livraison_id);
        $this->assertSame(1, $bc->lignes->count());
        $this->assertEquals(10, $bc->lignes[0]->quantite_commandee);
        $this->assertSame('preparation', $bc->status);

        $l = $bc->lignes[0];
        Livewire::test(\App\Livewire\BonsDeChargement\Show::class, ['id' => $bc->id])
            ->set("charge.{$l->id}", '4')->set('chauffeur', 'Karim')->set('vehicule', '12345-A-6')->set('commercial', 'Yassine')->call('save')->assertHasNoErrors();
        $bc->refresh();
        $this->assertSame('partiel', $bc->status);
        $this->assertSame('Yassine', $bc->commercial);
        $this->assertSame('partiel', $bc->lignes()->first()->statut());

        // editing the delivery note keeps what was loaded
        Livewire::test(\App\Livewire\BonLivraisons\Form::class, ['bonLivraisonId' => $bc->bon_livraison_id])->set('items.0.quantity', 12)->call('save')->assertHasNoErrors();
        $this->assertSame(1, BonDeChargement::count());
        $line = $bc->fresh()->lignes()->first();
        $this->assertEquals(12, $line->quantite_commandee);
        $this->assertEquals(4, $line->quantite_chargee);

        Livewire::test(\App\Livewire\BonsDeChargement\Show::class, ['id' => $bc->id])->call('toutCharger')->call('save');
        $this->assertSame('charge', $bc->fresh()->status);

        $this->get(route('bons-de-chargement.pdf', $bc->id))->assertOk();
        $this->get(route('bons-de-chargement.pdf-selection', ['ids' => $bc->id]))->assertOk();
        $this->get(route('bons-de-chargement.index'))->assertOk()->assertSee($bc->numero);
    }
}
