<?php

use App\Models\Project;
use App\Models\Societe;
use App\Models\User;
use App\Support\Countries;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

/**
 * Free self-service sign-up (2026-11-23). One form creates the company space:
 * Project + its Societe (country presets) + the admin user, then logs in.
 */
new #[Layout('layouts.guest')] class extends Component
{
    public string $company = '';
    public string $country = 'FR';
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;
    public string $website = ''; // honeypot, must stay empty

    public function register(): void
    {
        $key = 'signup:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', __('Trop de tentatives. Réessayez dans une heure.'));

            return;
        }
        RateLimiter::hit($key, 3600);
        abort_if($this->website !== '', 422);

        $v = $this->validate([
            'company' => ['required', 'string', 'max:120'],
            'country' => ['required', Rule::in(array_keys(Countries::ALL))],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $c = Countries::get($v['country']);
        $user = DB::transaction(function () use ($v, $c) {
            $project = Project::create(['name' => $v['company'], 'is_active' => true]);
            $project->forceFill(['country_code' => $v['country'], 'contact_phone' => $v['phone'] ?: null, 'signup_source' => 'self'])->save();

            $user = new User(['name' => $v['name'], 'email' => $v['email'], 'password' => Hash::make($v['password']), 'role' => 'admin', 'status' => 'active']);
            $user->forceFill(['project_id' => $project->id, 'is_demo' => false, 'is_superadmin' => false])->save();

            $societe = new Societe([
                'name' => $v['company'], 'country' => $c['name'], 'phone' => $v['phone'] ?: null, 'email' => $v['email'],
                'currency' => $c['currency'], 'tva_rate_default' => $c['vat'], 'qty_decimals' => 2, 'price_decimals' => $c['decimals'],
                'facture_type' => 'avec_entete', 'show_ref_facture' => true, 'show_ref_bl' => true, 'bons_chargement_enabled' => false,
            ]);
            $societe->forceFill(['project_id' => $project->id, 'is_demo' => false, 'country_code' => $v['country']])->save();

            return $user;
        });

        event(new Registered($user));
        // welcome email; never block the sign-up if the mail server is down
        try {
            $user->notify(new \App\Notifications\WelcomeStokito($v['company']));
        } catch (\Throwable $e) {
            report($e);
        }
        Auth::login($user);
        session()->regenerate();
        session()->flash('welcome', true);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    public function with(): array
    {
        return ['countries' => Countries::options()];
    }
}; ?>

<div>
    <h2 class="ind-h text-3xl leading-none mb-2">{{ __('Ajouter un compte gratuit') }}</h2>
    <p class="text-sm text-zinc-500 mb-6">{{ __('Votre espace est prêt en une minute. Pas de carte bancaire.') }}</p>

    <form wire:submit="register" class="space-y-4" x-data x-init="(() => { const z = { 'Africa/Casablanca': 'MA', 'Europe/Paris': 'FR', 'Europe/Brussels': 'BE', 'Europe/Zurich': 'CH', 'Europe/Luxembourg': 'LU', 'Africa/Dakar': 'SN', 'Africa/Abidjan': 'CI', 'Africa/Douala': 'CM', 'Africa/Tunis': 'TN', 'Africa/Algiers': 'DZ' }[Intl.DateTimeFormat().resolvedOptions().timeZone]; if (z) $wire.set('country', z) })()">
        <input type="text" wire:model="website" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="company" :value="__('Nom de l’entreprise')" />
                <x-text-input wire:model="company" id="company" class="block mt-1 w-full" type="text" required autofocus />
                <x-input-error :messages="$errors->get('company')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="country" :value="__('Pays')" />
                <select wire:model="country" id="country" class="block mt-1 w-full border border-zinc-200 rounded-none text-sm">
                    @foreach ($countries as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('country')" class="mt-2" />
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Votre nom')" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" required autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Téléphone / WhatsApp (facultatif)')" />
                <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" type="tel" autocomplete="tel" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" required autocomplete="new-password" />
            </div>
        </div>

        <label class="flex items-start gap-2 text-sm text-zinc-700 normal-case font-sans" style="font-family:inherit;text-transform:none;font-size:14px!important">
            <input type="checkbox" wire:model="terms" class="mt-1 border border-zinc-200 rounded-lg">
            <span>{!! __('J’accepte les :cgu et la :privacy.', ['cgu' => '<a href="'.route('legal.terms').'" target="_blank" class="underline decoration-amber-400 decoration-2">'.__('conditions d’utilisation').'</a>', 'privacy' => '<a href="'.route('legal.privacy').'" target="_blank" class="underline decoration-amber-400 decoration-2">'.__('politique de confidentialité').'</a>']) !!}</span>
        </label>
        <x-input-error :messages="$errors->get('terms')" class="mt-1" />

        <div class="flex items-center justify-between gap-3 pt-2">
            <a class="underline text-sm text-zinc-500" href="{{ route('login') }}" wire:navigate>{{ __('Déjà un compte ? Se connecter') }}</a>
            <x-primary-button>{{ __('Créer mon compte') }}</x-primary-button>
        </div>
    </form>
</div>
