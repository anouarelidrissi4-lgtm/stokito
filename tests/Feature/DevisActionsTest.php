<?php

namespace Tests\Feature;

use App\Livewire\Devis\Index;
use App\Models\BonLivraison;
use App\Models\Devis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DevisActionsTest extends TestCase
{
    use RefreshDatabase;

    private function devis(): Devis
    {
        $c = $this->client('Bâti Plus');
        $p = $this->product('FER-10');
        $d = Devis::create(['client_id' => $c->id, 'numero' => 'DEV-00001', 'date' => today()->subDays(10), 'valid_until' => today()->addDays(20),
            'status' => 'sent', 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0, 'total_ht' => 100, 'total_ttc' => 120]);
        $d->items()->create(['product_id' => $p->id, 'quantity' => 10, 'unit_price' => 10, 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0, 'total' => 100]);

        return $d;
    }

    public function test_duplicate_makes_a_new_draft_with_the_lines(): void
    {
        $this->companyUser('FR');
        $d = $this->devis();
        Livewire::test(Index::class)->call('duplicate', $d->id)->assertRedirect();
        $copy = Devis::where('id', '!=', $d->id)->sole();
        $this->assertSame('DEV-00002', $copy->numero);
        $this->assertSame('draft', $copy->status);
        $this->assertTrue($copy->date->isToday());
        $this->assertSame(1, $copy->items()->count());
        $this->assertEquals(120, $copy->total_ttc);
    }

    public function test_validate_to_delivery_note_once(): void
    {
        $this->companyUser('FR');
        $d = $this->devis();
        Livewire::test(Index::class)->call('convertToBonLivraison', $d->id);
        $this->assertSame('accepted', $d->fresh()->status);
        $this->assertSame(1, BonLivraison::count());
        Livewire::test(Index::class)->call('convertToBonLivraison', $d->id);
        $this->assertSame(1, BonLivraison::count());
    }

    public function test_list_shows_the_actions(): void
    {
        $this->companyUser('FR');
        $this->devis();
        Livewire::test(Index::class)->assertSee('Valider en bon de livraison')->assertSee('Dupliquer')->assertSee('Modifier')->assertSee('Imprimer')->assertSee('Supprimer');
    }
}
