<?php

namespace Tests\Feature;

use App\Livewire\CommandeFournisseurs\Form;
use App\Models\AiScan;
use App\Models\CommandeFournisseur;
use App\Models\Fournisseur;
use App\Support\InvoiceScanException;
use App\Support\InvoiceScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceScannerTest extends TestCase
{
    use RefreshDatabase;

    /** A scanner that answers without calling the API. */
    protected function fakeScanner(array $data, ?InvoiceScanException $fail = null): object
    {
        $fake = new class extends InvoiceScanner
        {
            public array $data = [];

            public ?InvoiceScanException $fail = null;

            public int $calls = 0;

            public function isConfigured(): bool
            {
                return true;
            }

            public function scan(string $path, string $mime): array
            {
                $this->calls++;
                if ($this->fail) {
                    throw $this->fail;
                }

                return ['data' => $this->data, 'input_tokens' => 3000, 'output_tokens' => 1500];
            }
        };
        $fake->data = $data;
        $fake->fail = $fail;
        $this->app->instance(InvoiceScanner::class, $fake);

        return $fake;
    }

    protected function invoice(array $over = []): array
    {
        return array_merge([
            'est_une_facture' => true,
            'fournisseur' => ['nom' => 'Brico Distribution SARL', 'identifiant' => '001234567000089', 'adresse' => '12 rue des Lilas, Lyon', 'telephone' => '04 78 00 00 00', 'email' => 'compta@brico.test'],
            'numero_facture' => 'FA-2026-0412',
            'date' => '2026-11-18',
            'devise' => 'EUR',
            'lignes' => [
                ['designation' => 'Vis inox 4x40 (boîte 100)', 'reference' => 'VIS-440', 'code_barres' => null, 'quantite' => 10, 'prix_unitaire_ht' => 6.5, 'taux_tva' => 20, 'total_ht' => 65],
                ['designation' => 'Cheville nylon 8mm', 'reference' => null, 'code_barres' => null, 'quantite' => 5, 'prix_unitaire_ht' => 3.2, 'taux_tva' => 20, 'total_ht' => 16],
                ['designation' => 'Perceuse sans fil 18V', 'reference' => 'PSF18', 'code_barres' => null, 'quantite' => 1, 'prix_unitaire_ht' => 89, 'taux_tva' => 20, 'total_ht' => 89],
            ],
            'remise_globale_ht' => null, 'total_ht' => 170, 'total_tva' => 34, 'total_ttc' => 204,
        ], $over);
    }

    public function test_scan_fills_the_supplier_date_and_lines_and_matches_products(): void
    {
        $this->companyUser('FR');
        $sup = Fournisseur::create(['name' => 'BRICO DISTRIBUTION', 'ice' => '001 234 567 00089']);
        $vis = $this->product('VIS-440');
        $cheville = \App\Models\Product::create(['reference' => 'CHV8', 'name' => 'Chevilles nylon 8mm', 'unit' => 'pièce', 'purchase_price' => 3, 'sale_price' => 6, 'stock_qty' => 0]);
        $this->fakeScanner($this->invoice());

        $c = Livewire::test(Form::class)
            ->set('scanFile', UploadedFile::fake()->image('facture.jpg', 800, 1100))
            ->assertHasNoErrors()
            ->assertSet('fournisseur_id', $sup->id)
            ->assertSet('date', '2026-11-18')
            ->assertSet('reference_fournisseur', 'FA-2026-0412')
            ->assertSet('scanSupplier', null);

        $items = $c->get('items');
        $this->assertCount(3, $items);
        $this->assertSame($vis->id, $items[0]['product_id']);
        $this->assertSame('ok', $items[0]['scan']['status']);
        $this->assertEquals(6.5, $items[0]['unit_price']);
        $this->assertSame($cheville->id, $items[1]['product_id']);
        $this->assertSame('check', $items[1]['scan']['status']);
        $this->assertSame('', $items[2]['product_id']);
        $this->assertSame('new', $items[2]['scan']['status']);
        $this->assertStringContainsString('3 ligne(s) lue(s)', $c->get('scanMessage'));

        $log = AiScan::sole();
        $this->assertSame('ok', $log->status);
        $this->assertEquals(InvoiceScanner::cost(3000, 1500), (float) $log->cost_usd);

        // "Créer ce produit" opens the modal prefilled with what was read
        $c->call('openProductModal', 2)
            ->assertSet('new_product_name', 'Perceuse sans fil 18V')
            ->assertSet('new_product_reference', 'PSF18')
            ->assertSet('new_product_purchase_price', 89.0)
            ->set('new_product_sale_price', 129)
            ->call('saveProduct')
            ->assertHasNoErrors();
        $this->assertSame('ok', $c->get('items')[2]['scan']['status']);

        $c->call('save')->assertHasNoErrors();
        $cmd = CommandeFournisseur::sole();
        $this->assertSame('FA-2026-0412', $cmd->reference_fournisseur);
        $this->assertCount(3, $cmd->items);
        $this->assertEquals(170, (float) $cmd->total_ht);
    }

    public function test_unknown_supplier_is_offered_for_creation(): void
    {
        $this->companyUser('MA');
        $this->product('VIS-440');
        $this->fakeScanner($this->invoice());

        $c = Livewire::test(Form::class)
            ->set('scanFile', UploadedFile::fake()->image('facture.png'))
            ->assertSet('fournisseur_id', '')
            ->assertSet('scanSupplier.nom', 'Brico Distribution SARL')
            ->call('createScannedSupplier')
            ->assertSet('scanSupplier', null);

        $f = Fournisseur::sole();
        $this->assertSame('Brico Distribution SARL', $f->name);
        $this->assertSame('001234567000089', $f->ice);
        $this->assertSame('compta@brico.test', $f->email);
        $c->assertSet('fournisseur_id', $f->id);
    }

    public function test_demo_account_cannot_scan(): void
    {
        $this->companyUser('FR', [], ['is_demo' => true]);
        $fake = $this->fakeScanner($this->invoice());

        Livewire::test(Form::class)
            ->set('scanFile', UploadedFile::fake()->image('facture.jpg'))
            ->assertHasErrors('scanFile');
        $this->assertSame(0, $fake->calls);
        $this->assertSame(0, AiScan::withoutGlobalScopes()->count());
    }

    public function test_monthly_limit_blocks_the_scan(): void
    {
        config(['services.anthropic.scans_per_month' => 2]);
        $this->companyUser('FR');
        AiScan::create(['kind' => 'facture_fournisseur', 'status' => 'ok']);
        AiScan::create(['kind' => 'facture_fournisseur', 'status' => 'ok']);
        $fake = $this->fakeScanner($this->invoice());

        Livewire::test(Form::class)
            ->set('scanFile', UploadedFile::fake()->image('facture.jpg'))
            ->assertHasErrors('scanFile');
        $this->assertSame(0, $fake->calls);
    }

    public function test_a_failed_reading_shows_the_message_and_is_logged(): void
    {
        $this->companyUser('FR');
        $this->fakeScanner([], new InvoiceScanException('La facture n’a pas pu être lue.', 0, null, 2000, 10));

        Livewire::test(Form::class)
            ->set('scanFile', UploadedFile::fake()->image('facture.jpg'))
            ->assertHasErrors('scanFile')
            ->assertSet('items.0.product_id', '');
        $this->assertSame('failed', AiScan::sole()->status);
        $this->assertSame(0, AiScan::usedThisMonth());
    }

    public function test_a_document_that_is_not_an_invoice_is_refused(): void
    {
        $this->companyUser('FR');
        $this->fakeScanner($this->invoice(['est_une_facture' => false, 'lignes' => []]));

        Livewire::test(Form::class)
            ->set('scanFile', UploadedFile::fake()->image('photo.jpg'))
            ->assertHasErrors('scanFile');
    }

    public function test_the_schema_is_strict_for_structured_outputs(): void
    {
        $walk = function (array $node) use (&$walk) {
            if (($node['type'] ?? null) === 'object') {
                $this->assertFalse($node['additionalProperties']);
                $this->assertEqualsCanonicalizing(array_keys($node['properties']), $node['required']);
                foreach ($node['properties'] as $p) {
                    $walk($p);
                }
            }
            if (isset($node['items'])) {
                $walk($node['items']);
            }
        };
        $walk(InvoiceScanner::schema());
    }
}
