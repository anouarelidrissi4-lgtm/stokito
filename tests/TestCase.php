<?php

namespace Tests;

use App\Models\Client;
use App\Models\Product;
use App\Models\Project;
use App\Models\Societe;
use App\Models\User;
use App\Support\Countries;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Safety net: the tests rebuild the database, so they must never reach the real one
     * (a cached config would ignore phpunit.xml and point at production MySQL).
     */
    public function createApplication()
    {
        $app = parent::createApplication();
        $c = $app['config'];
        if ($c->get('database.default') !== 'sqlite' || $c->get('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException('Tests refusés : la base n’est pas SQLite en mémoire (config en cache ? php artisan config:clear).');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Societe::flushCurrent();
        $this->withoutVite();
    }

    /** A company (project + admin user + Ma Société) and log in as its admin. */
    protected function companyUser(string $country = 'FR', array $societe = [], array $user = []): User
    {
        $p = Project::create(['name' => 'Test '.uniqid(), 'is_active' => true, 'country_code' => $country]);
        $p->forceFill(['plan' => 'pro', 'plan_ends_at' => null, 'plan_trial' => false])->save();
        $u = new User();
        $u->forceFill(array_merge([
            'name' => 'Admin Test', 'email' => uniqid('u').'@example.test', 'password' => bcrypt('secret-pass-123'),
            'role' => 'admin', 'status' => 'active', 'project_id' => $p->id, 'is_demo' => false, 'email_verified_at' => now(),
        ], $user))->save();
        $this->actingAs($u);
        $c = Countries::get($country);
        Societe::current()->update(array_merge([
            'name' => 'Société Test', 'country_code' => $country, 'country' => $c['name'],
            'currency' => $c['currency'], 'tva_rate_default' => $c['vat'],
        ], $societe));

        return $u;
    }

    protected function product(string $ref, float $stock = 100, float $achat = 5, float $venteTtc = 12): Product
    {
        return Product::create(['reference' => $ref, 'name' => 'Produit '.$ref, 'unit' => 'pièce',
            'purchase_price' => $achat, 'sale_price' => $venteTtc, 'stock_qty' => $stock]);
    }

    protected function client(string $name = 'Client Test', ?string $email = null): Client
    {
        return Client::create(['name' => $name, 'email' => $email]);
    }
}
