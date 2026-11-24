<x-app-layout>
    <x-slot name="header">
        <h2 class="ind-h text-3xl sm:text-4xl uppercase leading-none text-[#111]">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (auth()->user()->is_demo)
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg text-sm text-gray-600">Compte de démonstration : le profil, le mot de passe et la suppression du compte sont désactivés. Les données de la démo sont réinitialisées chaque nuit.</div>
            @else
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
