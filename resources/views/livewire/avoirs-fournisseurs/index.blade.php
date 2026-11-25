<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Avoirs Fournisseurs</h1>
        <button wire:click="create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">+ Ajouter un avoir</button>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motif</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($avoirs as $avoir)
                    <tr wire:key="avf-{{ $avoir->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $avoir->numero }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $avoir->fournisseur->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $avoir->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $avoir->motif }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-green-700 whitespace-nowrap">-{{ \App\Support\Fmt::money($avoir->montant) }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <button wire:click="edit({{ $avoir->id }})" class="text-indigo-600 hover:underline">Modifier</button>
                            <button wire:click="delete({{ $avoir->id }})" wire:confirm="Supprimer cet avoir ?" class="text-red-600 hover:underline">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">Aucun avoir.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $avoirs->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">{{ $editingId ? 'Modifier' : 'Nouvel' }} avoir fournisseur</h2>

                <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
                <select wire:model="fournisseur_id" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    <option value="">-- Choisir --</option>
                    @foreach ($fournisseurs as $fournisseur)
                        <option value="{{ $fournisseur->id }}">{{ $fournisseur->name }}</option>
                    @endforeach
                </select>
                @error('fournisseur_id') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Montant</label>
                <input wire:model="montant" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @error('montant') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Date</label>
                <input wire:model="date" type="date" class="w-full rounded-md border-gray-300 text-sm mb-1">

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Motif</label>
                <input wire:model="motif" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif
</div>
