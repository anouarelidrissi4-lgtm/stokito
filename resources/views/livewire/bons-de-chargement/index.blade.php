{{-- Bons de chargement (2026-11-27): one per delivery note, created automatically; select and print --}}
@php
    $badge = ['preparation' => 'bg-gray-100 text-gray-600', 'partiel' => 'bg-[#ffcc00]/30 text-zinc-900', 'charge' => 'bg-green-100 text-green-700', 'livre' => 'bg-zinc-900 text-white'];
    $labels = \App\Models\BonDeChargement::STATUTS;
    $cb = 'h-4 w-4 rounded border-gray-300 text-zinc-900 focus:ring-zinc-900';
    $ids = $chargements->pluck('id')->values();
    $pdfBtn = 'inline-flex items-center gap-1 bg-[#d92d20] text-white px-2 py-1 rounded-md text-xs font-bold hover:bg-[#b42318]';
@endphp
<div class="py-6 px-4 sm:px-6 lg:px-8"
     x-data="{ sel: [], url: @js(route('bons-de-chargement.pdf-selection')),
        toggle(id) { this.sel = this.sel.includes(id) ? this.sel.filter(x => x !== id) : [...this.sel, id] },
        all(ids, on) { this.sel = on ? [...new Set([...this.sel, ...ids])] : this.sel.filter(x => !ids.includes(x)) } }">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Bons de chargement</h1>
        <p class="mt-1 text-sm text-gray-500">Un bon de chargement est créé automatiquement avec chaque bon de livraison. Ouvrez-le pour saisir les quantités chargées.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="N° de chargement, N° de BL, client, code client…" class="w-full md:max-w-md rounded-md border-gray-300 text-sm">
        </div>
        <div x-show="sel.length" x-cloak class="flex flex-wrap items-center gap-3 px-4 py-3 bg-[#ffcc00]/20 border-b border-amber-300">
            <span class="text-sm font-semibold text-zinc-900" x-text="sel.length + (sel.length > 1 ? ' bons de chargement sélectionnés' : ' bon de chargement sélectionné')"></span>
            <a :href="url + '?ids=' + sel.join(',')" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 h-9 px-4 rounded-lg bg-zinc-900 text-white text-sm font-semibold hover:bg-zinc-700">
                <x-sp-icon name="printer" class="w-4 h-4" /> Imprimer la sélection
            </a>
            <button type="button" @click="sel = []" class="text-sm text-zinc-600 underline">Tout désélectionner</button>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="ps-6 pe-2 py-3 w-8"><input type="checkbox" class="{{ $cb }}" aria-label="Tout sélectionner"
                            :checked="@js($ids).length && @js($ids).every(i => sel.includes(i))" @change="all(@js($ids), $event.target.checked)"></th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">N°</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">BL</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Chauffeur · immat.</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commercial</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Chargement</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($chargements as $bc)
                        @php
                            $bl = $bc->bonLivraison ?? $bc->bonLivraisons->first();
                            $tot = $bc->lignes->count(); $done = $bc->lignes->filter(fn ($l) => $l->statut() === 'charge')->count();
                        @endphp
                        <tr wire:key="bc-{{ $bc->id }}" :class="sel.includes({{ $bc->id }}) ? 'bg-[#ffcc00]/10' : ''">
                            <td class="ps-6 pe-2 py-4"><input type="checkbox" class="{{ $cb }}" aria-label="Sélectionner {{ $bc->numero }}" :checked="sel.includes({{ $bc->id }})" @change="toggle({{ $bc->id }})"></td>
                            <td class="px-3 py-4 text-sm font-semibold text-gray-900 whitespace-nowrap"><a href="{{ route('bons-de-chargement.show', $bc->id) }}" wire:navigate class="hover:underline">{{ $bc->numero }}</a></td>
                            <td class="px-3 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $bl?->numero ?? '—' }}@if ($bc->bonLivraisons->count() > 1)<span class="text-xs text-gray-400"> +{{ $bc->bonLivraisons->count() - 1 }}</span>@endif</td>
                            <td class="px-3 py-4 text-sm text-gray-700">{{ $bl?->client ? \App\Support\SalesLists::clientLabel($bl->client) : '—' }}</td>
                            <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $bc->date?->format('d/m/Y') }}</td>
                            <td class="px-3 py-4 text-sm text-gray-700">{{ $bc->chauffeur ?: '—' }}@if ($bc->vehicule)<span class="block text-xs font-mono text-gray-500 whitespace-nowrap">{{ $bc->vehicule }}</span>@endif</td>
                            <td class="px-3 py-4 text-sm text-gray-700">{{ $bc->commercial ?: '—' }}</td>
                            <td class="px-3 py-4 text-sm text-gray-600 whitespace-nowrap">
                                {{ $done }} / {{ $tot }} ligne{{ $tot > 1 ? 's' : '' }}
                                <div class="mt-1 h-1.5 w-24 rounded-full bg-gray-200"><div class="h-1.5 rounded-full bg-[#ffcc00]" style="width: {{ $tot ? round($done / $tot * 100) : 0 }}%"></div></div>
                            </td>
                            <td class="px-3 py-4 text-sm"><span class="whitespace-nowrap px-2 py-1 rounded-full text-xs font-semibold {{ $badge[$bc->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $labels[$bc->status] ?? $bc->status }}</span></td>
                            <td class="px-3 py-4 text-right text-sm whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('bons-de-chargement.pdf', $bc->id) }}" target="_blank" class="{{ $pdfBtn }}"><x-sp-icon name="printer" class="w-3.5 h-3.5" />PDF</a>
                                    <a href="{{ route('bons-de-chargement.show', $bc->id) }}" wire:navigate class="inline-flex items-center gap-1 border border-gray-300 bg-white px-2 py-1 rounded-md text-xs font-bold text-gray-800 hover:border-zinc-900">Charger</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-6 py-8 text-center text-sm text-gray-400">Aucun bon de chargement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- phone --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse ($chargements as $bc)
                @php $bl = $bc->bonLivraison ?? $bc->bonLivraisons->first(); $tot = $bc->lignes->count(); $done = $bc->lignes->filter(fn ($l) => $l->statut() === 'charge')->count(); @endphp
                <div class="p-4" wire:key="bc-m-{{ $bc->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <label class="flex items-center gap-3"><input type="checkbox" class="{{ $cb }}" :checked="sel.includes({{ $bc->id }})" @change="toggle({{ $bc->id }})"><span class="font-semibold text-gray-900">{{ $bc->numero }}</span></label>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badge[$bc->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $labels[$bc->status] ?? $bc->status }}</span>
                    </div>
                    <div class="mt-1 text-sm text-gray-600">{{ $bl?->numero }} · {{ $bl?->client ? \App\Support\SalesLists::clientLabel($bl->client) : '' }} <span class="text-xs text-gray-400">{{ $bc->date?->format('d/m/Y') }} · {{ $done }}/{{ $tot }} chargée(s)</span>
                        @if ($bc->chauffeur || $bc->vehicule || $bc->commercial)<span class="block text-xs text-gray-500">{{ implode(' · ', array_filter([$bc->chauffeur, $bc->vehicule, $bc->commercial ? 'Commercial : '.$bc->commercial : null])) }}</span>@endif</div>
                    <div class="mt-3 flex justify-end gap-2">
                        <a href="{{ route('bons-de-chargement.pdf', $bc->id) }}" target="_blank" class="{{ $pdfBtn }}"><x-sp-icon name="printer" class="w-3.5 h-3.5" />PDF</a>
                        <a href="{{ route('bons-de-chargement.show', $bc->id) }}" wire:navigate class="inline-flex items-center border border-gray-300 bg-white px-2 py-1 rounded-md text-xs font-bold text-gray-800">Charger</a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-400">Aucun bon de chargement.</div>
            @endforelse
        </div>

        <div class="p-4">{{ $chargements->links() }}</div>
    </div>
</div>
