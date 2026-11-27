<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ToastsTest extends TestCase
{
    use RefreshDatabase;

    public function test_actions_send_a_green_or_red_notification(): void
    {
        $this->companyUser('MA');

        // validation error -> red, with the message
        Livewire::test(\App\Livewire\Clients\Index::class)->call('create')->call('save')
            ->assertDispatched('stk-toast', fn ($n, $p) => $p['type'] === 'error' && str_contains($p['message'], 'Vérifiez le formulaire'));

        // saved -> green
        Livewire::test(\App\Livewire\Clients\Index::class)->call('create')->set('name', 'Toast SARL')->call('save')
            ->assertDispatched('stk-toast', fn ($n, $p) => $p['type'] === 'success');

        // deleted -> green ; opening a form -> nothing
        $c = \App\Models\Client::where('name', 'Toast SARL')->sole();
        Livewire::test(\App\Livewire\Clients\Index::class)->call('create')->assertNotDispatched('stk-toast');
        Livewire::test(\App\Livewire\Clients\Index::class)->call('delete', $c->id)
            ->assertDispatched('stk-toast', fn ($n, $p) => $p['type'] === 'success');
    }
}
