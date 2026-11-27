<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Factures</h1>
        <a href="{{ route('factures.create') }}" wire:navigate class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">+ Ajouter une facture</a>
    </div>


    <x-list-totals :totals="$totals" />

    @if (count($selected) > 0)
        <div class="flex flex-wrap items-center justify-end gap-3 mb-3 px-4 py-3 rounded-lg bg-[#ffcc00]/20 border border-amber-300">
            <span class="me-auto text-sm font-semibold text-zinc-900">{{ count($selected) }} facture{{ count($selected) > 1 ? 's' : '' }} sélectionnée{{ count($selected) > 1 ? 's' : '' }}</span>
            <a href="{{ route('factures.pdf-selection', ['ids' => implode(',', $selected)]) }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 bg-zinc-900 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-zinc-700"><x-sp-icon name="printer" class="w-4 h-4" /> Imprimer la sélection</a>
            <button wire:click="deleteSelected" wire:confirm="Supprimer les {{ count($selected) }} facture(s) sélectionnée(s) ?" class="flex items-center gap-1.5 bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-600"><x-sp-icon name="trash" class="w-4 h-4" /> Supprimer la sélection ({{ count($selected) }})</button>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <x-list-toolbar :print-url="$printUrl" placeholder="N° de facture, BL, client, code client…" />
        <div class="hidden md:block overflow-x-auto">
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
                        <td class="px-4 py-4 text-sm text-gray-700">{{ \App\Support\SalesLists::clientLabel($facture->client) }}@if ($facture->client?->code)<span class="block text-xs text-gray-400">{{ $facture->client->code }}</span>@endif</td>
                        <td class="px-4 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $facture->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-4 text-sm text-right text-gray-900 whitespace-nowrap">{{ \App\Support\Fmt::money($facture->total_ttc) }}</td>
                        <td class="px-4 py-4 text-sm text-right {{ $facture->resteAPayer() > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }} whitespace-nowrap">{{ \App\Support\Fmt::money($facture->resteAPayer()) }}</td>
                        <td class="px-4 py-4 text-sm"><span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-gray-100 text-gray-600' => $facture->status === 'brouillon',
                                'bg-blue-100 text-blue-700' => $facture->status === 'envoyee',
                                'bg-amber-100 text-amber-700' => $facture->status === 'partiellement_payee',
                                'bg-green-100 text-green-700' => $facture->status === 'payee',
                                'bg-red-100 text-red-700' => $facture->status === 'en_retard',
                            ])>{{ \App\Support\Fmt::status($facture->status) }}</span></td>
                        <td class="px-4 py-4 text-right text-sm whitespace-nowrap"><div class="inline-flex items-center gap-2"><a href="{{ route('factures.pdf', $facture->id) }}" target="_blank" title="Ouvrir le PDF" class="inline-flex items-center gap-1 bg-[#d92d20] text-white px-2 py-1 rounded-md text-xs font-bold hover:bg-[#b42318]"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h1.5a1.5 1.5 0 0 1 0 3H8v-3zm0 3v2M13 13v5h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2h-1z" stroke-width="1.4"/></svg>PDF</a>@include('livewire.factures._actions')</div></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-6 py-8 text-center text-sm text-gray-400">Aucune facture trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- phone: one card per document (2026-11-26) --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse ($factures as $facture)
                <div class="p-4" wire:key="fac-m-{{ $facture->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <label class="min-w-0 flex items-center gap-3"><input type="checkbox" wire:model.live="selected" value="{{ $facture->id }}" class="h-4 w-4 rounded border-gray-300 text-zinc-900 focus:ring-zinc-900"><span class="font-semibold text-gray-900">{{ $facture->numero }}</span></label>
                        <div class="text-end shrink-0"><div class="font-bold text-gray-900 tabular-nums">{{ \App\Support\Fmt::money($facture->total_ttc) }}</div>@if ($facture->resteAPayer() > 0)<div class="text-xs font-semibold text-red-600">Reste {{ \App\Support\Fmt::money($facture->resteAPayer()) }}</div>@endif<div class="mt-1"><span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-gray-100 text-gray-600' => $facture->status === 'brouillon',
                                'bg-blue-100 text-blue-700' => $facture->status === 'envoyee',
                                'bg-amber-100 text-amber-700' => $facture->status === 'partiellement_payee',
                                'bg-green-100 text-green-700' => $facture->status === 'payee',
                                'bg-red-100 text-red-700' => $facture->status === 'en_retard',
                            ])>{{ \App\Support\Fmt::status($facture->status) }}</span></div></div>
                    </div>
                    <div class="mt-1 text-sm text-gray-600">{{ \App\Support\SalesLists::clientLabel($facture->client) }}@if ($facture->client?->code)<span class="block text-xs text-gray-400">{{ $facture->client->code }}</span>@endif<span class="text-xs text-gray-400">{{ $facture->date->format('d/m/Y') }}@if ($facture->bonLivraison) · {{ $facture->bonLivraison->numero }}@endif</span></div>
                    <div class="mt-3 flex items-center justify-end gap-2"><a href="{{ route('factures.pdf', $facture->id) }}" target="_blank" title="Ouvrir le PDF" class="inline-flex items-center gap-1 bg-[#d92d20] text-white px-2 py-1 rounded-md text-xs font-bold hover:bg-[#b42318]"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h1.5a1.5 1.5 0 0 1 0 3H8v-3zm0 3v2M13 13v5h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2h-1z" stroke-width="1.4"/></svg>PDF</a>@include('livewire.factures._actions')</div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-400">Aucun résultat.</div>
            @endforelse
        </div>

        <div class="p-4">{{ $factures->links() }}</div>
    </div>
    @include('livewire.partials.send-document-modal')
</div>
