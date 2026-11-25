<?php

namespace Tests\Feature;

use App\Livewire\Depenses\Index;
use App\Models\Depense;
use App\Models\MouvementBancaire;
use App\Models\TresorerieCompte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DepensesTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_computes_vat_and_logs_the_treasury_exit(): void
    {
        $this->companyUser('FR');
        $banque = TresorerieCompte::create(['name' => 'Banque', 'type' => 'banque', 'initial_balance' => 1000, 'is_active' => true]);

        Livewire::test(Index::class)
            ->call('create')
            ->set('libelle', 'Gasoil camion')
            ->set('categorie', 'transport')
            ->set('taux_tva', '20')
            ->set('montant_ttc', '120')
            ->assertSet('montant_tva', 20.0)
            ->set('mode_paiement', 'carte')
            ->set('tresorerie_compte_id', $banque->id)
            ->call('save')
            ->assertHasNoErrors();

        $d = Depense::sole();
        $this->assertSame(100.0, $d->montant_ht);
        $mv = MouvementBancaire::where('reference_type', 'depense')->sole();
        $this->assertSame('exit', $mv->type);
        $this->assertEquals(120, $mv->montant);
        $this->assertEquals(880, $banque->fresh()->currentBalance());
    }

    public function test_edit_and_delete_keep_the_treasury_in_sync(): void
    {
        $this->companyUser('FR');
        $a = TresorerieCompte::create(['name' => 'Caisse', 'type' => 'caisse', 'initial_balance' => 0, 'is_active' => true]);
        $d = Depense::create(['date' => now()->toDateString(), 'categorie' => 'loyer', 'libelle' => 'Loyer', 'montant_ttc' => 500, 'montant_tva' => 0, 'mode_paiement' => 'especes', 'tresorerie_compte_id' => $a->id]);
        $d->syncTresorerie();

        Livewire::test(Index::class)->call('edit', $d->id)->set('montant_ttc', '650')->set('tresorerie_compte_id', '')->call('save')->assertHasNoErrors();
        $this->assertEquals(650, $d->fresh()->montant_ttc);
        $this->assertSame(0, MouvementBancaire::where('reference_type', 'depense')->count());

        Livewire::test(Index::class)->call('edit', $d->id)->set('tresorerie_compte_id', $a->id)->call('save');
        $this->assertSame(1, MouvementBancaire::where('reference_type', 'depense')->count());

        Livewire::test(Index::class)->call('delete', $d->id);
        $this->assertSame(0, Depense::count());
        $this->assertSame(0, MouvementBancaire::where('reference_type', 'depense')->count());
    }

    public function test_receipt_is_private_and_scoped_to_the_company(): void
    {
        Storage::fake('local');
        $this->companyUser('FR');
        Livewire::test(Index::class)->call('create')
            ->set('libelle', 'Électricité')->set('montant_ttc', '90')
            ->set('justificatif', UploadedFile::fake()->create('facture.pdf', 120, 'application/pdf'))
            ->call('save')->assertHasNoErrors();
        $d = Depense::sole();
        Storage::disk('local')->assertExists($d->justificatif_path);
        $this->get(route('depenses.justificatif', $d->id))->assertOk();

        $this->companyUser('FR'); // another company
        $this->assertSame(0, Depense::count());
        $this->get(route('depenses.justificatif', $d->id))->assertNotFound();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(Index::class)->call('edit', $d->id);
    }

    public function test_foreign_account_id_is_refused(): void
    {
        $this->companyUser('FR');
        $other = TresorerieCompte::create(['name' => 'X', 'type' => 'banque', 'initial_balance' => 0, 'is_active' => true]);
        $this->companyUser('FR');
        Livewire::test(Index::class)->call('create')->set('libelle', 'Test')->set('montant_ttc', '10')
            ->set('tresorerie_compte_id', $other->id)->call('save')->assertHasErrors('tresorerie_compte_id');
        $this->assertSame(0, Depense::count());
    }

    public function test_employee_needs_the_permission(): void
    {
        $admin = $this->companyUser('FR');
        $emp = new User();
        $emp->forceFill(['name' => 'Emp', 'email' => uniqid('e').'@example.test', 'password' => bcrypt('secret-pass-123'),
            'role' => 'user', 'status' => 'active', 'project_id' => $admin->project_id, 'is_demo' => false,
            'email_verified_at' => now(), 'permissions' => ['dashboard']])->save();
        $this->actingAs($emp)->get(route('depenses.index'))->assertForbidden();

        $emp->forceFill(['permissions' => ['dashboard', 'depenses']])->save();
        $this->actingAs($emp->fresh())->get(route('depenses.index'))->assertOk()->assertSee('Ajouter une dépense');
    }

    public function test_dashboard_shows_month_expenses(): void
    {
        $this->companyUser('FR');
        Depense::create(['date' => now()->toDateString(), 'categorie' => 'loyer', 'libelle' => 'Loyer', 'montant_ttc' => 1234, 'montant_tva' => 0, 'mode_paiement' => 'virement']);
        $this->get(route('dashboard'))->assertOk()->assertSee('Dépenses du mois')->assertViewHas('depensesMoisTtc', 1234.0);
    }
}
