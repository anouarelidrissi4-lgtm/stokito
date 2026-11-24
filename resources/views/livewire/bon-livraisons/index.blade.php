<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Bons de Livraison</h1>
        <a href="{{ route('bon-livraisons.create') }}" wire:navigate class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">+ Nouveau Bon Livraison</a>
    </div>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher par numéro ou client..." class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($bonLivraisons as $bl)
                    <tr wire:key="bl-{{ $bl->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $bl->numero }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $bl->client->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $bl->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900 whitespace-nowrap">{{ \App\Support\Fmt::money($bl->total) }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-gray-100 text-gray-600' => $bl->status === 'draft',
                                'bg-green-100 text-green-700' => $bl->status === 'delivered',
                            ])>{{ \App\Support\Fmt::status($bl->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                            <a href="{{ route('bon-livraisons.pdf', $bl->id) }}" target="_blank" title="Ouvrir le PDF" class="inline-flex items-center gap-1 border-2 border-[#111] bg-[#d92d20] text-white px-2 py-1 text-xs font-bold hover:bg-[#b42318]"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h1.5a1.5 1.5 0 0 1 0 3H8v-3zm0 3v2M13 13v5h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2h-1z" stroke-width="1.4"/></svg>PDF</a>
                            <x-row-actions>
                                <a href="{{ route('bon-livraisons.edit', $bl->id) }}" wire:navigate>Modifier</a>
                                @if ($bl->status === 'draft')
                                    <button type="button" wire:click="markDelivered({{ $bl->id }})" wire:confirm="Marquer ce bon comme livré ? Les quantités seront retirées du stock." class="!font-bold">Marquer livré</button>
                                @endif
                                @if ($bl->facture)
                                    <a href="{{ route('factures.edit', $bl->facture->id) }}" wire:navigate>Voir la facture {{ $bl->facture->numero }}</a>
                                @elseif (auth()->user()->hasPermission('bl_facturer'))
                                    <a href="{{ route('factures.create', ['blId' => $bl->id]) }}" wire:navigate class="!font-bold">Facturer</a>
                                @endif
                                <button type="button" wire:click="delete({{ $bl->id }})" wire:confirm="Supprimer ce bon de livraison ?" class="!text-[#b42318] border-t border-[#e3e0d8]">Supprimer</button>
                            </x-row-actions>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">Aucun bon de livraison.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $bonLivraisons->links() }}</div>
    </div>
</div>
