<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Devis Clients</h1>
        <a href="{{ route('devis.create') }}" wire:navigate class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">+ Ajouter un devis</a>
    </div>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b flex flex-col sm:flex-row gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher par numéro ou client..." class="w-full sm:flex-1 rounded-md border-gray-300 text-sm">

            <form wire:submit.prevent="searchByNumero" class="flex gap-2 w-full sm:w-auto">
                <input wire:model="numeroSearch" type="text" placeholder="N&deg; de devis exact..." class="w-full sm:w-48 rounded-md border-gray-300 text-sm">
                <button type="submit" class="shrink-0 bg-gray-100 text-gray-700 px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-200">Rechercher</button>
                @if ($numeroSearch)
                    <button type="button" wire:click="resetNumeroSearch" title="Effacer" class="shrink-0 px-2 text-gray-400 hover:text-gray-600">&times;</button>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total TTC</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($devisList as $devis)
                    <tr wire:key="devis-{{ $devis->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $devis->numero }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $devis->client->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $devis->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900 whitespace-nowrap">{{ \App\Support\Fmt::money($devis->total_ttc) }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-gray-100 text-gray-600' => $devis->status === 'draft',
                                'bg-blue-100 text-blue-700' => $devis->status === 'sent',
                                'bg-green-100 text-green-700' => $devis->status === 'accepted',
                                'bg-red-100 text-red-700' => $devis->status === 'rejected',
                            ])>{{ \App\Support\Fmt::status($devis->status) }}</span>
                            @if ($devis->isExpired())<span class="ms-1 px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800" title="Valable jusqu’au {{ $devis->valid_until->format('d/m/Y') }}">Expiré</span>@endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                            <a href="{{ route('devis.pdf', $devis->id) }}" target="_blank" title="Ouvrir le PDF" class="inline-flex items-center gap-1 border border-zinc-200 bg-[#d92d20] text-white px-2 py-1 text-xs font-bold hover:bg-[#b42318] rounded-lg"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h1.5a1.5 1.5 0 0 1 0 3H8v-3zm0 3v2M13 13v5h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2h-1z" stroke-width="1.4"/></svg>PDF</a>
                            <x-row-actions>
                                @php $converti = $devis->bonLivraisons()->exists() || $devis->factures()->exists(); @endphp
                                @if (! $converti && auth()->user()->hasPermission('devis_valider'))
                                    <button type="button" wire:click="convertToBonLivraison({{ $devis->id }})" wire:confirm="Valider ce devis et créer le bon de livraison ?" class="!font-semibold"><x-sp-icon name="truck" class="w-4 h-4 shrink-0 text-zinc-500" /> Valider en bon de livraison</button>
                                @endif
                                @if (auth()->user()->hasPermission('devis_creer'))
                                    <button type="button" wire:click="duplicate({{ $devis->id }})"><x-sp-icon name="copy" class="w-4 h-4 shrink-0 text-zinc-500" /> Dupliquer</button>
                                @endif
                                <a href="{{ route('devis.edit', $devis->id) }}" wire:navigate><x-sp-icon name="pencil" class="w-4 h-4 shrink-0 text-zinc-500" /> Modifier</a>
                                <a href="{{ route('devis.pdf', $devis->id) }}" target="_blank" rel="noopener"><x-sp-icon name="printer" class="w-4 h-4 shrink-0 text-zinc-500" /> Imprimer</a>
                                @if (! $converti && auth()->user()->hasPermission('devis_valider'))
                                    <button type="button" wire:click="convertToFacture({{ $devis->id }})"><x-sp-icon name="doc" class="w-4 h-4 shrink-0 text-zinc-500" /> Facturer</button>
                                @endif
                                <button type="button" wire:click="openSend({{ $devis->id }})" title="{{ $devis->sent_at ? 'Envoyé le '.$devis->sent_at->format('d/m/Y H:i') : 'Envoyer par email' }}"><x-sp-icon name="send" class="w-4 h-4 shrink-0 text-zinc-500" /> {{ $devis->sent_at ? 'Renvoyer par email' : 'Envoyer par email' }}</button>
                                <a href="{{ \App\Support\WhatsAppShare::url($devis) }}" target="_blank" rel="noopener" class="!text-[#0a6b37]" title="Envoyer le devis sur WhatsApp (lien vers le PDF)"><svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.78 1.22h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.01c-.24.68-1.4 1.3-1.93 1.38-.49.08-1.12.11-1.81-.11-.42-.13-.95-.31-1.64-.6-2.89-1.25-4.78-4.15-4.92-4.34-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.12 1.01-2.41.26-.29.57-.36.76-.36l.55.01c.18.01.41-.07.64.49.24.58.81 2.01.88 2.16.07.15.12.32.02.51-.1.19-.14.31-.28.48-.14.17-.29.37-.41.5-.14.14-.28.3-.12.58.16.28.72 1.19 1.55 1.93 1.07.95 1.96 1.25 2.24 1.39.28.14.44.12.6-.07.16-.19.68-.79.87-1.06.18-.27.37-.22.62-.13.26.09 1.63.77 1.91.91.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg> WhatsApp</a>
                                @if (auth()->user()->hasPermission('devis_supprimer'))
                                    <button type="button" wire:click="delete({{ $devis->id }})" wire:confirm="Supprimer ce devis ?" class="!text-[#b42318] border-t border-zinc-200"><x-sp-icon name="trash" class="w-4 h-4 shrink-0" /> Supprimer</button>
                                @endif
                            </x-row-actions>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">Aucun devis.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $devisList->links() }}</div>
    </div>
    @include('livewire.partials.send-document-modal')
</div>
