{{-- Produits › Étiquettes code barre (2026-11-26): pick products and quantities, print an A4 label sheet.
     Rendering is done in the browser by /js/etiquettes.js (same engine as the public /generateur-code-barre page). --}}
{{-- Tailwind safelist, classes used by /js/etiquettes.js: ' '' (p.code + : ? bg-white bg-zinc-100 border border-0 border-zinc-300 flex flex-1 flex-wrap focus:ring-0 font-mono font-semibold gap-3 gap-x-1.5 gap-y-0.5 h-9 hover:text-zinc-900 inline-flex items-center min-w-0 mt-0.5 opacity-60') p-0 px-1.5 px-4 py-0.5 py-10 py-3' rounded rounded-lg text-[11px] text-center text-lg text-red-600 text-sm text-xs text-zinc-400 text-zinc-500 text-zinc-600 text-zinc-700 text-zinc-900 truncate w-14 w-9 --}}
<x-app-layout>
    @php
        $field = 'w-full h-10 rounded-lg border-zinc-300 text-sm focus:border-zinc-900 focus:ring-zinc-900';
        $btn = 'inline-flex items-center justify-center gap-1.5 h-9 px-3 rounded-lg border border-zinc-300 bg-white text-sm font-semibold text-zinc-700 hover:border-zinc-900 hover:text-zinc-900';
    @endphp
    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
            <div>
                <a href="{{ route('products.index') }}" wire:navigate class="text-sm text-zinc-500 hover:text-zinc-900">← Produits</a>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-900">Étiquettes code barre</h1>
                <p class="mt-1 text-sm text-zinc-500">Choisissez combien d’étiquettes imprimer pour chaque produit, puis imprimez la planche A4.</p>
            </div>
            <button id="lbl-print" type="button" class="inline-flex items-center gap-2 h-11 px-5 rounded-lg bg-zinc-900 text-white text-sm font-semibold hover:bg-zinc-700">
                <x-sp-icon name="printer" class="w-5 h-5" /> Imprimer <span id="lbl-count" class="rounded-full bg-[#ffcc00] px-2 py-0.5 text-xs font-bold text-zinc-900">0</span>
            </button>
        </div>

        <div id="lbl-app" class="grid lg:grid-cols-[1fr_380px] gap-6 items-start [&>*]:min-w-0">
            {{-- products --}}
            <div class="rounded-xl border border-zinc-200 bg-white shadow-sm">
                <div class="p-4 border-b border-zinc-200 grid sm:grid-cols-[1fr_200px] gap-2">
                    <input id="lbl-search" type="search" placeholder="Rechercher un produit, une référence, un code barre…" class="{{ $field }}">
                    <select id="lbl-cat" class="{{ $field }}">
                        <option value="">Toutes les catégories</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                    <div class="sm:col-span-2 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-medium text-zinc-500 me-1">Produits affichés :</span>
                        <button id="lbl-one" type="button" class="{{ $btn }}">1 étiquette chacun</button>
                        <button id="lbl-stock" type="button" class="{{ $btn }}">Quantité = stock</button>
                        <button id="lbl-zero" type="button" class="{{ $btn }}">Tout à 0</button>
                    </div>
                </div>
                <div id="lbl-rows" class="divide-y divide-zinc-100 max-h-[65vh] overflow-y-auto"></div>
            </div>

            {{-- sheet settings + preview --}}
            <div class="space-y-4 lg:sticky lg:top-20">
                <div class="rounded-xl border border-zinc-200 bg-white shadow-sm p-4 space-y-3">
                    <div>
                        <label for="lbl-format" class="block text-xs font-medium text-zinc-500 mb-1">Planche A4</label>
                        <select id="lbl-format" class="{{ $field }}">
                            <option value="3x8">24 étiquettes · 70 × 37 mm</option>
                            <option value="3x7">21 étiquettes · 70 × 42,3 mm</option>
                            <option value="4x10">40 étiquettes · 52,5 × 29,7 mm</option>
                            <option value="2x7">14 étiquettes · 99,1 × 38,1 mm</option>
                            <option value="5x13">65 étiquettes · 38,1 × 21,2 mm</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="lbl-price" class="block text-xs font-medium text-zinc-500 mb-1">Prix</label>
                            <select id="lbl-price" class="{{ $field }}">
                                <option value="ttc">Prix TTC</option>
                                <option value="ht">Prix HT</option>
                                <option value="none">Sans prix</option>
                            </select>
                        </div>
                        <div>
                            <label for="lbl-start" class="block text-xs font-medium text-zinc-500 mb-1">Commencer à l’étiquette</label>
                            <input id="lbl-start" type="number" min="1" max="65" value="1" class="{{ $field }}">
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-x-5 gap-y-2 pt-1">
                        @foreach (['lbl-name' => 'Nom du produit', 'lbl-digits' => 'Chiffres sous les barres'] as $id => $label)
                            <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-zinc-700">
                                <span class="relative inline-flex items-center">
                                    <input id="{{ $id }}" type="checkbox" checked class="peer sr-only">
                                    <span class="w-9 h-5 rounded-full bg-zinc-300 peer-checked:bg-[#ffcc00] transition"></span>
                                    <span class="absolute left-0.5 top-0.5 w-4 h-4 rounded-full bg-white shadow peer-checked:translate-x-4 transition"></span>
                                </span>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                    <div id="lbl-preview" class="relative w-full max-w-[340px] mx-auto rounded bg-white shadow-lg ring-1 ring-zinc-200 overflow-hidden" aria-label="Aperçu de la première page"></div>
                    <p id="lbl-msg" class="mt-3 text-center text-sm text-zinc-600" aria-live="polite"></p>
                    <p class="mt-2 text-center text-xs text-zinc-400">Imprimez à l’échelle 100 %, marges « Aucune ». « Enregistrer en PDF » pour un PDF.</p>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="lbl-data">@json($payload)</script>
    <script src="/js/etiquettes.js?v=20261126"></script>
</x-app-layout>
