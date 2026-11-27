<?php

namespace Tests\Feature;

use App\Livewire\ReglementsFournisseurs\Index;
use App\Models\Fournisseur;
use App\Models\ReglementFournisseur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReglementsFournisseursFiltresTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtre_par_periode_et_plusieurs_fournisseurs(): void
    {
        $this->companyUser('MA');
        $a = Fournisseur::create(['name' => 'Acier Nord']);
        $b = Fournisseur::create(['name' => 'Bois Atlas']);
        $c = Fournisseur::create(['name' => 'Ciment Sud']);
        foreach ([[$a, 100, '2026-10-05'], [$a, 250, '2026-11-10'], [$b, 300, '2026-11-15'], [$c, 999, '2026-11-20'], [$b, 40, '2025-12-31']] as [$f, $m, $d]) {
            ReglementFournisseur::create(['fournisseur_id' => $f->id, 'montant' => $m, 'date' => $d, 'mode' => 'especes']);
        }

        $this->get(route('reglements-fournisseurs.index'))->assertOk()->assertSee('Tous les fournisseurs');

        Livewire::test(Index::class)
            ->assertViewHas('count', 5)
            ->set('fournisseurIds', [(string) $a->id, (string) $b->id])
            ->assertViewHas('count', 4)
            ->assertViewHas('total', 690.0)
            ->assertSee('2 fournisseurs choisis')->assertSee('Par fournisseur')
            ->set('from', '2026-11-01')->set('to', '2026-11-30')
            ->assertSet('periode', 'perso')
            ->assertViewHas('count', 2)
            ->assertViewHas('total', 550.0)
            ->call('removeFournisseur', $b->id)
            ->assertSet('fournisseurIds', [$a->id])
            ->assertViewHas('total', 250.0)
            ->call('resetFilters')
            ->assertViewHas('count', 5);

        // préréglage "l'année dernière"
        $this->travelTo(\Carbon\Carbon::parse('2026-11-27'));
        Livewire::test(Index::class)->set('periode', 'annee_precedente')
            ->assertSet('from', '2025-01-01')->assertSet('to', '2025-12-31')
            ->assertViewHas('count', 1)->assertViewHas('total', 40.0);
    }
}
