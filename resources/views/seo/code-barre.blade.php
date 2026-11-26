{{-- SEO tool page (FR), 2026-11-25 — cluster "générateur code barre" (5 400 + 4 400 + 2 900 ×3 + 1 900 ×3, KD 23-32),
     "code barre" (18 100), "étiquette code barre" (1 600 + 1 300 ×3, KD 10), "code barre pays / 3 quel pays / pays code barre 8…"
     (~590-720 each, KD 10-15), "vérification code barre en ligne" (720 + 590), "code barre png" (1 000). Semrush FR, 2026-11-25.
     The tool runs in the browser: public/js/code-barre.js (encoders checked against JsBarcode, 1 498 codes, 0 mismatch). --}}
@php
    $url = route('seo.code-barre');
    $faqs = [
        ['Comment créer un code barre gratuitement ?', 'Choisissez le type (EAN-13 pour un produit, Code 128 pour une référence interne), tapez le numéro dans le générateur en haut de cette page, puis téléchargez le code barre en PNG ou en SVG, ou imprimez une planche d’étiquettes A4. C’est gratuit, sans inscription, et le numéro n’est envoyé nulle part : tout se fait dans votre navigateur.'],
        ['Peut-on inventer son propre code barre EAN-13 ?', 'Pour un usage interne (stock, entrepôt, boutique qui étiquette ses propres articles), oui : prenez un EAN-13 qui commence par 2, une plage réservée à cet usage, ou un Code 128. Pour vendre en grande surface ou sur une place de marché, non : le numéro doit venir de GS1, sinon il risque d’être celui d’un autre produit.'],
        ['Code barre qui commence par 3 : quel pays ?', 'Les préfixes 300 à 379 sont attribués par GS1 France : l’entreprise qui a créé le code est inscrite en France (ou à Monaco). Cela n’indique pas forcément que le produit est fabriqué en France.'],
        ['Comment vérifier si un code barre est valide ?', 'Le dernier chiffre d’un EAN ou d’un UPC est une clé de contrôle calculée à partir des autres. Collez le numéro dans l’outil de vérification de cette page : il dit si la clé est juste, de quel type de code il s’agit et quel préfixe pays il porte.'],
        ['Quelle taille pour un code barre EAN-13 ?', 'La taille de référence d’un EAN-13 est d’environ 37,3 × 25,9 mm, zones blanches et chiffres compris. GS1 autorise de 80 % à 200 % de cette taille. En dessous, les lecteurs ont du mal à le lire ; évitez aussi de réduire la hauteur des barres.'],
        ['Comment imprimer des étiquettes code barre ?', 'Dans la partie « Étiquettes » de cette page, choisissez votre format de planche A4 (24, 21, 40, 14 ou 65 étiquettes), ajoutez la désignation et le prix, puis cliquez sur Imprimer. Dans la fenêtre d’impression, gardez l’échelle à 100 % et les marges à « Aucune ». Pour obtenir un PDF, choisissez « Enregistrer en PDF ».'],
        ['PNG ou SVG : quel format choisir ?', 'Le PNG s’insère partout (Word, Excel, Canva, site web). Le SVG est vectoriel : il reste net à toutes les tailles, c’est le format à envoyer à un imprimeur ou à utiliser sur un emballage.'],
    ];
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Générateur de code barre', 'item' => $url],
            ],
        ],
        [
            '@type' => 'WebApplication',
            'name' => 'Générateur de code barre gratuit',
            'url' => $url,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Tous (navigateur web)',
            'inLanguage' => 'fr',
            'isAccessibleForFree' => true,
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
            'featureList' => 'EAN-13, EAN-8, UPC-A, Code 128, téléchargement PNG et SVG, planche d’étiquettes A4, vérification de la clé de contrôle, préfixe pays GS1',
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
    $msg = 'text-sm data-[level=error]:text-red-600 data-[level=info]:text-zinc-900 data-[level=ok]:text-zinc-500';
    $digits = [
        ['0 ou 1', 'États-Unis et Canada (codes UPC)'],
        ['2', 'Usage interne : magasin, entrepôt, produits vendus au poids'],
        ['3', 'France et Monaco (300 à 379), Bulgarie (380), Slovénie (383), Croatie (385)'],
        ['4', 'Allemagne (400 à 440), Japon (450 à 459 et 490 à 499), Russie (460 à 469), Hong Kong (489)'],
        ['5', 'Royaume-Uni (500 à 509), Grèce (520-521), Belgique et Luxembourg (540 à 549), Portugal (560), Danemark (570 à 579), Pologne (590)'],
        ['6', 'Afrique du Sud (600-601), Maroc (611), Algérie (613), Tunisie (619), Égypte (622), Arabie saoudite (628), Finlande (640 à 649), Chine (690 à 699)'],
        ['7', 'Norvège (700 à 709), Israël (729), Suède (730 à 739), Mexique (750), Canada (754-755), Suisse (760 à 769), Brésil (789-790)'],
        ['8', 'Italie (800 à 839), Espagne (840 à 849), Turquie (868-869), Pays-Bas (870 à 879), Corée du Sud (880), Inde (890)'],
        ['9', 'Autriche (900 à 919), Australie (930 à 939), Nouvelle-Zélande (940 à 949), presse ISSN (977), livres ISBN (978-979)'],
    ];
@endphp
@component('layouts.public', [
    'title' => 'Générateur de code barre gratuit (EAN-13, Code 128) | Stokito',
    'description' => 'Générateur de code barre gratuit : créez un EAN-13, EAN-8, UPC-A ou Code 128, téléchargez-le en PNG ou SVG, imprimez vos étiquettes code barre sur A4 et vérifiez un code en ligne.',
    'canonical' => $url,
    'jsonLd' => $jsonLd,
])
<div>
    {{-- HERO + OUTIL --}}
    <section class="bg-zinc-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14">
            <nav aria-label="Fil d’Ariane" class="text-xs text-zinc-400">
                <a href="{{ url('/') }}" class="hover:text-white">Accueil</a> <span aria-hidden="true">/</span> <span class="text-zinc-300">Générateur de code barre</span>
            </nav>
            <div class="mt-8 max-w-3xl">
                <p class="text-sm font-semibold text-amber-300">Gratuit · sans inscription · PNG, SVG et étiquettes A4</p>
                <h1 class="mt-3 text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.05]">Générateur de code barre <span class="text-amber-300">gratuit</span></h1>
                <p class="mt-5 text-lg text-zinc-300 leading-relaxed">Créez un code barre EAN-13, EAN-8, UPC-A ou Code 128 en quelques secondes, téléchargez-le en PNG ou en SVG et imprimez vos étiquettes. Tout se passe dans votre navigateur : vos numéros ne sont envoyés à personne.</p>
            </div>

            <div id="cb-app" class="mt-9 grid lg:grid-cols-[1fr_1.1fr] rounded-2xl bg-white text-zinc-900 shadow-2xl shadow-black/40 overflow-hidden">
                <div class="p-5 sm:p-7">
                    <p class="text-sm font-semibold">1. Type de code barre</p>
                    <div class="mt-2 grid grid-cols-4 gap-1 p-1 rounded-xl bg-zinc-100" role="radiogroup" aria-label="Type de code barre">
                        @foreach (['ean13' => 'EAN-13', 'ean8' => 'EAN-8', 'upca' => 'UPC-A', 'code128' => 'Code 128'] as $t => $label)
                            <button type="button" role="radio" data-cb-type="{{ $t }}" aria-checked="{{ $t === 'ean13' ? 'true' : 'false' }}" class="h-10 rounded-lg text-[13px] sm:text-sm font-semibold text-zinc-600 hover:text-zinc-900 aria-checked:bg-zinc-900 aria-checked:text-white aria-checked:shadow transition">{{ $label }}</button>
                        @endforeach
                    </div>
                    <p id="cb-help" class="mt-2 text-xs text-zinc-500 min-h-[2rem]">13 chiffres : le code des produits vendus en magasin. Tapez 12 chiffres, la clé de contrôle est ajoutée.</p>

                    <label for="cb-value" class="mt-4 block text-sm font-semibold">2. Numéro ou texte</label>
                    <div class="mt-2 flex gap-2">
                        <input id="cb-value" type="text" inputmode="numeric" autocomplete="off" spellcheck="false" value="2001234567893" class="flex-1 min-w-0 h-12 rounded-lg border-zinc-300 text-lg font-mono focus:border-zinc-900 focus:ring-zinc-900 aria-[invalid=true]:border-red-400">
                        <button id="cb-random" type="button" title="Génère un EAN-13 interne (préfixe 2) au hasard" class="shrink-0 h-12 px-3 rounded-lg border border-zinc-300 text-sm font-semibold hover:border-zinc-900">Code interne</button>
                    </div>
                    <p id="cb-msg" class="mt-2 {{ $msg }}" aria-live="polite" data-level="ok">Clé de contrôle valide.</p>

                    <p class="mt-5 text-sm font-semibold">3. Réglages</p>
                    <div class="mt-2 grid sm:grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 h-11 px-3 rounded-lg border border-zinc-200 cursor-pointer">
                            <span class="relative inline-flex items-center">
                                <input id="cb-text" type="checkbox" checked class="peer sr-only">
                                <span class="w-10 h-6 rounded-full bg-zinc-300 peer-checked:bg-[#ffcc00] transition"></span>
                                <span class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow peer-checked:translate-x-4 transition"></span>
                            </span>
                            <span class="text-sm">Afficher les chiffres</span>
                        </label>
                        <label class="flex items-center gap-3 h-11 px-3 rounded-lg border border-zinc-200">
                            <span class="text-sm shrink-0">Hauteur</span>
                            <input id="cb-height" type="range" min="25" max="110" value="60" class="flex-1 accent-zinc-900">
                        </label>
                    </div>
                </div>

                <div class="bg-zinc-50 border-t lg:border-t-0 lg:border-s border-zinc-200 p-5 sm:p-7 flex flex-col">
                    <div id="cb-preview" class="flex-1 grid place-items-center min-h-[200px] max-h-[300px] rounded-xl bg-white border border-zinc-200 p-5 [&>svg]:max-h-[250px]"></div>
                    <p id="cb-country" class="mt-3 text-sm text-zinc-600 min-h-[1.25rem]" aria-live="polite"></p>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <button id="cb-png" type="button" class="inline-flex items-center justify-center gap-1.5 h-12 rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold hover:bg-[#ffd633]">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12m0 0-5-5m5 5 5-5M5 20h14"/></svg> PNG
                        </button>
                        <button id="cb-svg" type="button" class="inline-flex items-center justify-center gap-1.5 h-12 rounded-lg bg-zinc-900 text-white font-semibold hover:bg-zinc-700">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12m0 0-5-5m5 5 5-5M5 20h14"/></svg> SVG
                        </button>
                        <a href="#etiquettes" class="inline-flex items-center justify-center gap-1.5 h-12 rounded-lg border border-zinc-300 bg-white font-semibold hover:border-zinc-900">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 8V4h10v4M7 17H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M7 14h10v6H7z"/></svg> Étiquettes
                        </a>
                    </div>
                </div>
            </div>
            <p class="mt-4 text-xs text-zinc-400">Encodage conforme aux normes EAN/UPC (GS1) et Code 128. Le générateur dessine le code ; pour vendre en magasin, le numéro doit venir de GS1 (<a href="#gs1" class="underline hover:text-white">voir pourquoi</a>).</p>
        </div>
    </section>

    {{-- ÉTIQUETTES --}}
    <section id="etiquettes" class="bg-white border-b border-zinc-200 scroll-mt-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 grid lg:grid-cols-[1.15fr_1fr] gap-10 items-start [&>*]:min-w-0">
            <div>
                <p class="text-sm font-semibold text-zinc-500">Étiquettes code barre</p>
                <h2 class="mt-2 text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900">Imprimez vos étiquettes code barre sur une planche A4</h2>
                <p class="mt-3 text-zinc-600 leading-relaxed">Le code barre du générateur ci-dessus est placé sur des étiquettes adhésives au format A4, avec la désignation et le prix si vous le souhaitez. Pour plusieurs produits, collez une liste.</p>

                <div class="mt-6 grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="lb-format" class="block text-sm font-semibold text-zinc-900 mb-1.5">Format de la planche</label>
                        <select id="lb-format" class="{{ $field }}">
                            <option value="3x8">24 étiquettes par page · 70 × 37 mm</option>
                            <option value="3x7">21 étiquettes par page · 70 × 42,3 mm</option>
                            <option value="4x10">40 étiquettes par page · 52,5 × 29,7 mm</option>
                            <option value="2x7">14 étiquettes par page · 99,1 × 38,1 mm</option>
                            <option value="5x13">65 étiquettes par page · 38,1 × 21,2 mm</option>
                        </select>
                    </div>
                    <div>
                        <label for="lb-name" class="block text-sm font-semibold text-zinc-900 mb-1.5">Désignation <span class="font-normal text-zinc-400">(facultatif)</span></label>
                        <input id="lb-name" type="text" maxlength="60" placeholder="Ex. Vis inox 4 × 30" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="lb-price" class="block text-sm font-semibold text-zinc-900 mb-1.5">Prix <span class="font-normal text-zinc-400">(facultatif)</span></label>
                        <input id="lb-price" type="text" maxlength="20" placeholder="Ex. 4,90 €" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="lb-qty" class="block text-sm font-semibold text-zinc-900 mb-1.5">Nombre d’étiquettes</label>
                        <input id="lb-qty" type="number" min="1" max="1000" value="24" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="lb-start" class="block text-sm font-semibold text-zinc-900 mb-1.5">Commencer à l’étiquette n°</label>
                        <input id="lb-start" type="number" min="1" max="65" value="1" class="{{ $field }}">
                    </div>
                </div>

                <details class="mt-4 rounded-xl border border-zinc-200 bg-zinc-50 group">
                    <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-zinc-900 flex items-center justify-between">Plusieurs produits ? Collez une liste <span class="text-zinc-400 group-open:rotate-45 transition text-lg leading-none">+</span></summary>
                    <div class="px-4 pb-4">
                        <p class="text-sm text-zinc-500">Une ligne par produit : <span class="font-mono text-zinc-700">code ; désignation ; prix ; quantité</span>. Le type de code est celui choisi plus haut. Une colonne copiée depuis Excel fonctionne aussi.</p>
                        <textarea id="lb-list" rows="5" spellcheck="false" class="mt-2 w-full rounded-lg border-zinc-300 font-mono text-sm focus:border-zinc-900 focus:ring-zinc-900" placeholder="2001234567893 ; Vis inox 4 × 30 ; 4,90 € ; 12&#10;2001234567909 ; Cheville 8 mm ; 2,50 € ; 12"></textarea>
                    </div>
                </details>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <button id="lb-print" type="button" class="inline-flex items-center gap-2 h-12 px-6 rounded-lg bg-zinc-900 text-white font-semibold hover:bg-zinc-700">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 8V4h10v4M7 17H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M7 14h10v6H7z"/></svg>
                        Imprimer la planche
                    </button>
                    <p id="lb-msg" class="{{ $msg }}" aria-live="polite" data-level="ok"></p>
                </div>
                <p class="mt-3 text-xs text-zinc-500">Dans la fenêtre d’impression : échelle 100 % (taille réelle), marges « Aucune ». Pour un PDF, choisissez « Enregistrer en PDF ». Faites un premier essai sur une feuille blanche, posée sur la planche à contre-jour.</p>
            </div>
            <div class="lg:sticky lg:top-24">
                <div id="lb-preview" class="relative w-full max-w-[420px] mx-auto rounded-md bg-white shadow-xl ring-1 ring-zinc-200 overflow-hidden" aria-label="Aperçu de la première page de la planche"></div>
                <p class="mt-3 text-center text-xs text-zinc-400">Aperçu de la première page</p>
            </div>
        </div>
    </section>

    {{-- SOMMAIRE --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-12">
        <div class="bg-white border border-zinc-200 shadow-sm p-5 rounded-xl">
            <p class="text-xs font-semibold text-zinc-500">Sommaire</p>
            <ol class="mt-2 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-[15px] list-decimal ps-5">
                <li><a href="#generer" class="{{ $a }}">Générer un code barre en 3 étapes</a></li>
                <li><a href="#types" class="{{ $a }}">EAN-13, EAN-8, UPC-A ou Code 128 ?</a></li>
                <li><a href="#verification" class="{{ $a }}">Vérification de code barre en ligne</a></li>
                <li><a href="#pays" class="{{ $a }}">Code barre par pays</a></li>
                <li><a href="#gs1" class="{{ $a }}">Faut-il acheter ses codes chez GS1 ?</a></li>
                <li><a href="#stokito" class="{{ $a }}">Scanner vos codes barres dans Stokito</a></li>
                <li><a href="#faq" class="{{ $a }}">Questions fréquentes</a></li>
            </ol>
        </div>
    </div>

    <article class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-[17px] leading-[1.85] text-zinc-700">

        <h2 id="generer" class="mt-4 mb-5 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24">Comment générer un code barre gratuitement en 3 étapes</h2>
        <ol class="mb-6 space-y-4">
            @foreach ([
                ['Choisissez le type', 'EAN-13 pour un produit vendu en magasin, Code 128 pour une référence interne, un carton ou un emplacement.'],
                ['Tapez le numéro', 'Pour un EAN-13, 12 chiffres suffisent : le générateur calcule le 13e, la clé de contrôle. S’il y a une erreur, il vous le dit tout de suite.'],
                ['Téléchargez ou imprimez', 'PNG pour Word, Excel ou Canva, SVG pour un imprimeur ou un emballage, ou une planche d’étiquettes A4 prête à imprimer.'],
            ] as $i => [$t, $d])
                <li class="flex gap-4">
                    <span class="grid place-items-center w-9 h-9 shrink-0 rounded-full bg-[#ffcc00] text-zinc-900 font-bold">{{ $i + 1 }}</span>
                    <span><strong class="text-zinc-900">{{ $t }}.</strong> {{ $d }}</span>
                </li>
            @endforeach
        </ol>
        <p class="mb-5">Ce <strong class="text-zinc-900">générateur de code barre</strong> est gratuit, sans inscription et sans filigrane. Le dessin des barres se fait dans votre navigateur : aucun numéro n’est envoyé ni enregistré sur nos serveurs.</p>

        <h2 id="types" class="{{ $h2 }}">Quel code barre choisir : EAN-13, EAN-8, UPC-A ou Code 128 ?</h2>
        <div class="my-6 overflow-x-auto rounded-xl border border-zinc-200 bg-white">
            <table class="w-full text-[15px]">
                <thead class="bg-zinc-50 text-zinc-500 text-sm"><tr><th class="text-start font-semibold px-4 py-3">Type</th><th class="text-start font-semibold px-4 py-3">Contenu</th><th class="text-start font-semibold px-4 py-3">Pour quoi faire</th></tr></thead>
                <tbody class="divide-y divide-zinc-100">
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">EAN-13</td><td class="px-4 py-3">13 chiffres</td><td class="px-4 py-3">Le code des produits vendus en magasin en Europe et dans la plupart des pays</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">EAN-8</td><td class="px-4 py-3">8 chiffres</td><td class="px-4 py-3">Les petits emballages où un EAN-13 ne tient pas</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">UPC-A</td><td class="px-4 py-3">12 chiffres</td><td class="px-4 py-3">Les produits vendus aux États-Unis et au Canada</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">Code 128</td><td class="px-4 py-3">Lettres, chiffres, signes</td><td class="px-4 py-3">Références internes, colis, cartons, emplacements d’entrepôt, numéros de lot</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mb-5">En résumé : un produit qui part en rayon porte un <strong class="text-zinc-900">code barre EAN</strong>. Pour tout ce qui reste chez vous (stock, réserve, étagères), le Code 128 est plus souple, car il accepte vos propres références, comme « VIS-INOX-430 ».</p>

        <h2 id="verification" class="{{ $h2 }}">Vérification de code barre en ligne : la clé de contrôle</h2>
        <p class="mb-5">Le dernier chiffre d’un EAN ou d’un UPC n’est pas choisi au hasard : c’est une <strong class="text-zinc-900">clé de contrôle</strong>, calculée à partir des autres chiffres. Si un chiffre est mal tapé ou mal lu, la clé ne correspond plus et la caisse rejette le code. Collez un numéro ici pour le vérifier :</p>
        <div class="my-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm not-prose">
            <label for="vf-value" class="block text-sm font-semibold text-zinc-900">Code à vérifier (EAN-8, UPC-A, EAN-13 ou GTIN-14)</label>
            <input id="vf-value" type="text" inputmode="numeric" autocomplete="off" spellcheck="false" placeholder="Ex. 3760123456784" class="mt-2 {{ $field }} font-mono">
            <p id="vf-result" class="mt-3 text-[15px] leading-relaxed data-[level=error]:text-red-600 data-[level=ok]:text-zinc-800" aria-live="polite"></p>
        </div>
        <p class="mb-3">Le calcul, sur l’exemple 376012345678 (12 premiers chiffres d’un EAN-13) :</p>
        <ol class="mb-6 list-decimal ps-6 space-y-2">
            <li>on multiplie les chiffres en partant de la droite, alternativement par 3 et par 1 : 8×3, 7×1, 6×3, 5×1… ;</li>
            <li>on additionne : 24 + 7 + 18 + 5 + 12 + 3 + 6 + 1 + 0 + 6 + 21 + 3 = 106 ;</li>
            <li>la clé est ce qu’il manque pour atteindre la dizaine suivante : 110 − 106 = <strong class="text-zinc-900">4</strong>. Le code complet est 3760123456784.</li>
        </ol>

        <h2 id="pays" class="{{ $h2 }}">Code barre par pays : à quoi correspondent les premiers chiffres ?</h2>
        <p class="mb-5">Les trois premiers chiffres d’un EAN-13 forment le <strong class="text-zinc-900">préfixe GS1</strong>. Il indique l’organisation GS1 auprès de laquelle l’entreprise a obtenu ses codes, donc en général le pays où elle est inscrite. Il ne dit pas où le produit a été fabriqué : une marque française qui fait produire en Asie garde un code qui commence par 3.</p>
        <div class="my-6 grid sm:grid-cols-2 gap-3">
            @foreach ($digits as [$d, $txt])
                <div class="flex gap-3 rounded-xl border border-zinc-200 bg-white p-4">
                    <span class="grid place-items-center w-11 h-11 shrink-0 rounded-lg bg-zinc-900 text-amber-300 font-mono font-bold {{ strlen($d) > 1 ? 'text-xs' : 'text-base' }}">{{ str_replace(' ou ', '/', $d) }}</span>
                    <span class="text-[15px] leading-snug"><strong class="block text-zinc-900">Code barre qui commence par {{ $d }}</strong>{{ $txt }}</span>
                </div>
            @endforeach
        </div>
        <p class="mb-5">Pour un préfixe précis, collez le code dans l’outil de <a href="#verification" class="{{ $a }}">vérification</a> ci-dessus : il affiche le pays correspondant. Un préfixe 611 correspond au Maroc, 619 à la Tunisie, 613 à l’Algérie.</p>

        <h2 id="gs1" class="{{ $h2 }}">Faut-il acheter ses codes barres chez GS1 ?</h2>
        <p class="mb-5">Un générateur dessine les barres d’un numéro ; il ne vous donne pas ce numéro. Dans un EAN-13, les premiers chiffres après le préfixe pays identifient l’entreprise, et ce sont les organisations GS1 (GS1 France, GS1 Maroc, etc.) qui les attribuent.</p>
        <ul class="mb-6 list-disc ps-6 space-y-2">
            <li><strong class="text-zinc-900">Vous vendez en grande surface, en pharmacie ou sur Amazon</strong> : vos codes doivent venir de GS1. Ces distributeurs vérifient que le code appartient bien à votre entreprise. L’adhésion est annuelle et son tarif dépend en général du chiffre d’affaires.</li>
            <li><strong class="text-zinc-900">Le code ne sert que chez vous</strong> (stock, entrepôt, boutique qui étiquette ses propres articles) : GS1 n’est pas nécessaire. Prenez un EAN-13 qui commence par 2, plage réservée à l’usage interne (bouton « Code interne » du générateur), ou un Code 128 avec vos références.</li>
            <li>Ne recopiez jamais le code d’un autre produit : à la caisse, c’est l’autre produit qui sortirait.</li>
        </ul>

        <h2 id="stokito" class="{{ $h2 }}">Scanner vos codes barres dans Stokito</h2>
        <p class="mb-5">Une fois vos étiquettes collées, il faut que le code serve à quelque chose. Dans <a href="{{ url('/') }}" class="{{ $a }}">Stokito</a>, chaque produit a son code barre :</p>
        <ul class="mb-6 list-disc ps-6 space-y-2">
            <li>la <strong class="text-zinc-900">caméra du téléphone</strong> scanne le code pour retrouver le produit dans le catalogue ou remplir le champ code barre d’un nouvel article ;</li>
            <li>dans un devis, un bon de livraison, une facture ou un bon de commande fournisseur, un scan ajoute la ligne ;</li>
            <li>à la <a href="{{ route('seo.caisse') }}" class="{{ $a }}">caisse</a>, un scan à la caméra ou à la douchette ajoute l’article au ticket, et le stock baisse à chaque vente.</li>
        </ul>
        <div class="my-8 rounded-2xl bg-zinc-900 text-white p-6 sm:p-8">
            <p class="text-xl font-bold">Vos codes barres, votre stock et votre caisse au même endroit</p>
            <p class="mt-2 text-zinc-300">Stokito est un <a href="{{ route('seo.logiciel-gratuit') }}" class="underline decoration-amber-400 decoration-2 underline-offset-4">logiciel de gestion de stock gratuit</a>, sans carte bancaire : produits, codes barres, devis, factures et caisse.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="inline-flex items-center h-11 px-5 rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold">Créer un compte gratuit</a>
                <a href="{{ route('demo.login') }}" class="inline-flex items-center h-11 px-5 rounded-lg border border-white/25 font-semibold hover:bg-white/10">Tester la démo</a>
            </div>
        </div>

        <h2 id="faq" class="{{ $h2 }}">Questions fréquentes sur les codes barres</h2>
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
<script src="/js/code-barre.js?v=20261126" defer></script>
@endcomponent
