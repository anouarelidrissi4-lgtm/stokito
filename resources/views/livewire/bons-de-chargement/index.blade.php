<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Bons de Chargement</h1>
        <button wire:click="create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">+ Nouveau Bon Chargement</button>
    </div>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher par numéro..." class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicule</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Chauffeur</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Bons Livraison</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($chargements as $chargement)
                    <tr wire:key="bc-{{ $chargement->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $chargement->numero }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $chargement->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $chargement->vehicule ?: '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $chargement->chauffeur ?: '-' }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ $chargement->bon_livraisons_count }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-gray-100 text-gray-600' => $chargement->status === 'preparation',
                                'bg-blue-100 text-blue-700' => $chargement->status === 'charge',
                                'bg-green-100 text-green-700' => $chargement->status === 'livre',
                            ])>{{ \App\Support\Fmt::status($chargement->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <button wire:click="delete({{ $chargement->id }})" wire:confirm="Supprimer ce bon de chargement ?" class="text-red-600 hover:underline">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-sm text-gray-400">Aucun bon de chargement.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $chargements->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">Nouveau bon de chargement</h2>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input wire:model="date" type="date" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select wire:model="status" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="preparation">Preparation</option>
                            <option value="charge">Charge</option>
                            <option value="livre">Livre</option>
                        </select>
                    </div>
                </div>

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Vehicule</label>
                <input wire:model="vehicule" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Chauffeur</label>
                <input wire:model="chauffeur" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Bons de livraison a charger</label>
                @error('selectedBonLivraisons') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror
                <div class="border rounded-md max-h-48 overflow-y-auto divide-y">
                    @forelse ($availableBonLivraisons as $bl)
                        <label class="flex items-center gap-2 px-3 py-2 text-sm">
                            <input type="checkbox" wire:model="selectedBonLivraisons" value="{{ $bl->id }}" class="rounded border-gray-300">
                            <span>{{ $bl->numero }} - {{ $bl->client->name ?? '-' }}</span>
                        </label>
                    @empty
                        <div class="px-3 py-4 text-sm text-gray-400">Aucun bon de livraison disponible.</div>
                    @endforelse
                </div>

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Notes</label>
                <textarea wire:model="notes" rows="2" class="w-full rounded-md border-gray-300 text-sm"></textarea>

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif
</div>
