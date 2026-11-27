<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $user ? 'Modifier' : 'Nouvel' }} utilisateur</h1>
        <a href="{{ route('utilisateurs.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">&larr; Retour</a>
    </div>


    <div class="bg-white shadow rounded-lg p-6 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <input wire:model="last_name" type="text" class="w-full rounded-md border-gray-300 text-sm">
                @error('last_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                <input wire:model="first_name" type="text" class="w-full rounded-md border-gray-300 text-sm">
                @error('first_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                <input wire:model="login" type="text" class="w-full rounded-md border-gray-300 text-sm">
                @error('login') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input wire:model="email" type="email" class="w-full rounded-md border-gray-300 text-sm">
                @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe {{ $user ? '(laisser vide pour ne pas changer)' : '' }}</label>
                <input wire:model="password" type="password" class="w-full rounded-md border-gray-300 text-sm">
                @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="w-full sm:w-64">
            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
            <select wire:model="status" class="w-full rounded-md border-gray-300 text-sm">
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
            </select>
        </div>

        <div class="border-t pt-6">
            <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <span>&#128274;</span> Permissions
            </h2>

            <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 mb-4 cursor-pointer">
                <input type="checkbox" wire:click="toggleAllPermissions" {{ count($permissions) === count(\App\Support\Permissions::ALL) ? 'checked' : '' }} class="rounded border-gray-300">
                Tous selectionner/deselectionner
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-2">
                @foreach ($permissionColumns as $column)
                    <div class="space-y-2">
                        @foreach ($column as $key => $label)
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" wire:model="permissions" value="{{ $key }}" class="rounded border-gray-300">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t">
            <a href="{{ route('utilisateurs.index') }}" wire:navigate class="px-4 py-2 text-sm text-gray-600 rounded-lg border border-gray-300">Annuler</a>
            <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">
                Enregistrer
            </button>
        </div>
    </div>
</div>
