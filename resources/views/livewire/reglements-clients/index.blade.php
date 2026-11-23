<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Règlements Clients</h1>
        <button wire:click="create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">+ Nouveau Règlement</button>
    </div>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher par client..." class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Facture</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mode</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($reglements as $reglement)
                    <tr wire:key="rc-{{ $reglement->id }}">
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $reglement->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $reglement->client->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $reglement->facture->numero ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ ucfirst($reglement->mode) }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-green-700 whitespace-nowrap">{{ \App\Support\Fmt::money($reglement->montant) }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <button wire:click="delete({{ $reglement->id }})" wire:confirm="Supprimer ce reglement ?" class="text-red-600 hover:underline">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">Aucun règlement.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $reglements->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">Nouveau règlement client</h2>

                <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                <select wire:model="client_id" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    <option value="">-- Choisir --</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('client_id') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Compte trésorerie</label>
                <select wire:model="tresorerie_compte_id" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    <option value="">-- Aucun --</option>
                    @foreach ($comptes as $compte)
                        <option value="{{ $compte->id }}">{{ $compte->name }}</option>
                    @endforeach
                </select>

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Montant</label>
                <input wire:model="montant" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @error('montant') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Date</label>
                <input wire:model="date" type="date" class="w-full rounded-md border-gray-300 text-sm mb-1">

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Mode</label>
                <select wire:model="mode" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    <option value="especes">Espèces</option>
                    <option value="cheque">Chèque</option>
                    <option value="virement">Virement</option>
                    <option value="carte">Carte</option>
                </select>

                @if ($mode === 'cheque')
                    <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Numéro chèque</label>
                    <input wire:model="numero_cheque" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @endif

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif
</div>
