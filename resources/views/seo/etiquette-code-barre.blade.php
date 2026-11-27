{{-- SEO tool page (FR), 2026-11-27 — cluster "étiquette code barre" (~5 000/mois cumulés, KD 10 : étiquette code barre, imprimer étiquette
     code barre, étiquette code barre A4, planche étiquette code barre, étiquette code barre prix, imprimante étiquette code barre).
     Tool = public/js/etiquette-code-barre.js (engine.js + etiquette-ui.js): liste de produits, type détecté par ligne, planches A4 + rouleau.
     Classes used only in JS (Tailwind safelist): text-zinc-400 text-red-600 text-zinc-600 grid grid-cols-[1fr_84px_52px] sm:grid-cols-[1.3fr_1fr_84px_60px_40px]
     gap-2 py-2 border-b border-zinc-100 items-start col-span-2 sm:col-span-1 order-1 order-2 order-3 order-4 order-5 sm:order-2 sm:order-3 sm:order-4 col-start-3
     sm:col-start-auto flex justify-end sm:justify-start mt-1 text-xs leading-snug w-full h-10 rounded-md border-zinc-300 text-sm focus:border-zinc-900
     focus:ring-zinc-900 font-mono px-2 w-10 place-items-center hover:text-red-600 hover:bg-zinc-100 flex-col items-center gap-3 py-4 relative bg-white shadow
     ring-1 ring-zinc-200 rounded-sm overflow-hidden py-10 --}}
@php
    $url = route('seo.etiquette-code-barre');
    $faqs = [
        ['Comment imprimer des étiquettes code barre gratuitement ?', 'Saisissez vos produits dans l’outil en haut de cette page (code, désignation, prix, quantité), choisissez une planche A4 ou la taille de votre rouleau, puis cliquez sur « Imprimer les étiquettes ». Dans la fenêtre d’impression, gardez l’échelle à 100 % et les marges à « Aucune ». C’est gratuit, sans inscription et sans filigrane.'],
        ['Peut-on imprimer des étiquettes code barre avec une imprimante normale ?', 'Oui. Une imprimante laser ou jet d’encre de bureau imprime très bien des codes barres sur des planches d’étiquettes adhésives A4. Prenez des planches prévues pour votre type d’imprimante (laser ou jet d’encre) et faites un premier essai sur une feuille blanche.'],
        ['Quel format d’étiquette choisir pour un code barre ?', 'Pour un produit vendu en magasin avec un EAN-13, visez au moins 38 × 21 mm, idéalement 50 × 25 mm ou plus. Pour une référence interne en Code 128, un format plus petit suffit si la référence est courte. La planche de 24 étiquettes (70 × 37 mm) est un bon choix par défaut : il reste de la place pour la désignation et le prix.'],
        ['Comment imprimer sur une imprimante d’étiquettes thermique ?', 'Choisissez « Rouleau » dans le format, avec la taille exacte de vos étiquettes (par exemple 50 × 30 mm). L’outil crée une page par étiquette, à cette taille. Dans la fenêtre d’impression, sélectionnez l’imprimante d’étiquettes, le format de papier correspondant et l’échelle 100 %. Si l’impression se décale, lancez le calibrage de l’imprimante.'],
        ['Quelle est la taille minimale d’un code barre EAN-13 ?', 'La taille de référence d’un EAN-13 est d’environ 37,3 × 25,9 mm, marges blanches et chiffres compris. GS1 autorise de 80 % à 200 % de cette taille, soit au minimum environ 29,8 × 20,7 mm. En dessous, ou si la hauteur des barres est réduite, les caisses risquent de ne pas le lire.'],
        ['Comment faire des étiquettes pour plusieurs produits ?', 'Ajoutez une ligne par produit avec sa quantité, ou copiez vos colonnes depuis Excel (code, désignation, prix, quantité) et collez-les dans « Coller depuis Excel ». L’outil reconnaît le type de chaque code : EAN-13, EAN-8, UPC-A ou Code 128.'],
        ['Mes produits sont-ils envoyés quelque part ?', 'Non. Les étiquettes sont dessinées dans votre navigateur. Votre liste est seulement gardée dans ce navigateur pour la retrouver à votre prochaine visite ; le bouton « Vider » l’efface.'],
    ];
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Générateur de code barre', 'item' => route('seo.code-barre')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Étiquette code barre', 'item' => $url],
            ],
        ],
        [
            '@type' => 'WebApplication',
            'name' => 'Créateur d’étiquettes code barre gratuit',
            'url' => $url,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Tous (navigateur web)',
            'inLanguage' => 'fr',
            'isAccessibleForFree' => true,
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
            'featureList' => 'Étiquettes code barre avec désignation et prix, planches A4 (14 à 65 étiquettes), rouleaux d’imprimante d’étiquettes thermique, EAN-13, EAN-8, UPC-A, Code 128, import depuis Excel',
            'publisher' => ['@type' => 'Organization', 'name' => 'Stokito', 'url' => url('/')],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs),
        ],
    ]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $a = 'font-semibold text-zinc-900 underline decoration-amber-400 decoration-2 underline-offset-4';
    $h2 = 'mt-16 mb-5 pt-4 border-t border-zinc-200 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24';
    $field = 'w-full h-11 rounded-lg border-zinc-300 text-[15px] focus:border-zinc-900 focus:ring-zinc-900';
    $msg = 'text-sm data-[level=error]:text-red-600 data-[level=ok]:text-zinc-600';
@endphp
@component('layouts.public', [
    'title' => 'Étiquette code barre : imprimer gratuitement (A4 ou rouleau) | Stokito',
    'description' => 'Créez et imprimez vos étiquettes code barre gratuitement : désignation, prix, EAN-13 ou Code 128, sur planche A4 (14 à 65 étiquettes) ou sur rouleau d’imprimante d’étiquettes. Import depuis Excel.',
    'canonical' => $url,
    'jsonLd' => $jsonLd,
])
<style>
    #et-app[data-roll="1"] .et-a4, #et-app[data-roll="0"] .et-roll, #et-app[data-custom="0"] .et-custom { display: none !important; }
</style>
<div>
    {{-- HERO + OUTIL --}}
    <section class="bg-zinc-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14">
            <nav aria-label="Fil d’Ariane" class="text-xs text-zinc-400">
                <a href="{{ url('/') }}" class="hover:text-white">Accueil</a> <span aria-hidden="true">/</span>
                <a href="{{ route('seo.code-barre') }}" class="hover:text-white">Générateur de code barre</a> <span aria-hidden="true">/</span>
                <span class="text-zinc-300">Étiquette code barre</span>
            </nav>
            <div class="mt-8 max-w-3xl">
                <p class="text-sm font-semibold text-amber-300">Gratuit · sans inscription · planche A4 ou rouleau</p>
                <h1 class="mt-3 text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.05]">Étiquette code barre : <span class="text-amber-300">imprimez-les gratuitement</span></h1>
                <p class="mt-5 text-lg text-zinc-300 leading-relaxed">Saisissez vos produits ou collez-les depuis Excel : code barre, désignation, prix, quantité. Imprimez sur des planches A4 avec votre imprimante de bureau, ou sur le rouleau d’une imprimante d’étiquettes thermique.</p>
            </div>

            <div id="et-app" data-roll="0" data-custom="0" class="mt-9 grid lg:grid-cols-[1.35fr_1fr] rounded-2xl bg-white text-zinc-900 shadow-2xl shadow-black/40 overflow-hidden [&>*]:min-w-0">
                <div class="p-5 sm:p-7">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-semibold">1. Vos produits <span id="et-count" class="font-normal text-zinc-500"></span></p>
                        <label class="flex items-center gap-2 text-sm text-zinc-600">Type
                            <select id="et-type" class="h-9 rounded-md border-zinc-300 text-sm focus:border-zinc-900 focus:ring-zinc-900">
                                <option value="auto">Automatique</option>
                                <option value="code128">Code 128 pour tout</option>
                            </select>
                        </label>
                    </div>
                    <div class="mt-2 hidden sm:grid grid-cols-[1.3fr_1fr_84px_60px_40px] gap-2 text-xs font-semibold text-zinc-500" aria-hidden="true"><span>Code barre</span><span>Désignation</span><span>Prix</span><span>Qté</span><span></span></div>
                    <div id="et-rows" class="mt-1 sm:mt-0"></div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button id="et-add" type="button" class="h-10 px-4 rounded-lg border border-zinc-300 text-sm font-semibold hover:border-zinc-900">+ Ajouter un produit</button>
                        <button id="et-clear" type="button" class="h-10 px-3 rounded-lg text-sm font-semibold text-zinc-500 hover:text-red-600">Vider</button>
                    </div>
                    <details class="mt-4 rounded-xl border border-zinc-200 bg-zinc-50 group">
                        <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-zinc-900 flex items-center justify-between">Coller depuis Excel <span class="text-zinc-400 group-open:rotate-45 transition text-lg leading-none">+</span></summary>
                        <div class="px-4 pb-4">
                            <p class="text-sm text-zinc-500">Copiez vos colonnes dans Excel (code, désignation, prix, quantité) et collez-les ici. Une ligne d’en-tête est ignorée.</p>
                            <textarea id="et-paste" rows="4" spellcheck="false" class="mt-2 w-full rounded-lg border-zinc-300 font-mono text-sm focus:border-zinc-900 focus:ring-zinc-900" placeholder="3760123456784	Gel douche 250 ml	3,20 €	10&#10;VIS-INOX-430	Vis inox 4 × 30	4,90 €	24"></textarea>
                            <button id="et-import" type="button" class="mt-2 h-10 px-4 rounded-lg bg-zinc-900 text-white text-sm font-semibold hover:bg-zinc-700">Ajouter à la liste</button>
                        </div>
                    </details>

                    <p class="mt-6 text-sm font-semibold">2. Format</p>
                    <div class="mt-2 grid sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label for="et-format" class="sr-only">Format des étiquettes</label>
                            <select id="et-format" class="{{ $field }}">
                                <optgroup label="Planche A4 (imprimante de bureau)">
                                    <option value="a4:3x8">A4 · 24 étiquettes · 70 × 37 mm</option>
                                    <option value="a4:3x7">A4 · 21 étiquettes · 70 × 42,3 mm</option>
                                    <option value="a4:4x10">A4 · 40 étiquettes · 52,5 × 29,7 mm</option>
                                    <option value="a4:2x7">A4 · 14 étiquettes · 99,1 × 38,1 mm</option>
                                    <option value="a4:5x13">A4 · 65 étiquettes · 38,1 × 21,2 mm</option>
                                </optgroup>
                                <optgroup label="Rouleau (imprimante d’étiquettes)">
                                    <option value="roll:50x25">Rouleau · 50 × 25 mm</option>
                                    <option value="roll:40x30">Rouleau · 40 × 30 mm</option>
                                    <option value="roll:50x30">Rouleau · 50 × 30 mm</option>
                                    <option value="roll:57x32">Rouleau · 57 × 32 mm</option>
                                    <option value="roll:60x40">Rouleau · 60 × 40 mm</option>
                                    <option value="roll:100x50">Rouleau · 100 × 50 mm</option>
                                    <option value="roll:perso">Rouleau · autre taille…</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="et-custom">
                            <label for="et-w" class="block text-xs font-semibold text-zinc-500 mb-1">Largeur (mm)</label>
                            <input id="et-w" type="number" min="20" max="150" step="0.5" value="50" class="{{ $field }}">
                        </div>
                        <div class="et-custom">
                            <label for="et-h" class="block text-xs font-semibold text-zinc-500 mb-1">Hauteur (mm)</label>
                            <input id="et-h" type="number" min="10" max="150" step="0.5" value="30" class="{{ $field }}">
                        </div>
                        <div class="et-a4">
                            <label for="et-start" class="block text-xs font-semibold text-zinc-500 mb-1">Commencer à l’étiquette n°</label>
                            <input id="et-start" type="number" min="1" max="65" value="1" class="{{ $field }}">
                        </div>
                        <label class="flex items-center gap-3 h-11 px-3 self-end rounded-lg border border-zinc-200 cursor-pointer">
                            <span class="relative inline-flex items-center">
                                <input id="et-digits" type="checkbox" checked class="peer sr-only">
                                <span class="w-10 h-6 rounded-full bg-zinc-300 peer-checked:bg-[#ffcc00] transition"></span>
                                <span class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow peer-checked:translate-x-4 transition"></span>
                            </span>
                            <span class="text-sm">Chiffres sous le code</span>
                        </label>
                    </div>
                </div>

                <div class="bg-zinc-50 border-t lg:border-t-0 lg:border-s border-zinc-200 p-5 sm:p-7 flex flex-col">
                    <p class="text-sm font-semibold">3. Aperçu et impression</p>
                    <div class="mt-3 flex-1">
                        <div id="et-preview" class="relative w-full max-w-[400px] mx-auto rounded-md bg-white shadow-xl ring-1 ring-zinc-200 overflow-hidden" aria-label="Aperçu des étiquettes"></div>
                    </div>
                    <p id="et-msg" class="mt-3 min-h-[2.5rem] {{ $msg }}" aria-live="polite" data-level="ok"></p>
                    <button id="et-print" type="button" class="mt-2 inline-flex items-center justify-center gap-2 h-12 px-6 rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold hover:bg-[#ffd633]">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 8V4h10v4M7 17H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M7 14h10v6H7z"/></svg>
                        Imprimer les étiquettes
                    </button>
                    <p class="mt-3 text-xs text-zinc-500">Échelle 100 % (taille réelle), marges « Aucune ». Pour un PDF, choisissez « Enregistrer en PDF » comme imprimante.</p>
                </div>
            </div>
            <p class="mt-4 text-xs text-zinc-400">Codes conformes aux normes EAN/UPC (GS1) et Code 128. Vous n’avez qu’un seul code à créer ? Le <a href="{{ route('seo.code-barre') }}" class="underline hover:text-white">générateur de code barre</a> le télécharge en PNG ou SVG.</p>
        </div>
    </section>

    {{-- SOMMAIRE --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-12">
        <div class="bg-white border border-zinc-200 shadow-sm p-5 rounded-xl">
            <p class="text-xs font-semibold text-zinc-500">Sommaire</p>
            <ol class="mt-2 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-[15px] list-decimal ps-5">
                <li><a href="#etapes" class="{{ $a }}">Créer ses étiquettes en 3 étapes</a></li>
                <li><a href="#format" class="{{ $a }}">Quel format d’étiquette choisir ?</a></li>
                <li><a href="#imprimante" class="{{ $a }}">Planche A4 ou imprimante d’étiquettes</a></li>
                <li><a href="#contenu" class="{{ $a }}">Que mettre sur l’étiquette ?</a></li>
                <li><a href="#impression" class="{{ $a }}">Réussir l’impression</a></li>
                <li><a href="#stokito" class="{{ $a }}">Étiquettes depuis votre stock</a></li>
                <li><a href="#faq" class="{{ $a }}">Questions fréquentes</a></li>
            </ol>
        </div>
    </div>

    <article class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-[17px] leading-[1.85] text-zinc-700">

        <h2 id="etapes" class="mt-4 mb-5 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24">Créer ses étiquettes code barre en 3 étapes</h2>
        <ol class="mb-6 space-y-4">
            @foreach ([
                ['Saisissez vos produits', 'Une ligne par produit : le code barre, la désignation et le prix s’ils doivent apparaître, et le nombre d’étiquettes. Vous pouvez aussi coller une liste copiée depuis Excel.'],
                ['Choisissez le format', 'Une planche A4 pour une imprimante de bureau, ou la taille de votre rouleau pour une imprimante d’étiquettes. L’aperçu se met à jour à chaque modification.'],
                ['Imprimez', 'Cliquez sur « Imprimer les étiquettes » : chaque produit est imprimé autant de fois que sa quantité, à la taille exacte de l’étiquette.'],
            ] as $i => [$t, $d])
                <li class="flex gap-4">
                    <span class="grid place-items-center w-9 h-9 shrink-0 rounded-full bg-[#ffcc00] text-zinc-900 font-bold">{{ $i + 1 }}</span>
                    <span><strong class="text-zinc-900">{{ $t }}.</strong> {{ $d }}</span>
                </li>
            @endforeach
        </ol>
        <p class="mb-5">L’outil reconnaît chaque code tout seul : 13 chiffres avec une clé juste donnent un <strong class="text-zinc-900">EAN-13</strong>, 12 chiffres un UPC-A (ou un EAN-13 dont la clé est calculée pour vous), et une référence avec des lettres, comme « VIS-INOX-430 », un <strong class="text-zinc-900">Code 128</strong>. Si un EAN a une clé de contrôle fausse, la ligne est signalée en rouge au lieu d’imprimer un code illisible en caisse.</p>

        <h2 id="format" class="{{ $h2 }}">Quel format d’étiquette code barre choisir ?</h2>
        <div class="my-6 overflow-x-auto rounded-xl border border-zinc-200 bg-white">
            <table class="w-full text-[15px]">
                <thead class="bg-zinc-50 text-zinc-500 text-sm"><tr><th class="text-start font-semibold px-4 py-3">Format</th><th class="text-start font-semibold px-4 py-3">Taille</th><th class="text-start font-semibold px-4 py-3">Pour quoi faire</th></tr></thead>
                <tbody class="divide-y divide-zinc-100">
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">A4 · 24 étiquettes</td><td class="px-4 py-3 whitespace-nowrap">70 × 37 mm</td><td class="px-4 py-3">Le format passe-partout : code barre, désignation et prix tiennent sans forcer</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">A4 · 14 étiquettes</td><td class="px-4 py-3 whitespace-nowrap">99,1 × 38,1 mm</td><td class="px-4 py-3">Cartons, bacs, étagères : lisible de loin</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">A4 · 40 étiquettes</td><td class="px-4 py-3 whitespace-nowrap">52,5 × 29,7 mm</td><td class="px-4 py-3">Petits produits, avec un prix court</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">A4 · 65 étiquettes</td><td class="px-4 py-3 whitespace-nowrap">38,1 × 21,2 mm</td><td class="px-4 py-3">Bijoux, pièces détachées : code seul, de préférence un Code 128 court</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">Rouleau</td><td class="px-4 py-3 whitespace-nowrap">50 × 25, 50 × 30 mm…</td><td class="px-4 py-3">Imprimante d’étiquettes : on imprime le nombre exact, sans gâcher de planche</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mb-5">Pour un produit vendu en magasin, ne descendez pas trop bas : un <strong class="text-zinc-900">code barre EAN-13</strong> mesure environ 37,3 × 25,9 mm à sa taille de référence, et GS1 tolère jusqu’à 80 % de cette taille (environ 29,8 × 20,7 mm). Ne réduisez pas non plus la hauteur des barres. Sur les très petites étiquettes, préférez une référence interne courte en Code 128.</p>

        <h2 id="imprimante" class="{{ $h2 }}">Planche A4 ou imprimante d’étiquettes ?</h2>
        <div class="my-6 grid sm:grid-cols-2 gap-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5">
                <p class="font-bold text-zinc-900">Planches A4 adhésives</p>
                <p class="mt-2 text-[15px] leading-relaxed">Vous utilisez l’imprimante de bureau que vous avez déjà. Idéal pour démarrer ou pour quelques dizaines d’étiquettes de temps en temps. Prenez des planches prévues pour votre imprimante : laser ou jet d’encre, ce n’est pas le même papier. Une planche entamée se réutilise avec « Commencer à l’étiquette n° ».</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5">
                <p class="font-bold text-zinc-900">Imprimante d’étiquettes thermique</p>
                <p class="mt-2 text-[15px] leading-relaxed">Elle imprime une étiquette à la fois sur un rouleau, sans encre. Rentable dès que vous étiquetez chaque jour : réception de marchandise, réassort, colis. Une résolution de 203 dpi suffit pour la plupart des codes ; prenez 300 dpi pour de très petites étiquettes.</p>
            </div>
        </div>
        <p class="mb-5">Il existe deux techniques. En <strong class="text-zinc-900">thermique directe</strong>, le papier noircit à la chaleur : pas de ruban, mais l’étiquette pâlit au soleil et avec le temps (parfait pour les colis et les prix qui changent). En <strong class="text-zinc-900">transfert thermique</strong>, un ruban encré imprime sur l’étiquette : c’est plus durable, pour les produits stockés longtemps ou manipulés.</p>

        <h2 id="contenu" class="{{ $h2 }}">Que mettre sur une étiquette code barre ?</h2>
        <ul class="mb-6 list-disc ps-6 space-y-2">
            <li><strong class="text-zinc-900">Le code barre</strong>, avec ses chiffres en dessous : si la lecture échoue, le caissier peut taper le numéro.</li>
            <li><strong class="text-zinc-900">La désignation</strong>, courte : « Vis inox 4 × 30 » plutôt que la fiche complète. Elle évite les erreurs au rangement.</li>
            <li><strong class="text-zinc-900">Le prix</strong>, si l’étiquette sert aussi d’étiquette de prix. Pensez à la réimprimer quand le prix change.</li>
        </ul>
        <p class="mb-5">Quel code utiliser ? Pour vendre en grande surface ou sur une place de marché, le numéro EAN doit venir de GS1 (<a href="{{ route('seo.code-barre') }}#gs1" class="{{ $a }}">voir pourquoi</a>). Pour un usage interne (boutique, réserve, entrepôt), un EAN-13 qui commence par 2 ou un Code 128 avec vos propres références suffit. Pour savoir d’où vient un code existant, consultez la liste <a href="{{ route('seo.code-barre-pays') }}" class="{{ $a }}">code barre pays</a>.</p>

        <h2 id="impression" class="{{ $h2 }}">Réussir l’impression de ses étiquettes</h2>
        <ul class="mb-6 space-y-3">
            @foreach ([
                ['Échelle 100 %', 'Dans la fenêtre d’impression, choisissez « Taille réelle » ou 100 %, jamais « Ajuster à la page » : sinon tout se décale d’une étiquette à l’autre.'],
                ['Marges « Aucune »', 'Les marges sont déjà calculées pour la planche. Si votre imprimante refuse les marges nulles, choisissez les plus petites possibles.'],
                ['Un essai sur papier blanc', 'Imprimez d’abord une feuille blanche et posez-la sur la planche, à contre-jour : vous voyez tout de suite si c’est aligné.'],
                ['Imprimante d’étiquettes', 'Réglez dans le pilote la taille du papier sur celle de l’étiquette (par exemple 50 × 30 mm) et lancez le calibrage si l’impression glisse d’une étiquette à l’autre.'],
                ['Vérifiez un code', 'Scannez une étiquette avant d’imprimer toute la série, avec une douchette ou le <a href="'.route('seo.scanner-code-barre').'" class="'.$a.'">scanner de code barre en ligne</a> et la caméra de votre téléphone.'],
            ] as [$t, $d])
                <li class="flex gap-3"><span class="mt-2 w-2 h-2 shrink-0 rounded-full bg-[#ffcc00]"></span><span><strong class="text-zinc-900">{{ $t }}.</strong> {!! $d !!}</span></li>
            @endforeach
        </ul>

        <h2 id="stokito" class="{{ $h2 }}">Imprimer les étiquettes directement depuis votre stock</h2>
        <p class="mb-5">Retaper ses produits à chaque réassort, c’est du temps perdu. Dans <a href="{{ url('/') }}" class="{{ $a }}">Stokito</a>, la page Catalogue › Étiquettes code barre reprend votre catalogue : vous cochez les produits, la quantité peut suivre le stock en un clic, et le prix de vente est repris de la fiche produit. Ensuite, chaque étiquette se scanne :</p>
        <ul class="mb-6 list-disc ps-6 space-y-2">
            <li>à la <a href="{{ route('seo.caisse') }}" class="{{ $a }}">caisse</a>, le scan ajoute l’article au ticket et le stock baisse ;</li>
            <li>dans un devis, une facture ou un <a href="{{ route('seo.bon-de-commande') }}" class="{{ $a }}">bon de commande</a>, le scan ajoute la ligne.</li>
        </ul>
        <div class="my-8 rounded-2xl bg-zinc-900 text-white p-6 sm:p-8">
            <p class="text-xl font-bold">Vos étiquettes, votre stock et votre caisse au même endroit</p>
            <p class="mt-2 text-zinc-300">Stokito est un <a href="{{ route('seo.logiciel-gratuit') }}" class="underline decoration-amber-400 decoration-2 underline-offset-4">logiciel de gestion de stock gratuit</a>, sans carte bancaire : produits, codes barres, étiquettes, devis, factures et caisse.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="inline-flex items-center h-11 px-5 rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold">Créer un compte gratuit</a>
                <a href="{{ route('demo.login') }}" class="inline-flex items-center h-11 px-5 rounded-lg border border-white/25 font-semibold hover:bg-white/10">Tester la démo</a>
            </div>
        </div>

        <h2 id="faq" class="{{ $h2 }}">Questions fréquentes sur les étiquettes code barre</h2>
        <div class="divide-y divide-zinc-200 rounded-xl border border-zinc-200 bg-white" x-data="{ open: 0 }">
            @foreach ($faqs as $i => [$q, $ans])
                <div>
                    <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center gap-4 px-5 py-4 text-start font-semibold text-zinc-900">
                        <span class="flex-1">{{ $q }}</span><span class="text-xl leading-none text-zinc-400" x-text="open === {{ $i }} ? '−' : '+'">+</span>
                    </button>
                    <div x-show="open === {{ $i }}" @if ($i !== 0) x-cloak @endif class="px-5 pb-5 text-[16px] text-zinc-600">{{ $ans }}</div>
                </div>
            @endforeach
        </div>
    </article>
</div>
<script src="/js/etiquette-code-barre.js?v=20261127c" defer></script>
@endcomponent
