<?php

namespace Tests\Feature;

use App\Models\BonLivraison;
use App\Models\ChequeEffetFournisseur;
use App\Models\Fournisseur;
use App\Models\ReglementClient;
use App\Support\GrandLivre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GrandLivreTest extends TestCase
{
    use RefreshDatabase;

    private function bl($client, string $num, string $date, float $total): BonLivraison
    {
        return BonLivraison::create(['client_id' => $client->id, 'numero' => $num, 'date' => $date, 'status' => 'delivered', 'show_prices' => true,
            'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0, 'total' => $total]);
    }

    public function test_grand_livre_matches_the_model(): void
    {
        $this->companyUser('MA');
        $c = $this->client('MOHAMED CHERAFAT');
        $b1 = $this->bl($c, '01327/26', '2026-11-14', 320000);
        foreach ([['2026-11-20', 12000, 'versement'], ['2026-11-23', 100000, 'versement'], ['2026-11-24', 40500, 'especes']] as [$d, $m, $mode]) {
            ReglementClient::create(['client_id' => $c->id, 'bon_livraison_id' => $b1->id, 'montant' => $m, 'date' => $d, 'mode' => $mode]);
        }
        $this->bl($c, '01360/26', '2026-11-25', 81055);

        $l = GrandLivre::client($c);
        $this->assertSame(2, $l['documents']);
        $this->assertEquals(401055, $l['debits']);
        $this->assertEquals(152500, $l['credits']);
        $this->assertEquals(-248555, $l['soldeComptable']);
        $this->assertEquals([-320000, -308000, -208000, -167500, -248555], array_column($l['rows'], 'balance'));
        $this->assertSame('Règlement Versement', $l['rows'][1]['element']);
        $this->assertSame('BL 01327/26', $l['rows'][1]['ref']);

        // period: what came before is carried over
        $p = GrandLivre::client($c, '2026-11-21');
        $this->assertEquals(-308000, $p['report']);
        $this->assertEquals(-248555, $p['soldePeriode']);

        // paid delivery notes hidden with their payments
        $this->bl($c, 'BL-PAYE', '2026-11-26', 500);
        ReglementClient::create(['client_id' => $c->id, 'bon_livraison_id' => BonLivraison::where('numero', 'BL-PAYE')->value('id'), 'montant' => 500, 'date' => '2026-11-26', 'mode' => 'especes']);
        $h = GrandLivre::client($c, null, null, true);
        $this->assertNotContains('BL-PAYE', array_column($h['rows'], 'ref'));
        $this->assertEquals(-248555, $h['soldePeriode']);

        Livewire::test(\App\Livewire\DossierClients\Index::class)->assertSee('MOHAMED CHERAFAT');
        Livewire::test(\App\Livewire\DossierClients\Show::class, ['clientId' => $c->id])->assertSee('Grand livre client')->assertSee('01360/26')->assertSee('Solde comptable');
        $this->get(route('dossier-clients.pdf', ['clientId' => $c->id, 'payes' => 1]))->assertOk();
    }

    public function test_supplier_cheques_page_filters_and_totals(): void
    {
        $this->companyUser('MA');
        $f = Fournisseur::create(['name' => 'BATIFER']);
        $mk = fn ($n, $ech, $m, $st = 'en_attente', $type = 'cheque') => ChequeEffetFournisseur::create(['fournisseur_id' => $f->id, 'type' => $type, 'numero_cheque' => $n,
            'montant' => $m, 'date_emission' => '2026-05-01', 'date_echeance' => $ech, 'status' => $st]);
        $mk('2100241', '2026-05-08', 51570);
        $mk('2100238', '2026-05-08', 60000, 'encaisse');
        $mk('E-77', '2099-01-10', 1000, 'en_attente', 'effet');

        $all = \App\Livewire\ChequesFournisseurs\Index::data('', '', '', '');
        $this->assertSame(3, $all['stats']['all'][0]);
        $this->assertEquals(60000, $all['stats']['paid'][1]);
        $this->assertSame(1, $all['stats']['late'][0]);
        $this->assertSame(1, \App\Livewire\ChequesFournisseurs\Index::data('', '', '', 'retard')['stats']['all'][0]);
        $this->assertSame(2, \App\Livewire\ChequesFournisseurs\Index::data('', '2026-01-01', '2026-12-31', '')['stats']['all'][0]);

        Livewire::test(\App\Livewire\ChequesFournisseurs\Index::class)->assertSee('BATIFER')->assertSee('Non payé')->assertSee('Effet')
            ->call('create')->set('fournisseur_id', $f->id)->set('type', 'effet')->set('numero_cheque', 'E-78')->set('montant', 250)->call('save')->assertHasNoErrors();
        $this->assertSame('effet', ChequeEffetFournisseur::where('numero_cheque', 'E-78')->value('type'));
        $this->get(route('cheques-fournisseurs.pdf', ['statut' => 'retard']))->assertOk();
    }
}
