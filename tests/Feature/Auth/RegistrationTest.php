<?php

namespace Tests\Feature\Auth;

use App\Models\Project;
use App\Models\Societe;
use App\Notifications\WelcomeStokito;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_company_can_sign_up(): void
    {
        Notification::fake();

        $component = Volt::test('pages.auth.register')
            ->set('company', 'Menuiserie Test')
            ->set('country', 'FR')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('phone', '06 12 34 56 78')
            ->set('password', 'Password-123!')
            ->set('password_confirmation', 'Password-123!')
            ->set('terms', true);

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = auth()->user();
        $project = Project::find($user->project_id);
        $this->assertSame('self', $project->signup_source);
        $this->assertSame('FR', $project->country_code);
        $societe = Societe::withoutGlobalScopes()->where('project_id', $project->id)->first();
        $this->assertSame('EUR', $societe->currency);
        $this->assertSame('Menuiserie Test', $societe->name);
        Notification::assertSentTo($user, WelcomeStokito::class);
    }

    public function test_terms_must_be_accepted(): void
    {
        Volt::test('pages.auth.register')
            ->set('company', 'X')->set('country', 'FR')->set('name', 'Y')->set('email', 'y@example.com')
            ->set('password', 'Password-123!')->set('password_confirmation', 'Password-123!')
            ->call('register')
            ->assertHasErrors(['terms']);

        $this->assertGuest();
    }
}
