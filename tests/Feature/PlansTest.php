<?php

namespace Tests\Feature;

use App\Models\Facture;
use App\Models\Project;
use App\Support\Plans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlansTest extends TestCase
{
    use RefreshDatabase;

    private function setPlan(string $plan, $ends = null): void
    {
        auth()->user()->project->forceFill(['plan' => $plan, 'plan_ends_at' => $ends, 'plan_trial' => false])->save();
        auth()->user()->unsetRelation('project');
    }

    public function test_new_company_gets_the_premium_trial(): void
    {
        $p = Project::create(['name' => 'Nouvelle', 'is_active' => true]);
        $this->assertSame('premium', $p->plan);
        $this->assertTrue($p->plan_trial);
        $this->assertTrue($p->plan_ends_at->between(now()->addMonths(3)->subDay(), now()->addMonths(3)->addDay()));
    }

    public function test_free_plan_allows_five_invoices_a_month_and_no_ai(): void
    {
        $this->companyUser('MA');
        $this->setPlan('free');
        $c = $this->client('Martin');
        for ($i = 1; $i <= 5; $i++) {
            Facture::create(['client_id' => $c->id, 'numero' => 'FAC-0000'.$i, 'date' => now(), 'due_date' => now(), 'tva_rate' => 20, 'status' => 'envoyee',
                'remise_type' => 'percent', 'remise_value' => 0, 'total_ht' => 10, 'total_ttc' => 12, 'montant_paye' => 0]);
        }
        $this->assertFalse(Plans::canCreateFacture());
        Livewire::test(\App\Livewire\Factures\Form::class)->call('save')->assertHasErrors('plan');
        $this->assertSame(0, Plans::limit('copilot'));
        $this->assertSame(0, Plans::limit('scans'));

        $this->setPlan('premium', now()->addMonth());
        $this->assertTrue(Plans::canCreateFacture());
        $this->assertNull(Plans::limit('copilot'));

        $this->setPlan('premium', now()->subDay());                     // expired -> back to Free
        $this->assertSame('free', Plans::current());
    }

    public function test_superadmin_extends_a_plan_and_admin_sees_the_page(): void
    {
        $this->companyUser('FR');
        $this->get(route('abonnement'))->assertOk()->assertSee('Premium')->assertSee('25 €')->assertSee('AimaneAdri', false)->assertSee('79EUR', false);
        $project = auth()->user()->project;

        $sa = new \App\Models\User();
        $sa->forceFill(['name' => 'SA', 'email' => uniqid('sa').'@example.test', 'password' => bcrypt('x-secret-123'), 'role' => 'admin', 'status' => 'active',
            'is_superadmin' => true, 'project_id' => null, 'is_demo' => false, 'email_verified_at' => now()])->save();
        $this->actingAs($sa);
        Livewire::test(\App\Livewire\Projects\Index::class)->call('setPlan', $project->id, 'pro', 12);
        $project->refresh();
        $this->assertSame('pro', $project->plan);
        $this->assertSame('annee', $project->plan_period);
        $this->assertTrue($project->plan_ends_at->isAfter(now()->addMonths(11)));
    }
}
