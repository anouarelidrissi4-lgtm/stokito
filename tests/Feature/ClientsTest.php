<?php

namespace Tests\Feature;

use App\Livewire\Clients\Index;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_client_gets_a_code_and_a_first_name(): void
    {
        $this->companyUser('FR');
        $this->client('Ancien client');                       // created elsewhere: code given automatically
        $this->assertSame('CL-0001', Client::sole()->code);

        Livewire::test(Index::class)->call('create')->assertSet('code', 'CL-0002')
            ->set('name', 'Martin')->set('prenom', 'Camille')->call('save')->assertHasNoErrors();
        $c = Client::where('name', 'Martin')->sole();
        $this->assertSame('CL-0002', $c->code);
        $this->assertSame('Camille', $c->prenom);
        $this->assertSame('Camille Martin', $c->full_name);

        // own code accepted, duplicate refused (per company)
        Livewire::test(Index::class)->call('create')->set('code', 'VIP-1')->set('name', 'Durand')->call('save')->assertHasNoErrors();
        Livewire::test(Index::class)->call('create')->set('code', 'VIP-1')->set('name', 'Autre')->call('save')->assertHasErrors('code');

        // another company starts again at CL-0001
        $this->companyUser('FR');
        $this->assertSame('CL-0001', $this->client('Ailleurs')->code);
    }

    public function test_search_and_print_list(): void
    {
        $this->companyUser('FR');
        Client::create(['name' => 'Bâti Plus SARL', 'phone' => '0400000001']);
        Client::create(['name' => 'Martin', 'prenom' => 'Camille']);

        Livewire::test(Index::class)->set('search', 'Camille')->assertSee('Martin')->assertDontSee('Bâti Plus');
        $this->get(route('clients.print'))->assertOk()->assertSee('CL-0001')->assertSee('Bâti Plus SARL')->assertSee('Camille');
        $this->get(route('clients.print', ['q' => 'Bâti']))->assertOk()->assertSee('Bâti Plus SARL')->assertDontSee('Camille');
    }
}
