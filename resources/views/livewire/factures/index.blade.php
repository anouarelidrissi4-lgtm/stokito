<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Factures</h1>
        <a href="{{ route('factures.create') }}" wire:navigate class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">+ Créer une facture</a>
    </div>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg p-4 mb-4">
        <div class="flex flex-col lg:flex-row lg:items-end gap-3">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Recherche :</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="N° facture, BL, client" class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                <input wire:model.live="dateStart" type="date" class="rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                <input wire:model.live="dateEnd" type="date" class="rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="bg-indigo-600 text-white h-9 px-4 rounded-md text-sm font-semibold hover:bg-indigo-700 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg>
                    Rechercher
                </button>
                <button wire:click="resetFilters" type="button" class="border border-gray-300 text-gray-700 h-9 px-4 rounded-md text-sm font-semibold hover:bg-gray-50">
                    Réinitialiser
                </button>
            </div>
        </div>
    </div>

    @if (count($selected) > 0)
        <div class="flex justify-end mb-3">
            <button wire:click="deleteSelected" wire:confirm="Supprimer les {{ count($selected) }} facture(s) selectionnee(s) ?" class="flex items-center gap-1.5 bg-red-400 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                Supprimer selection ({{ count($selected) }})
            </button>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 w-10"><input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300"></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° facture</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">BL</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant total</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reste</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($factures as $facture)
                    <tr wire:key="fac-{{ $facture->id }}">
                        <td class="px-4 py-4"><input type="checkbox" wire:model.live="selected" value="{{ $facture->id }}" class="rounded border-gray-300"></td>
                        <td class="px-4 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $facture->numero }}</td>
                        <td class="px-4 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $facture->bonLivraison->numero ?? '-' }}</td>
                        <td class="px-4 py-4 text-sm text-gray-700">{{ $facture->client->name ?? '-' }}</td>
                        <td class="px-4 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $facture->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-4 text-sm text-right text-gray-900 whitespace-nowrap">{{ \App\Support\Fmt::money($facture->total_ttc) }}</td>
                        <td class="px-4 py-4 text-sm text-right {{ $facture->resteAPayer() > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }} whitespace-nowrap">{{ \App\Support\Fmt::money($facture->resteAPayer()) }}</td>
                        <td class="px-4 py-4 text-sm">
                            <span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-gray-100 text-gray-600' => $facture->status === 'brouillon',
                                'bg-blue-100 text-blue-700' => $facture->status === 'envoyee',
                                'bg-amber-100 text-amber-700' => $facture->status === 'partiellement_payee',
                                'bg-green-100 text-green-700' => $facture->status === 'payee',
                                'bg-red-100 text-red-700' => $facture->status === 'en_retard',
                            ])>{{ \App\Support\Fmt::status($facture->status) }}</span>
                        </td>
                        <td class="px-4 py-4 text-right text-sm whitespace-nowrap"><div class="inline-flex items-center gap-2">
                            <a href="{{ route('factures.pdf', $facture->id) }}" target="_blank" title="Ouvrir le PDF" class="inline-flex items-center gap-1 border border-zinc-200 bg-[#d92d20] text-white px-2 py-1 text-xs font-bold hover:bg-[#b42318] rounded-lg"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h1.5a1.5 1.5 0 0 1 0 3H8v-3zm0 3v2M13 13v5h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2h-1z" stroke-width="1.4"/></svg>PDF</a>
                            <x-row-actions>
                                <a href="{{ route('factures.edit', $facture->id) }}" wire:navigate>Modifier</a>
                                @if ($facture->resteAPayer() > 0)
                                    <a href="{{ route('reglements-clients.create', ['factureId' => $facture->id]) }}" wire:navigate class="!font-bold">Encaisser</a>
                                @endif
                                <button type="button" wire:click="openSend({{ $facture->id }})" title="{{ $facture->sent_at ? 'Envoyée le '.$facture->sent_at->format('d/m/Y H:i') : 'Envoyer par email' }}">{{ $facture->sent_at ? 'Renvoyer par email' : 'Envoyer par email' }}</button>
                                @if (\App\Support\WhatsAppShare::isLate($facture) && ($wa = \App\Support\Relance::whatsappUrl($facture)))
                                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="!text-[#0a6b37]" title="Relancer le client sur WhatsApp (avec le lien vers la facture)">Relancer sur WhatsApp</a>
                                @else
                                    <a href="{{ \App\Support\WhatsAppShare::url($facture) }}" target="_blank" rel="noopener" class="!text-[#0a6b37]" title="Envoyer la facture sur WhatsApp (lien vers le PDF)">WhatsApp</a>
                                @endif
                                <button type="button" wire:click="delete({{ $facture->id }})" wire:confirm="Supprimer cette facture ?" class="!text-[#b42318] border-t border-zinc-200">Supprimer</button>
                            </x-row-actions>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-6 py-8 text-center text-sm text-gray-400">Aucune facture trouvee.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $factures->links() }}</div>
    </div>
    @include('livewire.partials.send-document-modal')
</div>
