<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Facture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_company_never_sees_another_companys_data(): void
    {
        $this->companyUser('FR');
        $c = $this->client('Client de A');
        $f = Facture::create(['client_id' => $c->id, 'numero' => 'FAC-A1', 'date' => now(), 'tva_rate' => 20, 'status' => 'brouillon', 'remise_type' => 'percent', 'remise_value' => 0]);

        $this->companyUser('MA'); // second company, now logged in as its admin
        $this->assertSame(0, Client::count());
        $this->assertNull(Facture::find($f->id));
        $this->get(route('factures.pdf', $f->id))->assertNotFound();
        $this->get(route('factures.edit', $f->id))->assertNotFound();
    }

    public function test_demo_and_real_rows_of_the_same_project_are_separated(): void
    {
        $u = $this->companyUser();
        $this->client('Réel');
        $u->forceFill(['is_demo' => true])->save();
        $this->actingAs($u->fresh());
        $this->assertSame(0, Client::count());
    }

    public function test_superadmin_page_is_forbidden_to_company_admins(): void
    {
        $this->companyUser();
        $this->get('/inscriptions')->assertForbidden();
    }
}
