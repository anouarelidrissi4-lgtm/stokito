@php $idLabel = \App\Support\Countries::for(\App\Models\Societe::current())['client']; @endphp
<div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Clients</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('clients.print', array_filter(['q' => $search])) }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 bg-white text-zinc-900 border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a1 1 0 001 1h8a1 1 0 001-1v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a1 1 0 00-1-1H6a1 1 0 00-1 1zm2 0h6v3H7V4zm6 10v3H7v-5h6v2z" /></svg>
                    Imprimer
                </a>
                <button wire:click="create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">+ Nouveau client</button>
            </div>
        </div>

        @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
            <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="p-4 border-b">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher : nom, prénom, code, téléphone…" class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code client</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prénom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $idLabel }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Téléphone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($clients as $client)
                        <tr wire:key="client-{{ $client->id }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $client->code ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $client->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $client->prenom ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $client->ice ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $client->phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $client->email ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-sm space-x-2 whitespace-nowrap">
                                <button wire:click="edit({{ $client->id }})" class="text-indigo-600 hover:underline">Modifier</button>
                                <button wire:click="delete({{ $client->id }})" wire:confirm="Supprimer ce client ?" class="text-red-600 hover:underline">Supprimer</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-sm text-gray-400">Aucun client.</td></tr>
                    @endforelse
                </tbody>
            </table>
</div>
            <div class="p-4">{{ $clients->links() }}</div>
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4">{{ $editingId ? 'Modifier' : 'Nouveau' }} client</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code client</label>
                        <input wire:model="code" type="text" placeholder="Automatique" class="w-full rounded-md border-gray-300 text-sm">
                        @error('code') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $idLabel }}</label>
                        <input wire:model="ice" type="text" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-gray-400 font-normal">(ou raison sociale)</span></label>
                        <input wire:model="name" type="text" class="w-full rounded-md border-gray-300 text-sm">
                        @error('name') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                        <input wire:model="prenom" type="text" class="w-full rounded-md border-gray-300 text-sm">
                        @error('prenom') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Limite crédit</label>
                        <input wire:model="credit_limit" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm">
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
