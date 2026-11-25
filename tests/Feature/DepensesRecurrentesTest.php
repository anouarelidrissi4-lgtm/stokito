<?php

namespace Tests\Feature;

use App\Livewire\DepensesRecurrentes\Index;
use App\Models\Depense;
use App\Models\DepenseRecurrente;
use App\Models\MouvementBancaire;
use App\Models\TresorerieCompte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class DepensesRecurrentesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_past_start_creates_the_due_expenses_and_schedules_the_next(): void
    {
        Carbon::setTestNow('2026-11-26 10:00');
        $this->companyUser('FR');
        $banque = TresorerieCompte::create(['name' => 'Banque', 'type' => 'banque', 'initial_balance' => 5000, 'is_active' => true]);

        Livewire::test(Index::class)->call('create')
            ->set('libelle', 'Loyer du dépôt')->set('categorie', 'loyer')->set('montant_ttc', '1200')->set('taux_tva', '0')
            ->set('date_debut', '2026-10-05')->set('jour', 5)->set('tresorerie_compte_id', $banque->id)
            ->call('save')->assertHasNoErrors();

        $r = DepenseRecurrente::sole();
        $this->assertSame(['2026-10-05', '2026-11-05'], Depense::orderBy('date')->pluck('date')->map->toDateString()->all());
        $this->assertSame('2026-12-05', $r->prochaine_date->toDateString());
        $this->assertSame(2, MouvementBancaire::where('reference_type', 'depense')->count());
        $this->assertEquals(2600, $banque->fresh()->currentBalance());
        $this->assertSame($r->id, Depense::first()->depense_recurrente_id);
    }

    public function test_daily_command_creates_due_expenses_in_each_company(): void
    {
        Carbon::setTestNow('2026-11-26 10:00');
        $a = $this->companyUser('FR');
        $ra = DepenseRecurrente::create(['libelle' => 'Internet', 'categorie' => 'telecom', 'montant_ttc' => 39, 'montant_tva' => 6.5, 'mode_paiement' => 'prelevement',
            'frequence' => 'mensuelle', 'jour' => 31, 'date_debut' => '2026-11-30', 'prochaine_date' => '2026-11-30', 'actif' => true, 'user_id' => $a->id]);
        $b = $this->companyUser('FR');
        DepenseRecurrente::create(['libelle' => 'Assurance', 'categorie' => 'assurance', 'montant_ttc' => 600, 'montant_tva' => 0, 'mode_paiement' => 'virement',
            'frequence' => 'annuelle', 'jour' => 1, 'date_debut' => '2026-12-01', 'prochaine_date' => '2026-12-01', 'actif' => true, 'user_id' => $b->id]);
        Auth::logout();

        Carbon::setTestNow('2026-12-01 06:10');
        $this->artisan('depenses:recurrentes')->assertSuccessful();

        $all = Depense::withoutGlobalScopes()->orderBy('id')->get();
        $this->assertCount(2, $all);
        $this->assertSame([$a->project_id, $b->project_id], $all->pluck('project_id')->all());
        $this->assertSame('2026-11-30', $all[0]->date->toDateString());
        $this->assertSame('2026-12-31', $ra->fresh()->prochaine_date->toDateString());
        $this->assertSame('2027-12-01', DepenseRecurrente::withoutGlobalScopes()->where('libelle', 'Assurance')->value('prochaine_date')->toDateString());

        $this->artisan('depenses:recurrentes')->assertSuccessful();
        $this->assertSame(2, Depense::withoutGlobalScopes()->count());   // nothing twice
    }

    public function test_pause_end_date_and_tenant_isolation(): void
    {
        Carbon::setTestNow('2026-11-26 10:00');
        $this->companyUser('FR');
        $r = DepenseRecurrente::create(['libelle' => 'Salaire', 'categorie' => 'salaires', 'montant_ttc' => 1800, 'montant_tva' => 0, 'mode_paiement' => 'virement',
            'frequence' => 'mensuelle', 'jour' => 28, 'date_debut' => '2026-11-28', 'prochaine_date' => '2026-11-28', 'date_fin' => '2026-12-31', 'actif' => true, 'user_id' => auth()->id()]);
        Livewire::test(Index::class)->call('toggle', $r->id);
        $this->assertFalse($r->fresh()->actif);

        Carbon::setTestNow('2027-01-10 10:00');
        $r->fresh()->update(['actif' => true, 'prochaine_date' => '2026-11-28']);
        $this->assertSame(2, $r->fresh()->genererEcheances());           // 28/11 and 28/12, then ended
        $this->assertFalse($r->fresh()->actif);

        $this->companyUser('FR');
        $this->assertSame(0, DepenseRecurrente::count());
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(Index::class)->call('edit', $r->id);
    }
}
