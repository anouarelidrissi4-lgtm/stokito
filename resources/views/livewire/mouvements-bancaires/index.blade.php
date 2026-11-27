<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Mouvements Bancaires</h1>
        <button wire:click="create" class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">+ Mouvement manuel</button>
    </div>


    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <select wire:model.live="compteFilter" class="w-full sm:w-64 rounded-md border-gray-300 text-sm">
                <option value="">Tous les comptes</option>
                @foreach ($comptes as $compte)
                    <option value="{{ $compte->id }}">{{ $compte->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Compte</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Entrée</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sortie</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($mouvements as $mvt)
                    <tr wire:key="mvt-{{ $mvt->id }}">
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $mvt->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $mvt->tresorerieCompte->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $mvt->description ?? ucfirst(str_replace('_', ' ', $mvt->reference_type)) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-green-700 font-semibold whitespace-nowrap">{{ $mvt->type === 'entry' ? \App\Support\Fmt::money($mvt->montant) : '' }}</td>
                        <td class="px-6 py-4 text-sm text-right text-red-700 font-semibold whitespace-nowrap">{{ $mvt->type === 'exit' ? \App\Support\Fmt::money($mvt->montant) : '' }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            @if ($mvt->reference_type === 'manuel')
                                <button wire:click="delete({{ $mvt->id }})" wire:confirm="Supprimer ce mouvement ?" class="text-red-600 hover:underline">Supprimer</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">Aucun mouvement.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $mouvements->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">Nouveau mouvement manuel</h2>

                <label class="block text-sm font-medium text-gray-700 mb-1">Compte</label>
                <select wire:model="tresorerie_compte_id" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    <option value="">-- Choisir --</option>
                    @foreach ($comptes as $compte)
                        <option value="{{ $compte->id }}">{{ $compte->name }}</option>
                    @endforeach
                </select>
                @error('tresorerie_compte_id') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Type</label>
                <select wire:model="type" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    <option value="entry">Entrée</option>
                    <option value="exit">Sortie</option>
                </select>

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Montant</label>
                <input wire:model="montant" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @error('montant') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Date</label>
                <input wire:model="date" type="date" class="w-full rounded-md border-gray-300 text-sm mb-1">

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Description</label>
                <input wire:model="description" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif
</div>
