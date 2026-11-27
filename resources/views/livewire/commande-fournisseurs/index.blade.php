<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Bons de commande') }}</h1>
        <a href="{{ route('commande-fournisseurs.create') }}" wire:navigate class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">+ {{ __('Ajouter un bon de commande') }}</a>
    </div>


    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher par numéro ou fournisseur..." class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($commandes as $commande)
                    <tr wire:key="cf-{{ $commande->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $commande->numero }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $commande->fournisseur->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $commande->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900 whitespace-nowrap">{{ \App\Support\Fmt::money($commande->total) }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-gray-100 text-gray-600' => $commande->status === 'draft',
                                'bg-blue-100 text-blue-700' => $commande->status === 'ordered',
                                'bg-green-100 text-green-700' => $commande->status === 'received',
                            ])>{{ \App\Support\Fmt::status($commande->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                            <a href="{{ route('commande-fournisseurs.pdf', $commande->id) }}" target="_blank" title="Ouvrir le PDF" class="inline-flex items-center gap-1 border border-zinc-200 bg-[#d92d20] text-white px-2 py-1 text-xs font-bold hover:bg-[#b42318] rounded-lg"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h1.5a1.5 1.5 0 0 1 0 3H8v-3zm0 3v2M13 13v5h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2h-1z" stroke-width="1.4"/></svg>PDF</a>
                            <x-row-actions>
                                <a href="{{ route('commande-fournisseurs.edit', $commande->id) }}" wire:navigate>Modifier</a>
                                @if ($commande->status !== 'received')
                                    <button type="button" wire:click="markReceived({{ $commande->id }})" wire:confirm="Marquer cette commande comme reçue ? Le stock sera mis à jour." class="!font-bold">Marquer reçue</button>
                                @endif
                                <button type="button" wire:click="delete({{ $commande->id }})" wire:confirm="Supprimer cette commande ?" class="!text-[#b42318] border-t border-zinc-200">Supprimer</button>
                            </x-row-actions>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">Aucune commande.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $commandes->links() }}</div>
    </div>
</div>
