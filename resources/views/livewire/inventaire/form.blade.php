<div class="p-4 sm:p-6 lg:p-8 max-w-6xl pb-32">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <a href="{{ route('inventaire.index') }}" wire:navigate class="text-sm text-slate-500 hover:text-slate-700">&larr; Inventaires</a>
            <h1 class="text-2xl font-bold text-slate-900">Nouvel inventaire</h1>
            <p class="text-sm text-slate-500">Saisissez la quantité réellement comptée. Les produits laissés vides ne changent pas.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-4 flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher référence, désignation, code-barres..." class="flex-1 min-w-[220px] rounded-lg border-slate-300 text-sm">
        <select wire:model.live="category" class="rounded-lg border-slate-300 text-sm">
            <option value="">Toutes les catégories</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <label class="inline-flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" wire:model.live="onlyCounted" class="rounded border-slate-300 text-indigo-600"> Seulement les comptés</label>
        <button type="button" wire:click="fillVisible" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" title="Met le stock actuel dans les cases vides des produits affichés">Reprendre le stock actuel</button>
    </div>

    @error('counted') <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm">{{ $message }}</div> @enderror

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Produit</th>
                        <th class="px-4 py-3 text-end text-xs font-semibold text-slate-500 uppercase">Stock système</th>
                        <th class="px-4 py-3 text-end text-xs font-semibold text-slate-500 uppercase w-36">Compté</th>
                        <th class="px-4 py-3 text-end text-xs font-semibold text-slate-500 uppercase">Écart</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $p)
                        @php
                            $v = $counted[$p->id] ?? '';
                            $has = $v !== '' && is_numeric($v);
                            $gap = $has ? (int) round((float) $v) - (int) $p->stock_qty : null;
                        @endphp
                        <tr wire:key="p-{{ $p->id }}" class="{{ $has ? ($gap ? 'bg-amber-50/60' : 'bg-emerald-50/40') : '' }}">
                            <td class="px-4 py-2.5">
                                <div class="text-sm font-medium text-slate-900">{{ $p->name }}</div>
                                <div class="text-xs text-slate-500">{{ $p->reference }}{{ $p->category ? ' · '.$p->category->name : '' }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-end text-sm text-slate-600 whitespace-nowrap">{{ (int) $p->stock_qty }} <span class="text-slate-400">{{ $p->unit }}</span></td>
                            <td class="px-4 py-2.5 text-end">
                                <input type="number" inputmode="numeric" step="1" wire:model.live.debounce.400ms="counted.{{ $p->id }}" class="w-28 rounded-lg border-slate-300 text-sm text-end" placeholder="—">
                            </td>
                            <td class="px-4 py-2.5 text-end text-sm font-semibold whitespace-nowrap {{ $gap === null ? 'text-slate-300' : ($gap === 0 ? 'text-emerald-600' : ($gap > 0 ? 'text-indigo-600' : 'text-rose-600')) }}">
                                {{ $gap === null ? '—' : ($gap === 0 ? 'OK' : ($gap > 0 ? '+'.$gap : $gap)) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-slate-400">Aucun produit trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes (optionnel)</label>
        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border-slate-300 text-sm" placeholder="Ex. inventaire de fin d'année, magasin principal"></textarea>
    </div>

    {{-- sticky summary bar --}}
    <div class="fixed bottom-0 inset-x-0 sm:start-64 z-20 border-t border-slate-200 bg-white/95 backdrop-blur px-4 sm:px-8 py-3 print:hidden">
        <div class="max-w-6xl flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-slate-600">
                <strong class="text-slate-900">{{ $nbCounted }}</strong> produit(s) compté(s) ·
                <strong class="{{ $nbGaps ? 'text-amber-600' : 'text-slate-900' }}">{{ $nbGaps }}</strong> écart(s)
                @if ($seePrice && $nbGaps)
                    · valeur de l'écart <strong class="{{ $gapValue < 0 ? 'text-rose-600' : 'text-indigo-600' }}">{{ \App\Support\Fmt::money($gapValue) }}</strong>
                @endif
            </div>
            <div class="flex gap-2">
                @if ($nbCounted)
                    <button type="button" wire:click="resetCounts" wire:confirm="Effacer toutes les quantités saisies ?" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Effacer</button>
                @endif
                <button type="button" wire:click="validateInventaire" wire:confirm="Valider l'inventaire ? Le stock des produits comptés sera remplacé par la quantité comptée." @disabled(! $nbCounted)
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-40">Valider l'inventaire</button>
            </div>
        </div>
    </div>
</div>
