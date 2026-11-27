<div class="py-6 px-4 sm:px-6 lg:px-8"
     x-data="{ sel: [], url: @js(route('bon-livraisons.pdf-selection')),
        toggle(id) { this.sel = this.sel.includes(id) ? this.sel.filter(x => x !== id) : [...this.sel, id] },
        all(ids, on) { this.sel = on ? [...new Set([...this.sel, ...ids])] : this.sel.filter(x => !ids.includes(x)) } }">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Bons de Livraison</h1>
        <a href="{{ route('bon-livraisons.create') }}" wire:navigate class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">+ Ajouter un bon de livraison</a>
    </div>


    <x-list-totals :totals="$totals" />

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <x-list-toolbar :print-url="$printUrl" placeholder="N° de BL, client, code client…" />
        {{-- selected delivery notes -> one PDF, one per page (2026-11-27) --}}
        <div x-show="sel.length" x-cloak class="flex flex-wrap items-center gap-3 px-4 py-3 bg-[#ffcc00]/20 border-b border-amber-300">
            <span class="text-sm font-semibold text-zinc-900" x-text="sel.length + (sel.length > 1 ? ' bons de livraison sélectionnés' : ' bon de livraison sélectionné')"></span>
            <a :href="url + '?ids=' + sel.join(',')" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 h-9 px-4 rounded-lg bg-zinc-900 text-white text-sm font-semibold hover:bg-zinc-700">
                <x-sp-icon name="printer" class="w-4 h-4" /> Imprimer la sélection
            </a>
            <a :href="url + '?sans_prix=1&ids=' + sel.join(',')" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 h-9 px-4 rounded-lg border border-zinc-900 bg-white text-zinc-900 text-sm font-semibold hover:bg-zinc-50">
                <x-sp-icon name="printer" class="w-4 h-4" /> Imprimer sans prix
            </a>
            <button type="button" @click="sel = []" class="text-sm text-zinc-600 underline">Tout désélectionner</button>
        </div>
        <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="ps-6 pe-2 py-3 w-8"><input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-zinc-900 focus:ring-zinc-900" title="Tout sélectionner" aria-label="Tout sélectionner"
                        :checked="@js($bonLivraisons->pluck('id')->values()).length && @js($bonLivraisons->pluck('id')->values()).every(i => sel.includes(i))"
                        @change="all(@js($bonLivraisons->pluck('id')->values()), $event.target.checked)"></th>
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
                    <tr wire:key="bl-{{ $bl->id }}" :class="sel.includes({{ $bl->id }}) ? 'bg-[#ffcc00]/10' : ''">
                        <td class="ps-6 pe-2 py-4"><input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-zinc-900 focus:ring-zinc-900" aria-label="Sélectionner {{ $bl->numero }}" :checked="sel.includes({{ $bl->id }})" @change="toggle({{ $bl->id }})"></td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $bl->numero }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ \App\Support\SalesLists::clientLabel($bl->client) }}@if ($bl->client?->code)<span class="block text-xs text-gray-400">{{ $bl->client->code }}</span>@endif</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $bl->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900 whitespace-nowrap">{{ \App\Support\Fmt::money($bl->total) }}</td>
                        <td class="px-6 py-4 text-sm"><span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-gray-100 text-gray-600' => $bl->status === 'draft',
                                'bg-green-100 text-green-700' => $bl->status === 'delivered',
                            ])>{{ \App\Support\Fmt::status($bl->status) }}</span></td>
                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap"><div class="inline-flex items-center gap-2"><a href="{{ route('bon-livraisons.pdf', $bl->id) }}" target="_blank" title="Ouvrir le PDF" class="inline-flex items-center gap-1 bg-[#d92d20] text-white px-2 py-1 rounded-md text-xs font-bold hover:bg-[#b42318]"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h1.5a1.5 1.5 0 0 1 0 3H8v-3zm0 3v2M13 13v5h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2h-1z" stroke-width="1.4"/></svg>PDF</a><a href="{{ route('bon-livraisons.pdf', [$bl->id, 'sans_prix' => 1]) }}" target="_blank" title="BL sans prix (quantités seulement)" class="inline-flex items-center gap-1 border border-gray-300 bg-white text-gray-800 px-2 py-1 rounded-md text-xs font-bold hover:border-zinc-900"><x-sp-icon name="printer" class="w-3.5 h-3.5" />Sans prix</a>@include('livewire.bon-livraisons._actions')</div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-sm text-gray-400">Aucun bon de livraison.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- phone: one card per document (2026-11-26) --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse ($bonLivraisons as $bl)
                <div class="p-4" wire:key="bl-m-{{ $bl->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <label class="min-w-0 flex items-center gap-3"><input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-zinc-900 focus:ring-zinc-900" :checked="sel.includes({{ $bl->id }})" @change="toggle({{ $bl->id }})"><span class="font-semibold text-gray-900">{{ $bl->numero }}</span></label>
                        <div class="text-end shrink-0"><div class="font-bold text-gray-900 tabular-nums">{{ \App\Support\Fmt::money($bl->total) }}</div><div class="mt-1"><span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-gray-100 text-gray-600' => $bl->status === 'draft',
                                'bg-green-100 text-green-700' => $bl->status === 'delivered',
                            ])>{{ \App\Support\Fmt::status($bl->status) }}</span></div></div>
                    </div>
                    <div class="mt-1 text-sm text-gray-600">{{ \App\Support\SalesLists::clientLabel($bl->client) }}@if ($bl->client?->code)<span class="block text-xs text-gray-400">{{ $bl->client->code }}</span>@endif<span class="text-xs text-gray-400">{{ $bl->date->format('d/m/Y') }}</span></div>
                    <div class="mt-3 flex items-center justify-end gap-2"><a href="{{ route('bon-livraisons.pdf', $bl->id) }}" target="_blank" title="Ouvrir le PDF" class="inline-flex items-center gap-1 bg-[#d92d20] text-white px-2 py-1 rounded-md text-xs font-bold hover:bg-[#b42318]"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h1.5a1.5 1.5 0 0 1 0 3H8v-3zm0 3v2M13 13v5h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2h-1z" stroke-width="1.4"/></svg>PDF</a><a href="{{ route('bon-livraisons.pdf', [$bl->id, 'sans_prix' => 1]) }}" target="_blank" title="BL sans prix (quantités seulement)" class="inline-flex items-center gap-1 border border-gray-300 bg-white text-gray-800 px-2 py-1 rounded-md text-xs font-bold hover:border-zinc-900"><x-sp-icon name="printer" class="w-3.5 h-3.5" />Sans prix</a>@include('livewire.bon-livraisons._actions')</div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-400">Aucun résultat.</div>
            @endforelse
        </div>

        <div class="p-4">{{ $bonLivraisons->links() }}</div>
    </div>
</div>
