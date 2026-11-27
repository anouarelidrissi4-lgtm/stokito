<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GestionStockExcelPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_excel_page_is_public_and_indexed(): void
    {
        $this->get('/gestion-de-stock-excel')->assertOk()
            ->assertSee('Gestion de stock Excel')->assertSee('/docs/modele-gestion-de-stock-excel.xlsx', false)
            ->assertSee('SOMME.SI.ENS')->assertSee('FAQPage', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('gestion-de-stock-excel');
        $this->assertStringContainsString('Allow: /gestion-de-stock-excel', file_get_contents(public_path('robots.txt')));
        $this->assertFileExists(public_path('images/excel/gestion-stock-excel-produits.webp'));
        $this->get('/logiciel-gestion-de-stock-gratuit')->assertOk()->assertSee('gestion-de-stock-excel');
    }
}
