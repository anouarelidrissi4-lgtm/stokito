<?php

namespace Tests\Feature;

use App\Models\BonLivraison;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\MouvementBancaire;
use App\Models\ReglementClient;
use App\Models\TresorerieCompte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesListsTest extends TestCase
{
    use RefreshDatabase;

    private function facture(float $ttc, string $date, string $num): Facture
    {
        $c = \App\Models\Client::first() ?? $this->client('Bâti Plus');

        return Facture::create(['client_id' => $c->id, 'numero' => $num, 'date' => $date, 'due_date' => now()->addDays(30), 'tva_rate' => 20,
            'status' => 'envoyee', 'remise_type' => 'percent', 'remise_value' => 0, 'total_ht' => round($ttc / 1.2, 2), 'total_ttc' => $ttc, 'montant_paye' => 0]);
    }

    public function test_devis_date_filter_and_totals(): void
    {
        $this->companyUser('FR');
        $c = $this->client('Martin');
        foreach ([['DEV-00001', '2026-10-05', 100, 'accepted'], ['DEV-00002', '2026-11-10', 200, 'sent']] as [$n, $d, $t, $st]) {
            Devis::create(['client_id' => $c->id, 'numero' => $n, 'date' => $d, 'status' => $st, 'tva_rate' => 20, 'total_ht' => $t / 1.2, 'total_ttc' => $t, 'remise_type' => 'percent', 'remise_value' => 0]);
        }
        Livewire::test(\App\Livewire\Devis\Index::class)
            ->assertViewHas('totals', fn ($t) => $t['Devis'] === '2')
            ->set('dateStart', '2026-11-01')
            ->assertSee('DEV-00002')->assertDontSee('DEV-00001')
            ->assertViewHas('totals', fn ($t) => $t['Devis'] === '1');
        $this->get(route('devis.print', ['dateStart' => '2026-11-01']))->assertOk()->assertSee('DEV-00002')->assertDontSee('DEV-00001');
    }

    public function test_factures_totals_and_client_code_search(): void
    {
        $this->companyUser('FR');
        $f = $this->facture(120, '2026-11-02', 'FAC-00001');
        $this->facture(60, '2026-11-03', 'FAC-00002');
        ReglementClient::create(['client_id' => $f->client_id, 'facture_id' => $f->id, 'montant' => 50, 'date' => '2026-11-04', 'mode' => 'cheque'])->applyEffects();

        Livewire::test(\App\Livewire\Factures\Index::class)
            ->assertViewHas('totals', fn ($t) => $t['Factures'] === '2' && str_contains($t['Reste à encaisser'], '130'))
            ->set('search', 'CL-0001')->assertSee('FAC-00001');
        foreach (['factures', 'bon-livraisons', 'reglements-clients', 'soldes-clients'] as $p) {
            $this->get(route($p.'.print'))->assertOk();
        }
    }

    public function test_plafonds_print_lists_overdrawn_clients(): void
    {
        $this->companyUser('FR');
        $c = $this->client('Durand');
        $c->update(['credit_limit' => 100]);
        $this->client('Sans Plafond');
        $this->facture(160, '2026-11-02', 'FAC-00001');

        $this->get(route('plafonds-clients.print'))->assertOk()
            ->assertSee('Plafonds clients')->assertSee('Durand')->assertDontSee('Sans Plafond')->assertSee('Dépassement')->assertSee('60');
        Livewire::test(\App\Livewire\ClientReports\Index::class, ['mode' => 'plafonds'])
            ->assertViewHas('printUrl', fn ($u) => str_contains($u, 'plafonds-clients/imprimer'))
            ->assertSee('client-reports-phone', false);
    }

    public function test_edit_and_delete_payment_keep_treasury_and_invoice_right(): void
    {
        $this->companyUser('FR');
        $banque = TresorerieCompte::create(['name' => 'Banque', 'type' => 'banque', 'initial_balance' => 0, 'is_active' => true]);
        $f = $this->facture(120, '2026-11-02', 'FAC-00001');

        Livewire::test(\App\Livewire\ReglementsClients\Index::class)->call('create')
            ->set('client_id', $f->client_id)->set('facture_id', $f->id)->set('montant', 120)->set('mode', 'virement')->set('tresorerie_compte_id', $banque->id)
            ->call('save')->assertHasNoErrors();
        $r = ReglementClient::sole();
        $this->assertSame('payee', $f->fresh()->status);

        Livewire::test(\App\Livewire\ReglementsClients\Index::class)->call('edit', $r->id)->set('montant', 70)->call('save')->assertHasNoErrors();
        $this->assertSame(1, MouvementBancaire::where('reference_type', 'reglement_client')->count());
        $this->assertEquals(70, MouvementBancaire::where('reference_type', 'reglement_client')->value('montant'));
        $this->assertSame('partiellement_payee', $f->fresh()->status);

        Livewire::test(\App\Livewire\ReglementsClients\Index::class)->call('delete', $r->id);
        $this->assertSame(0, MouvementBancaire::where('reference_type', 'reglement_client')->count());
        $this->assertSame('envoyee', $f->fresh()->status);
        $this->assertEquals(0, $f->fresh()->montant_paye);
    }

    public function test_duplicate_bl_opens_a_prefilled_form_without_saving(): void
    {
        $this->companyUser('FR');
        $c = $this->client('Martin');
        $p = $this->product('FER-10');
        $bl = BonLivraison::create(['client_id' => $c->id, 'numero' => 'BL-00001', 'date' => now(), 'show_prices' => true, 'status' => 'delivered', 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0, 'total' => 120]);
        $bl->items()->create(['product_id' => $p->id, 'quantity' => 10, 'unit_price' => 10, 'tva_rate' => 20]);

        $res = Livewire::test(\App\Livewire\BonLivraisons\Index::class)->call('duplicate', $bl->id);
        $url = $res->effects['redirect'] ?? '';
        $this->assertStringContainsString('copilot=', $url);
        $this->assertSame(1, BonLivraison::count());                       // nothing saved yet

        parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
        request()->query->set('copilot', $q['copilot']);
        Livewire::withQueryParams(['copilot' => $q['copilot']])->test(\App\Livewire\BonLivraisons\Form::class)
            ->assertSet('client_id', $c->id)->assertSet('items.0.product_id', $p->id)->assertSet('items.0.quantity', 10.0);
    }
}
