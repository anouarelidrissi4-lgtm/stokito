<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
        <button wire:click="create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">+ Nouveau Compte</button>
    </div>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher..." class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                    @if (! $typeFilter)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    @endif
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Solde Initial</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Solde Actuel</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($comptes as $compte)
                    <tr wire:key="compte-{{ $compte->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $compte->name }}@if($compte->bank_name) <span class="text-gray-400">({{ $compte->bank_name }})</span>@endif</td>
                        @if (! $typeFilter)
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $compte->type === 'banque' ? 'Banque' : 'Caisse' }}</td>
                        @endif
                        <td class="px-6 py-4 text-sm text-right text-gray-500 whitespace-nowrap">{{ \App\Support\Fmt::money($compte->initial_balance) }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold {{ $compte->solde_actuel < 0 ? 'text-red-600' : 'text-gray-900' }} whitespace-nowrap">{{ \App\Support\Fmt::money($compte->solde_actuel) }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $compte->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $compte->is_active ? 'Actif' : 'Inactif' }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <button wire:click="edit({{ $compte->id }})" class="text-indigo-600 hover:underline">Modifier</button>
                            <button wire:click="delete({{ $compte->id }})" wire:confirm="Supprimer ce compte ?" class="text-red-600 hover:underline">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">Aucun compte.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $comptes->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">{{ $editingId ? 'Modifier' : 'Nouveau' }} compte</h2>

                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <input wire:model="name" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @error('name') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                @if (! $typeFilter)
                    <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Type</label>
                    <select wire:model="type" class="w-full rounded-md border-gray-300 text-sm mb-1">
                        <option value="caisse">Caisse (especes)</option>
                        <option value="banque">Banque</option>
                    </select>
                @endif

                @if ($type === 'banque')
                    <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Banque</label>
                    <input wire:model="bank_name" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">

                    <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">RIB</label>
                    <input wire:model="rib" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @endif

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Solde Initial</label>
                <input wire:model="initial_balance" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @error('initial_balance') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-2">
                    <input wire:model="is_active" type="checkbox" class="rounded border-gray-300">
                    Compte actif
                </label>

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif
</div>
