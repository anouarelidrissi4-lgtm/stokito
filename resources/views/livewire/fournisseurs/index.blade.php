<div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Fournisseurs</h1>
            <button wire:click="create" class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">+ Ajouter un fournisseur</button>
        </div>


        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="p-4 border-b">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher..." class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ \App\Support\Countries::for(\App\Models\Societe::current())['client'] }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Téléphone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($fournisseurs as $f)
                        <tr wire:key="four-{{ $f->id }}">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $f->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $f->ice ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $f->phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $f->email ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-sm space-x-2">
                                <button wire:click="edit({{ $f->id }})" class="text-indigo-600 hover:underline">Modifier</button>
                                <button wire:click="delete({{ $f->id }})" wire:confirm="Supprimer ce fournisseur ?" class="text-red-600 hover:underline">Supprimer</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">Aucun fournisseur.</td></tr>
                    @endforelse
                </tbody>
            </table>
</div>
            <div class="p-4">{{ $fournisseurs->links() }}</div>
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
                <h2 class="text-lg font-semibold mb-4">{{ $editingId ? 'Modifier' : 'Nouveau' }} fournisseur</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input wire:model="name" type="text" class="w-full rounded-md border-gray-300 text-sm">
                        @error('name') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ \App\Support\Countries::for(\App\Models\Societe::current())['client'] }}</label>
                        <input wire:model="ice" type="text" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input wire:model="phone" type="text" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input wire:model="email" type="email" class="w-full rounded-md border-gray-300 text-sm">
                        @error('email') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <textarea wire:model="address" rows="2" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif
</div>
