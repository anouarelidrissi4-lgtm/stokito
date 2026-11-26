<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductLabelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_labels_page_ships_the_products_of_the_company_only(): void
    {
        $this->companyUser('FR');
        $p = $this->product('FER-10');
        $p->update(['barcode' => '2001234567893', 'sale_price' => 12.5]);

        $this->get(route('products.labels'))->assertOk()
            ->assertSee('Étiquettes code barre')
            ->assertSee('2001234567893')
            ->assertSee('/js/etiquettes.js', false);

        $this->companyUser('MA');                       // another company: must not see FER-10
        $this->get(route('products.labels'))->assertOk()->assertDontSee('2001234567893');
    }
}
