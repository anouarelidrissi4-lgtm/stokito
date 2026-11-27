<?php

namespace Tests\Feature;

use App\Support\ProductSheet;
use Tests\TestCase;

/** The free "gestion de stock Excel" template imports as it is into Stokito (2026-11-27). */
class StockExcelTemplateTest extends TestCase
{
    public function test_template_is_read_by_the_product_import(): void
    {
        $path = public_path('docs/modele-gestion-de-stock-excel.xlsx');
        $this->assertFileExists($path);
        $r = ProductSheet::read($path, 'xlsx');

        $this->assertCount(8, $r['rows']);                       // empty template rows (formulas giving "") are skipped
        $first = $r['rows'][0];
        $this->assertSame('VIS-430', $first['reference']);
        $this->assertEquals(42, $first['stock_qty']);            // computed "Stock" (40 + 20 - 18), not the formula text
        $this->assertEquals(7.1, $first['sale_price']);
        $this->assertEquals(3.2, $first['purchase_price']);
        $this->assertEquals(10, $first['stock_alert_threshold']);
        $this->assertEquals(0, $r['rows'][7]['stock_qty']);      // GAN-T9 en rupture
    }
}
