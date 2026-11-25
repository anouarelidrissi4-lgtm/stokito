<?php

namespace Tests\Feature;

use App\Livewire\Products\Index;
use App\Models\Category;
use App\Models\Product;
use App\Support\MenuItems;
use App\Support\ProductSheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsSheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_is_a_real_xlsx_that_reads_back(): void
    {
        $this->companyUser('FR');
        $p = $this->product('FER-10', 310, 5.8, 8.2);
        $p->update(['category_id' => Category::create(['name' => 'Fer et acier'])->id]);

        $path = ProductSheet::export(Product::with(['category', 'brand', 'fournisseur'])->get(), true);
        $this->assertSame("PK\x03\x04", file_get_contents($path, false, null, 0, 4)); // zip = xlsx
        $sheet = ProductSheet::read($path, 'xlsx');
        $this->assertSame([], $sheet['unknown']);
        $this->assertSame('FER-10', $sheet['rows'][0]['reference']);
        $this->assertSame('Fer et acier', $sheet['rows'][0]['category']);
        $this->assertEquals(8.2, $sheet['rows'][0]['sale_price']);
        @unlink($path);

        Livewire::test(Index::class)->call('exportExcel')->assertFileDownloaded('produits-'.now()->format('Y-m-d').'.xlsx');
    }

    public function test_import_xlsx_creates_and_updates_without_wiping_empty_cells(): void
    {
        $this->companyUser('FR');
        $this->product('VIS-440', 20, 6.2, 11.9)->update(['barcode' => '3700000000011']);

        $tmp = tempnam(sys_get_temp_dir(), 't').'.xlsx';
        $w = new \OpenSpout\Writer\XLSX\Writer();
        $w->openToFile($tmp);
        $w->addRow(\OpenSpout\Common\Entity\Row::fromValues(['Réf', 'Désignation', 'Catégorie', 'Prix de vente TTC', 'Stock', 'Couleur']));
        $w->addRow(\OpenSpout\Common\Entity\Row::fromValues(['VIS-440', '', '', 12.5, '', 'rouge']));       // update price only
        $w->addRow(\OpenSpout\Common\Entity\Row::fromValues(['CHV-8', 'Chevilles 8 mm', 'Quincaillerie', '6,50', 40, '']));
        $w->addRow(\OpenSpout\Common\Entity\Row::fromValues(['', 'Sans référence', '', 3, 1, '']));
        $w->close();

        Livewire::test(Index::class)->call('openImport')
            ->set('importFile', UploadedFile::fake()->createWithContent('produits.xlsx', file_get_contents($tmp)))
            ->call('importExcel')->assertHasNoErrors()
            ->assertSet('importReport.created', 1)->assertSet('importReport.updated', 1)
            ->assertSet('importReport.unknown', ['Couleur']);
        @unlink($tmp);

        $vis = Product::where('reference', 'VIS-440')->sole();
        $this->assertEquals(12.5, $vis->sale_price);
        $this->assertEquals(6.2, $vis->purchase_price);       // empty cell kept the value
        $this->assertSame('3700000000011', $vis->barcode);
        $this->assertEquals(20, $vis->stock_qty);
        $chv = Product::where('reference', 'CHV-8')->sole();
        $this->assertEquals(6.5, $chv->sale_price);
        $this->assertSame('Quincaillerie', $chv->category->name);
    }

    public function test_import_csv_from_excel_fr_semicolon_windows_1252(): void
    {
        $this->companyUser('FR');
        $csv = mb_convert_encoding("Référence;Désignation;Prix Vente TTC;Stock\r\nTOLE-1;Tôle galvanisée;1 234,50;7\r\n", 'Windows-1252', 'UTF-8');

        Livewire::test(Index::class)->call('openImport')
            ->set('importFile', UploadedFile::fake()->createWithContent('produits.csv', $csv))
            ->call('importExcel')->assertHasNoErrors()->assertSet('importReport.created', 1);

        $p = Product::where('reference', 'TOLE-1')->sole();
        $this->assertSame('Tôle galvanisée', $p->name);
        $this->assertEquals(1234.5, $p->sale_price);
        $this->assertEquals(7, $p->stock_qty);
    }

    public function test_print_page_lists_all_filtered_products_with_chosen_columns(): void
    {
        $this->companyUser('FR');
        foreach (range(1, 12) as $i) {
            $this->product(sprintf('P-%02d', $i));
        }
        $this->get(route('products.print', ['cols' => 'marque']))
            ->assertOk()->assertSee('P-01')->assertSee('P-12')->assertSee('Marque')->assertDontSee('Prix achat');
        $this->get(route('products.print', ['q' => 'P-03']))->assertOk()->assertSee('P-03')->assertDontSee('P-04');
    }

    public function test_caisse_has_its_own_menu_section(): void
    {
        $menu = MenuItems::resolve(null);
        $this->assertArrayHasKey('caisse', $menu['sections']);
        $keys = array_column($menu['sections']['caisse']['items'], 'key');
        $this->assertContains('caisse.pos', $keys);
        $this->assertNotContains('caisse.pos', array_column($menu['sections']['ventes']['items'], 'key'));
    }
}
