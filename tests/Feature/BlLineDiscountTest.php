<?php

namespace Tests\Feature;

use App\Livewire\BonLivraisons\Form as BlForm;
use App\Livewire\Factures\Form as FactureForm;
use App\Models\BonLivraison;
use App\Support\DocumentPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Réduction par ligne sur le bon de livraison + infos stock / prix sous la ligne (2026-11-27). */
class BlLineDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_line_discounts_flow_into_totals_pdf_and_invoice(): void
    {
        $this->companyUser('MA');
        $client = $this->client('Client Remise');
        $a = $this->product('COR-25', 58.1, 30, 54);     // vente 54 TTC → 45 HT à 20 %
        $a->update(['tva_rate' => 20, 'wholesale_price' => 48]);
        $b = $this->product('TUB-20', 10, 10, 24);
        $b->update(['tva_rate' => 20]);

        $c = Livewire::test(BlForm::class)
            ->set('client_id', $client->id)
            ->set('items.0.product_id', $a->id)
            ->assertSet('items.0.unit_price', 45.0)
            ->assertSee('Stock actuel')->assertSee('Prix gros')
            ->set('items.0.quantity', 2)
            ->set('items.0.remise_type', 'percent')->set('items.0.remise_value', 10)   // 90 - 9 = 81
            ->call('addItem')
            ->set('items.1.product_id', $b->id)                                         // 20 HT
            ->set('items.1.quantity', 3)
            ->set('items.1.remise_type', 'fixed')->set('items.1.remise_value', 5)        // 60 - 5 = 55
            ->set('remise_type', 'percent')->set('remise_value', 10);                    // (81 + 55) × 10 % = 13,60

        $this->assertEquals(14.0, $c->instance()->montantRemiseLignes);
        $this->assertEquals(13.6, $c->instance()->montantRemise);
        $this->assertEquals(122.4, $c->instance()->totalHt);
        $this->assertEquals(146.88, $c->instance()->totalTtc);

        // "Prix gros" under the line puts the wholesale price (48 TTC → 40 HT)
        $c->call('applyPrice', 0, 'gros')->assertSet('items.0.unit_price', 40.0)
            ->call('applyPrice', 0, 'detail')->assertSet('items.0.unit_price', 45.0);

        $c->call('save')->assertHasNoErrors();

        $bl = BonLivraison::latest('id')->first();
        $this->assertEquals(122.4, (float) $bl->total_ht);
        $this->assertEquals(146.88, (float) $bl->total);
        $this->assertEquals([81.0, 55.0], $bl->items()->orderBy('id')->pluck('total')->map(fn ($v) => (float) $v)->all());
        $this->assertEquals('fixed', $bl->items()->orderBy('id')->get()[1]->remise_type);

        // PDF: net line totals, the line discount named on the line, then the document discount
        $d = DocumentPdf::bonLivraisonData($bl->fresh());
        $this->assertEquals(81.0, (float) $d['items'][0]['total']);
        $this->assertStringContainsString('remise 10 %', $d['items'][0]['name']);
        $this->assertEquals(136.0, (float) $d['totals']['brut']);
        $this->assertEquals(13.6, (float) $d['totals']['remise']);
        $this->assertEquals(146.88, (float) $d['totals']['ttc']);

        // the invoice made from this BL keeps the same prices and line discounts (invoices have line discounts too)
        $f = Livewire::test(FactureForm::class, ['blId' => $bl->id]);
        $this->assertEquals([45.0, 20.0], array_map(fn ($i) => (float) $i['unit_price'], $f->get('items')));
        $this->assertEquals(['percent', 'fixed'], array_map(fn ($i) => $i['remise_type'], $f->get('items')));
        $this->assertEquals(146.88, $f->instance()->totalTtc);
        $f->call('save')->assertHasNoErrors();
        $fac = \App\Models\Facture::latest('id')->first();
        $this->assertEquals(122.4, (float) $fac->total_ht);
        $this->assertEquals(146.88, (float) $fac->total_ttc);
        $this->assertEquals([81.0, 55.0], $fac->items()->orderBy('id')->pluck('total')->map(fn ($v) => (float) $v)->all());
        $pdf = DocumentPdf::factureData($fac->fresh());
        $this->assertStringContainsString('remise 10 %', $pdf['items'][0]['name']);
        $this->assertEquals(136.0, (float) $pdf['totals']['brut']);
        $this->assertEquals(13.6, (float) $pdf['totals']['remise']);

        // editing the BL reloads the discounts
        Livewire::test(BlForm::class, ['bonLivraisonId' => $bl->id])
            ->assertSet('items.0.remise_value', 10.0)->assertSet('items.1.remise_type', 'fixed');
    }

    public function test_ttc_price_and_single_discount_field(): void
    {
        $this->companyUser('MA');
        $p = $this->product('COR-25', 58.1, 30, 54);
        $p->update(['tva_rate' => 20]);
        $c = Livewire::test(BlForm::class)->set('items.0.product_id', $p->id)
            ->assertSee('Prix TTC')->assertSee('0 ou 0%')
            ->call('setLinePriceTtc', 0, '42,00')->assertSet('items.0.unit_price', 35.0)
            ->call('setLineRemise', 0, '10%')->assertSet('items.0.remise_type', 'percent')->assertSet('items.0.remise_value', 10.0)
            ->call('setLineRemise', 0, '2,5')->assertSet('items.0.remise_type', 'fixed')->assertSet('items.0.remise_value', 2.5)
            ->call('setLineRemise', 0, '')->assertSet('items.0.remise_value', 0.0);
        $this->assertSame('2,5', $c->instance()->ligneRemiseTexte(['remise_type' => 'fixed', 'remise_value' => 2.5]));
        $this->assertSame('10%', $c->instance()->ligneRemiseTexte(['remise_type' => 'percent', 'remise_value' => 10]));
    }

    public function test_percent_line_discount_cannot_exceed_100(): void
    {
        $this->companyUser('MA');
        $client = $this->client();
        $p = $this->product('X-1');
        Livewire::test(BlForm::class)
            ->set('client_id', $client->id)->set('items.0.product_id', $p->id)
            ->set('items.0.remise_type', 'percent')->set('items.0.remise_value', 150)
            ->call('save')->assertHasErrors('items.0.remise_value');
    }
}
