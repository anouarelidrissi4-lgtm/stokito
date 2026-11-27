<?php

namespace Tests\Feature;

use App\Models\ChequeEffetFournisseur;
use App\Models\CommandeFournisseur;
use App\Models\Fournisseur;
use App\Models\MouvementBancaire;
use App\Models\Product;
use App\Models\ReglementFournisseur;
use App\Models\TresorerieCompte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommandeFournisseurPaymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_typed_on_a_new_order_are_saved_with_it(): void
    {
        $this->companyUser('MA');
        $f = Fournisseur::create(['name' => 'Acier Nord']);
        $p = Product::create(['name' => 'Tube 40', 'reference' => 'T40', 'purchase_price' => 50, 'sale_price' => 70, 'stock_qty' => 0, 'unit' => 'piece']);
        $caisse = TresorerieCompte::create(['name' => 'Caisse', 'type' => 'caisse', 'initial_balance' => 0, 'is_active' => true]);

        Livewire::test(\App\Livewire\CommandeFournisseurs\Form::class)
            ->set('fournisseur_id', $f->id)->set('tva_rate', 0)
            ->set('items', [['product_id' => $p->id, 'quantity' => 10, 'unit_price' => 50]])
            ->assertSee('Ils seront enregistrés avec le bon de commande.')
            ->call('openPayment', 'cash')->set('payMode', 'transfert')->set('payMontant', 200)->set('payCompte', $caisse->id)->set('payNotes', 'Wafacash')->call('addPayment')
            ->call('openPayment', 'papier')->set('payMontant', 300)->set('payNumero', '778899')->set('payBanque', 'Attijariwafa bank')->set('payTireur', 'Acier Nord SARL')->call('addPayment')
            ->assertHasNoErrors()
            ->call('save')->assertHasNoErrors();

        $cf = CommandeFournisseur::sole();
        $this->assertSame(2, $cf->reglements()->count());
        $this->assertEquals(500, $cf->reglements()->sum('montant'));
        $this->assertSame('Wafacash', ReglementFournisseur::where('mode', 'transfert')->value('notes'));
        $this->assertSame(1, MouvementBancaire::where('reference_type', 'reglement_fournisseur')->count());
        $ch = ChequeEffetFournisseur::sole();
        $this->assertSame('778899', $ch->numero_cheque);
        $this->assertSame('Attijariwafa bank', $ch->banque);

        // editing: cheque becomes a virement → the pending cheque goes away; amount and treasury follow
        $cheque = ReglementFournisseur::where('mode', 'cheque')->value('id');
        Livewire::test(\App\Livewire\CommandeFournisseurs\Form::class, ['commandeId' => $cf->id])
            ->call('editPayment', $cheque)->assertSet('payGroup', 'papier')->assertSet('payNumero', '778899')->assertSee('Modifier le règlement')
            ->set('payMode', 'virement')->set('payMontant', 250)->set('payCompte', $caisse->id)->call('addPayment')->assertHasNoErrors();
        $this->assertSame('virement', ReglementFournisseur::find($cheque)->mode);
        $this->assertEquals(250, ReglementFournisseur::find($cheque)->montant);
        $this->assertSame(0, ChequeEffetFournisseur::count());
        $this->assertEquals(250, MouvementBancaire::where('reference_type', 'reglement_fournisseur')->where('reference_id', $cheque)->sole()->montant);
        ReglementFournisseur::whereKey($cheque)->update(['mode' => 'cheque']);

        // on the saved order a payment is recorded at once, and deleting it cleans up treasury and cheque
        $lw = Livewire::test(\App\Livewire\CommandeFournisseurs\Form::class, ['commandeId' => $cf->id])->assertSee('Transfert cash')->assertSee('778899');
        $lw->call('removePayment', ReglementFournisseur::where('mode', 'transfert')->value('id'));
        $lw->call('removePayment', ReglementFournisseur::where('mode', 'cheque')->value('id'));
        $this->assertSame(0, ReglementFournisseur::count());
        $this->assertSame(0, MouvementBancaire::where('reference_type', 'reglement_fournisseur')->count());
        $this->assertSame(0, ChequeEffetFournisseur::count());
    }

    public function test_bl_payment_can_be_edited_before_and_after_saving(): void
    {
        $this->companyUser('MA');
        $c = $this->client('Bati Sud');
        $caisse = TresorerieCompte::create(['name' => 'Caisse', 'type' => 'caisse', 'initial_balance' => 0, 'is_active' => true]);
        $p = Product::create(['name' => 'Fer 12', 'reference' => 'F12', 'purchase_price' => 5, 'sale_price' => 10, 'stock_qty' => 100, 'unit' => 'piece']);

        $lw = Livewire::test(\App\Livewire\BonLivraisons\Form::class)
            ->assertSee('Ajouter un règlement')
            ->call('openPayment', 'versement')->assertSet('payGroup', 'cash')->assertSet('payMode', 'versement')
            ->set('payMontant', 40)->call('addPayment')
            ->call('editPayment', 0)->assertSet('payMontant', 40.0)->set('payMontant', 55)->set('payNotes', 'au dépôt')->call('addPayment')
            ->assertHasNoErrors();
        $this->assertSame(1, count($lw->get('pendingPayments')));
        $this->assertEquals(55, $lw->get('pendingPayments')[0]['montant']);

        $r = \App\Models\ReglementClient::create(['client_id' => $c->id, 'montant' => 30, 'date' => now(), 'mode' => 'especes']);
        $bl = \App\Models\BonLivraison::create(['client_id' => $c->id, 'numero' => 'BL-00001', 'date' => now(), 'status' => 'delivered', 'show_prices' => true, 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0, 'total' => 100]);
        $r->update(['bon_livraison_id' => $bl->id]);
        Livewire::test(\App\Livewire\BonLivraisons\Form::class, ['bonLivraisonId' => $bl->id])
            ->call('editPayment', $r->id)->assertSet('payMode', 'especes')
            ->set('payMontant', 45)->set('payCompte', $caisse->id)->call('addPayment')->assertHasNoErrors();
        $this->assertEquals(45, $r->fresh()->montant);
        $this->assertSame(1, MouvementBancaire::where('reference_type', 'reglement_client')->where('reference_id', $r->id)->count());
    }
}
