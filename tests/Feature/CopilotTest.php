<?php

namespace Tests\Feature;

use App\Livewire\Copilot\Chat;
use App\Models\AiScan;
use App\Support\Caisse;
use App\Support\Copilot;
use App\Support\CopilotTools;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CopilotTest extends TestCase
{
    use RefreshDatabase;

    protected function fake(string $text = 'Voici.', array $actions = []): object
    {
        $fake = new class extends Copilot
        {
            public array $answer = [];

            public array $seen = [];

            public function isConfigured(): bool
            {
                return true;
            }

            public function reply(array $history, string $message): array
            {
                $this->seen[] = [$history, $message];

                return $this->answer + ['input_tokens' => 1200, 'output_tokens' => 300];
            }
        };
        $fake->answer = ['text' => $text, 'actions' => $actions];
        $this->app->instance(Copilot::class, $fake);

        return $fake;
    }

    public function test_prepared_quote_opens_prefilled_and_is_not_saved(): void
    {
        $this->companyUser('FR');
        $c = $this->client('Bâti Plus SARL');
        $p = $this->product('FER-10', 100, 5, 12);   // 12 € TTC, 20 % -> 10 € HT

        $this->assertSame($p->id, CopilotTools::produits('fer-10')[0]['id']);
        $this->assertSame($c->id, CopilotTools::clients('bâti')[0]['id']);
        [$res, $action] = CopilotTools::run('preparer_document', ['type' => 'devis', 'client_id' => $c->id, 'lignes' => [['product_id' => $p->id, 'quantite' => 50]]]);
        $this->assertTrue($res['ok']);
        $this->assertStringContainsString('500,00', $res['total_ht']);
        $this->assertSame(0, \App\Models\Devis::count());

        $key = substr($action['url'], strpos($action['url'], 'copilot=') + 8);
        Livewire::withQueryParams(['copilot' => $key])->test(\App\Livewire\Devis\Form::class)
            ->assertSet('client_id', $c->id)
            ->assertSet('items.0.product_id', $p->id)
            ->assertSet('items.0.quantity', 50.0)
            ->assertSet('items.0.unit_price', 10.0);
        $this->assertSame(0, \App\Models\Devis::count());

        // the key is single-use
        Livewire::withQueryParams(['copilot' => $key])->test(\App\Livewire\Devis\Form::class)->assertSet('client_id', '');
    }

    public function test_purchase_order_uses_the_supplier_and_purchase_price(): void
    {
        $this->companyUser('MA');
        $f = \App\Models\Fournisseur::create(['name' => 'Sonasid Distribution']);
        $p = $this->product('TUB', 10, 18.5, 30);
        [$res, $action] = CopilotTools::run('preparer_document', ['type' => 'bon_commande', 'fournisseur_id' => $f->id, 'lignes' => [['product_id' => $p->id, 'quantite' => 4]]]);
        $this->assertTrue($res['ok']);
        $key = substr($action['url'], strpos($action['url'], 'copilot=') + 8);
        Livewire::withQueryParams(['copilot' => $key])->test(\App\Livewire\CommandeFournisseurs\Form::class)
            ->assertSet('fournisseur_id', $f->id)->assertSet('items.0.unit_price', 18.5);
    }

    public function test_a_prefill_belongs_to_its_user_and_its_form(): void
    {
        $this->companyUser('FR');
        $p = $this->product('A');
        [, $action] = CopilotTools::run('preparer_document', ['type' => 'facture', 'lignes' => [['product_id' => $p->id, 'quantite' => 1]]]);
        $key = substr($action['url'], strpos($action['url'], 'copilot=') + 8);

        Livewire::withQueryParams(['copilot' => $key])->test(\App\Livewire\Devis\Form::class)->assertSet('items.0.product_id', '');  // wrong form
        $this->companyUser('FR');
        Livewire::withQueryParams(['copilot' => $key])->test(\App\Livewire\Factures\Form::class)->assertSet('items.0.product_id', '');  // other user
    }

    public function test_tools_respect_permissions_and_tenant(): void
    {
        $this->companyUser('FR', [], ['role' => 'user', 'permissions' => ['copilot', 'produits_consulter']]);
        $p = $this->product('X');
        [$res] = CopilotTools::run('preparer_document', ['type' => 'devis', 'lignes' => [['product_id' => $p->id, 'quantite' => 1]]]);
        $this->assertStringContainsString('Accès refusé', $res['erreur']);
        [$res] = CopilotTools::run('statistiques', ['periode' => 'mois']);
        $this->assertStringContainsString('Accès refusé', $res['erreur']);
        $this->assertArrayNotHasKey('prix_achat_ht', CopilotTools::produits('X')[0]);

        $this->companyUser('FR');
        $this->assertSame([], CopilotTools::produits('X'));   // other company's product is invisible
        [$res] = CopilotTools::run('preparer_document', ['type' => 'devis', 'lignes' => [['product_id' => $p->id, 'quantite' => 1]]]);
        $this->assertArrayHasKey('erreur', $res);
    }

    public function test_statistics_include_invoices_and_till(): void
    {
        $this->companyUser('FR', ['caisse_enabled' => true]);
        $p = $this->product('S', 10, 5, 24);
        Caisse::encaisser([Caisse::lineFor($p, 2)], ['carte' => 48]);
        $s = CopilotTools::stats('aujourd_hui');
        $this->assertSame(\App\Support\Fmt::money(48), $s['chiffre_affaires_ttc']);
        $this->assertSame(1, $s['nombre_tickets_caisse']);
        $this->assertSame('Produit S', $s['meilleurs_produits_ht'][0]['produit']);
    }

    public function test_chat_answers_logs_usage_and_keeps_the_conversation(): void
    {
        $this->companyUser('FR');
        $fake = $this->fake('Votre devis est prêt.', [['label' => 'Ouvrir le devis pré-rempli', 'url' => '/devis/create?copilot=abc12345']]);

        Livewire::test(Chat::class)
            ->set('input', 'Devis pour Bâti Plus')->call('send')
            ->assertSee('Votre devis est prêt.')->assertSee('Ouvrir le devis pré-rempli')
            ->set('input', 'merci')->call('send');
        $this->assertSame(2, AiScan::where('kind', 'copilot')->where('status', 'ok')->count());
        $this->assertSame('Devis pour Bâti Plus', $fake->seen[1][0][0]['text']);   // history sent with the 2nd message
        $this->assertCount(4, session('copilot.thread'));
    }

    public function test_an_invoice_dropped_in_the_chat_becomes_a_prefilled_purchase_order(): void
    {
        $this->companyUser('FR');
        $sup = \App\Models\Fournisseur::create(['name' => 'Quincaillerie Rhône Pro', 'ice' => '81234567800027']);
        $p = $this->product('VIS-440');
        $fake = new class extends \App\Support\InvoiceScanner
        {
            public function isConfigured(): bool
            {
                return true;
            }

            public function scan(string $path, string $mime): array
            {
                return ['input_tokens' => 4000, 'output_tokens' => 600, 'data' => [
                    'est_une_facture' => true, 'fournisseur' => ['nom' => 'Quincaillerie Rhône Pro', 'identifiant' => '812 345 678 00027', 'adresse' => '', 'telephone' => '', 'email' => ''],
                    'numero_facture' => 'FA-24-11872', 'date' => '2026-11-18', 'devise' => 'EUR',
                    'lignes' => [['designation' => 'Vis inox', 'reference' => 'VIS-440', 'code_barres' => '', 'quantite' => 10, 'prix_unitaire_ht' => 6.5, 'taux_tva' => 20, 'total_ht' => 65],
                        ['designation' => 'Frais de port', 'reference' => '', 'code_barres' => '', 'quantite' => 1, 'prix_unitaire_ht' => 12, 'taux_tva' => 20, 'total_ht' => 12]],
                    'remise_globale_ht' => null, 'total_ht' => 77, 'total_tva' => 15.4, 'total_ttc' => 92.4]];
            }
        };
        $this->app->instance(\App\Support\InvoiceScanner::class, $fake);

        $c = Livewire::test(Chat::class)->set('attachment', \Illuminate\Http\UploadedFile::fake()->image('facture.jpg'))
            ->assertSee('Facture lue')->assertSee('Ouvrir le bon de commande pré-rempli');
        $this->assertSame('ok', AiScan::where('kind', 'facture_fournisseur')->sole()->status);
        $url = collect($c->get('thread'))->last()['actions'][0]['url'];
        $key = substr($url, strpos($url, 'copilot=') + 8);

        Livewire::withQueryParams(['copilot' => $key])->test(\App\Livewire\CommandeFournisseurs\Form::class)
            ->assertSet('fournisseur_id', $sup->id)
            ->assertSet('reference_fournisseur', 'FA-24-11872')
            ->assertSet('date', '2026-11-18')
            ->assertSet('items.0.product_id', $p->id)
            ->assertSet('items.1.scan.status', 'new');
        $this->assertSame(0, \App\Models\CommandeFournisseur::count());
    }

    public function test_demo_and_monthly_limit_block_the_copilot(): void
    {
        $this->companyUser('FR', [], ['is_demo' => true]);
        $fake = $this->fake();
        Livewire::test(Chat::class)->set('input', 'bonjour')->call('send')->assertSee('désactivé dans la démo');
        $this->assertSame([], $fake->seen);

        config(['services.anthropic.copilot_per_month' => 1]);
        $this->companyUser('FR');
        AiScan::create(['kind' => 'copilot', 'status' => 'ok']);
        Livewire::test(Chat::class)->set('input', 'bonjour')->call('send')->assertSee('messages Copilot de ce mois');
        $this->assertSame([], $fake->seen);
    }
}
