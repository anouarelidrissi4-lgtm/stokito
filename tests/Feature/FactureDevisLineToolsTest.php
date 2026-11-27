<?php

namespace Tests\Feature;

use App\Livewire\Devis\Form as DevisForm;
use App\Livewire\Factures\Form as FactureForm;
use App\Models\Facture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Facture + devis: réduction par ligne, Prix TTC, « 0 ou 0% », stock et prix sous la ligne (2026-11-27). */
class FactureDevisLineToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_line_discount_ttc_price_and_save(): void
    {
        $this->companyUser('MA');
        $client = $this->client('Client Facture');
        $a = $this->product('COR-25', 58.1, 30, 54);       // 45 HT
        $a->update(['tva_rate' => 20, 'wholesale_price' => 48]);

        $c = Livewire::test(FactureForm::class)
            ->set('client_id', $client->id)
            ->set('items.0.product_id', $a->id)
            ->assertSee('Stock actuel')->assertSee('Prix gros')->assertSee('Prix TTC')->assertSee('0 ou 0%')
            ->set('items.0.quantity', 4)
            ->call('setLinePriceTtc', 0, '60')->assertSet('items.0.unit_price', 50.0)      // 200 HT
            ->call('setLineRemise', 0, '5%')                                               // - 10 → 190
            ->set('remise_type', 'fixed')->set('remise_value', 10);                          // - 10 → 180

        $this->assertEquals(10.0, $c->instance()->montantRemiseLignes);
        $this->assertEquals(180.0, $c->instance()->totalHt);
        $this->assertEquals(216.0, $c->instance()->totalTtc);
        $c->call('applyPrice', 0, 'gros')->assertSet('items.0.unit_price', 40.0)
            ->call('setLinePriceTtc', 0, '60')
            ->call('save')->assertHasNoErrors();

        $f = Facture::latest('id')->first();
        $this->assertEquals(180.0, (float) $f->total_ht);
        $this->assertEquals(216.0, (float) $f->total_ttc);
        $item = $f->items()->first();
        $this->assertEquals('percent', $item->remise_type);
        $this->assertEquals(190.0, (float) $item->total);

        // editing the invoice reloads the discount; a percent above 100 is refused
        Livewire::test(FactureForm::class, ['factureId' => $f->id])
            ->assertSet('items.0.remise_value', 5.0)
            ->call('setLineRemise', 0, '150%')->call('save')->assertHasErrors('items.0.remise_value');
    }

    public function test_devis_has_the_same_line_tools_and_its_invoice_keeps_the_amount(): void
    {
        $this->companyUser('MA');
        $client = $this->client('Client Devis');
        $p = $this->product('TUB-20', 10, 10, 24);          // 20 HT
        $p->update(['tva_rate' => 20]);

        $c = Livewire::test(DevisForm::class)
            ->set('client_id', $client->id)
            ->set('items.0.product_id', $p->id)
            ->assertSee('Prix TTC')->assertSee('0 ou 0%')->assertSee('Stock actuel')
            ->set('items.0.quantity', 3)
            ->call('setLineRemise', 0, '5')->assertSet('items.0.remise_type', 'fixed')->assertSet('items.0.remise_value', 5.0);
        $this->assertEquals(55.0, $c->instance()->ligneTotal($c->get('items')[0]));
        $c->call('save')->assertHasNoErrors();

        $devis = \App\Models\Devis::latest('id')->first();
        $f = Livewire::withQueryParams(['devis' => $devis->id])->test(FactureForm::class);
        $this->assertEquals(18.33, (float) $f->get('items')[0]['unit_price']);   // 55 / 3, the quote's discount in the net price
        $this->assertEqualsWithDelta((float) $devis->fresh()->total_ttc, $f->instance()->totalTtc, 0.05);
    }
}
