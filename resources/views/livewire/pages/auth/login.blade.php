<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h2 class="ind-h text-4xl uppercase leading-none mb-2">{{ __('Connexion') }}</h2>
    <p class="text-sm text-[#555] mb-6">{{ __('Accedez a votre espace Stokito') }}</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-7 pt-6 border-t-2 border-dashed border-[#111]/30 text-center">
        <p class="text-sm text-[#555]">{{ __('Pas encore de compte ?') }}</p>
        <div class="mt-3 flex flex-wrap justify-center gap-2">
            <a href="{{ route('register') }}" wire:navigate class="inline-flex items-center gap-2 border-2 border-[#111] bg-[#ffcc00] px-4 py-2 ind-h text-lg uppercase tracking-wider hover:bg-white">{{ __('Créer un compte gratuit') }}</a>
            <a href="{{ route('demo.login') }}" class="inline-flex items-center gap-2 border-2 border-[#111] bg-white px-4 py-2 ind-h text-lg uppercase tracking-wider hover:bg-[#ffcc00]">{{ __('Tester la démo') }}</a>
        </div>
    </div>
</div>
