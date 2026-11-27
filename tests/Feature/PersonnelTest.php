<?php

namespace Tests\Feature;

use App\Livewire\Personnel\Index;
use App\Livewire\Personnel\Pointage;
use App\Livewire\Personnel\Show;
use App\Models\Depense;
use App\Models\Employe;
use App\Models\EmployeAvance;
use App\Models\EmployeCredit;
use App\Models\EmployePaie;
use App\Models\EmployePresence;
use App\Support\Paie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/** Module Personnel (2026-11-28): employees, attendance, advances, loans, pay. */
class PersonnelTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_worker_attendance_advance_loan_and_pay(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 10:00'));
        $this->companyUser('MA');

        Livewire::test(Index::class)->call('create')
            ->set('nom', 'Karim Benali')->set('poste', 'Soudeur')->set('type_paie', 'jour')->set('taux', 150)
            ->call('save')->assertHasNoErrors();
        $e = Employe::firstOrFail();
        $this->assertSame('jour', $e->type_paie);

        // attendance: everyone present on the 17th and 18th, half day on the 19th, absent the 20th
        $p = Livewire::test(Pointage::class);
        foreach (['2026-11-17', '2026-11-18'] as $d) {
            $p->set('jour', $d)->call('toutPresent');
        }
        $p->call('cycle', $e->id, '2026-11-19')->call('cycle', $e->id, '2026-11-19');   // empty → P → D
        $p->set('jour', '2026-11-20')->call('marquer', $e->id, 'A');
        $this->assertSame('D', EmployePresence::where('employe_id', $e->id)->whereDate('date', '2026-11-19')->value('statut'));

        $c = Paie::calcul($e, Carbon::parse('2026-11-01'), Carbon::parse('2026-11-20'));
        $this->assertEquals(2.5, $c['jours']);
        $this->assertEquals(375.0, $c['base']);

        // advance (an expense right away), loan of 1000 repaid 100 per pay
        Livewire::test(Index::class)->call('openAvance', $e->id)->set('avMontant', 50)->call('saveAvance')->assertHasNoErrors();
        $this->assertEquals(50, (float) Depense::where('categorie', 'salaires')->sum('montant_ttc'));
        $s = Livewire::test(Show::class, ['employeId' => $e->id])
            ->call('open', 'credit')->set('montant', 1000)->set('mensualite', 100)->call('saveCredit')->assertHasNoErrors();

        // pay: 375 + 25 bonus − 50 advance − 100 loan = 250
        $s->call('open', 'paie')
            ->assertSet('debut', '2026-11-01')->assertSet('fin', '2026-11-20')
            ->assertSet('base', 375.0)->assertSet('retenue', 100.0)
            ->set('prime', 25);
        $this->assertEquals(250.0, $s->instance()->net);
        $s->call('payer')->assertHasNoErrors();

        $paie = EmployePaie::firstOrFail();
        $this->assertEquals(250, (float) $paie->net);
        $this->assertEquals(50, (float) $paie->avances);
        $this->assertNotNull(EmployeAvance::firstOrFail()->paie_id);
        $this->assertEquals(100, (float) EmployeCredit::firstOrFail()->rembourse);
        $this->assertEquals(300, (float) Depense::where('categorie', 'salaires')->sum('montant_ttc'));   // advance 50 + net 250

        $this->get(route('personnel.recu', $paie->id))->assertOk();
        $this->get(route('personnel.show', $e->id))->assertOk()->assertSee('Karim Benali');
        $this->get(route('personnel.pointage'))->assertOk();

        // the next daily period starts the day after this pay
        [$d] = Paie::periodeParDefaut($e->fresh(), Carbon::parse('2026-11-25'));
        $this->assertSame('2026-11-21', $d->toDateString());

        // deleting the pay gives back the advance and the loan repayment, and removes the expense
        $s->call('deletePaie', $paie->id);
        $this->assertNull(EmployeAvance::firstOrFail()->paie_id);
        $this->assertEquals(0, (float) EmployeCredit::firstOrFail()->rembourse);
        $this->assertEquals(50, (float) Depense::where('categorie', 'salaires')->sum('montant_ttc'));
    }

    public function test_monthly_salary_minus_absences(): void
    {
        $this->companyUser('MA');
        $e = Employe::create(['nom' => 'Youssef', 'type_paie' => 'mois', 'taux' => 2600, 'actif' => true]);
        EmployePresence::create(['employe_id' => $e->id, 'date' => '2026-11-03', 'statut' => 'A']);
        EmployePresence::create(['employe_id' => $e->id, 'date' => '2026-11-04', 'statut' => 'A']);
        EmployePresence::create(['employe_id' => $e->id, 'date' => '2026-11-05', 'statut' => 'C']);   // a day off does not reduce the pay
        $c = Paie::calcul($e, Carbon::parse('2026-11-01'), Carbon::parse('2026-11-30'));
        $this->assertEquals(2400.0, $c['base']);                                                      // 2600 − 2 × 2600/26

        // once November is paid, the default period is December (no double pay)
        $this->travelTo(Carbon::parse('2026-11-27 10:00'));
        [$d0] = Paie::periodeParDefaut($e);
        $this->assertSame('2026-11-01', $d0->toDateString());
        Paie::payer($e, ['debut' => '2026-11-01', 'fin' => '2026-11-30', 'jours' => 0, 'base' => 2400, 'date_paiement' => '2026-11-27', 'mode_paiement' => 'especes', 'tresorerie_compte_id' => null]);
        [$d1, $f1] = Paie::periodeParDefaut($e->fresh());
        $this->assertSame(['2026-12-01', '2026-12-31'], [$d1->toDateString(), $f1->toDateString()]);
    }

    public function test_daily_worker_paid_up_to_today_has_nothing_left(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 10:00'));
        $this->companyUser('MA');
        $e = Employe::create(['nom' => 'Ali', 'type_paie' => 'jour', 'taux' => 100, 'actif' => true]);
        EmployePresence::create(['employe_id' => $e->id, 'date' => '2026-11-20', 'statut' => 'P']);
        Paie::payer($e, ['debut' => '2026-11-01', 'fin' => '2026-11-20', 'jours' => 1, 'base' => 100, 'date_paiement' => '2026-11-20', 'mode_paiement' => 'especes', 'tresorerie_compte_id' => null]);
        [$d, $f] = Paie::periodeParDefaut($e->fresh());
        $this->assertSame('2026-11-21', $d->toDateString());
        $this->assertEquals(0.0, Paie::calcul($e, $d, $f)['base']);
    }

    public function test_free_plan_is_locked(): void
    {
        $u = $this->companyUser('MA');
        $u->project->forceFill(['plan' => 'free', 'plan_ends_at' => null])->save();
        $this->get(route('personnel.index'))->assertOk()->assertSee('inclus dans les plans Pro et Premium');
        Livewire::test(Index::class)->call('create')->assertForbidden();
    }
}
