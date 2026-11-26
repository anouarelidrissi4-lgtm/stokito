<?php

namespace Tests\Feature;

use App\Models\BonLivraison;
use App\Models\Facture;
use App\Models\ReglementClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private function bl($client): BonLivraison
    {
        $p = $this->product('FER-10');
        $bl = BonLivraison::create(['client_id' => $client->id, 'numero' => 'BL-00001', 'date' => now(), 'show_prices' => true, 'status' => 'delivered',
            'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0, 'total' => 1200, 'total_ht' => 1000]);
        $bl->items()->create(['product_id' => $p->id, 'quantity' => 10, 'unit_price' => 100, 'tva_rate' => 20]);

        return $bl;
    }

    public function test_payment_on_a_delivery_note_counts_in_the_balance_and_moves_to_the_invoice(): void
    {
        $this->companyUser('MA');
        $c = $this->client('Martin');
        $bl = $this->bl($c);
        $this->assertEquals(1200, $c->fresh()->solde());                 // un-invoiced delivery note is owed

        Livewire::test(\App\Livewire\BonLivraisons\Form::class, ['bonLivraisonId' => $bl->id])
            ->call('openPayment', 'papier')->set('payMode', 'cheque')->set('payMontant', 500)->set('payBanque', 'CIH Bank')->set('payNumero', '123456')->set('payTireur', 'Martin SARL')->set('payNotes', 'Remis par le chauffeur')
            ->call('addPayment')->assertHasNoErrors();
        $r = ReglementClient::sole();
        $this->assertSame($bl->id, $r->bon_livraison_id);
        $this->assertSame('CIH Bank', $r->banque);
        $this->assertSame('Martin SARL', $r->tireur);
        $this->assertSame('Remis par le chauffeur', $r->observation());
        $this->assertEquals(700, $bl->fresh()->resteAPayer());
        $this->assertEquals(700, $c->fresh()->solde());

        $f = Facture::create(['client_id' => $c->id, 'bon_livraison_id' => $bl->id, 'numero' => 'FAC-00001', 'date' => now(), 'due_date' => now()->addDays(30),
            'tva_rate' => 20, 'status' => 'envoyee', 'remise_type' => 'percent', 'remise_value' => 0, 'total_ht' => 1000, 'total_ttc' => 1200, 'montant_paye' => 0]);
        ReglementClient::where('bon_livraison_id', $bl->id)->whereNull('facture_id')->update(['facture_id' => $f->id]);
        $f->refreshStatus();
        $this->assertSame('partiellement_payee', $f->fresh()->status);
        $this->assertEquals(700, $c->fresh()->solde());                  // counted once, through the invoice
    }

    public function test_payments_typed_on_a_new_delivery_note_are_saved_with_it(): void
    {
        $this->companyUser('FR');
        $c = $this->client('Durand');
        $p = $this->product('TUB-40');
        Livewire::test(\App\Livewire\BonLivraisons\Form::class)
            ->set('client_id', $c->id)->set('items.0.product_id', $p->id)->set('items.0.quantity', 2)->set('items.0.unit_price', 50)
            ->call('openPayment', 'cash')->set('payMode', 'especes')->set('payMontant', 60)->set('payNotes', 'Payé au dépôt')->call('addPayment')->assertHasNoErrors()
            ->assertSet('pendingPayments.0.mode', 'especes')
            ->call('save')->assertHasNoErrors();
        $r = ReglementClient::sole();
        $this->assertNotNull($r->bon_livraison_id);
        $this->assertSame('especes', $r->mode);
        $this->assertSame('Payé au dépôt', $r->notes);
        $this->assertSame($c->id, $r->client_id);
    }

    public function test_banks_follow_the_country(): void
    {
        $this->companyUser('FR');
        $this->assertContains('BNP Paribas', \App\Support\Banks::for());
        $this->assertNotContains('Attijariwafa bank', \App\Support\Banks::for());
    }
}
