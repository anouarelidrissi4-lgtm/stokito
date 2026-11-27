{{-- SEO tool page (FR), 2026-11-27 — cluster "scanner code barre en ligne" / "scanner code barre" (2 400) / "lecteur code barre en ligne",
     "scanner un code barre avec son téléphone", "scanner code barre pc", "douchette". Tool = public/js/scanner-code-barre.js
     (engine.js + scanner-ui.js), BarcodeDetector natif sinon ZXing 0.21.3 auto-hébergé (/js/zxing-0.21.3.min.js, chargé au 1er scan).
     Classes used only in JS (Tailwind safelist): inline-flex items-center justify-center h-11 px-4 rounded-lg bg-zinc-900 text-white text-sm font-semibold
     hover:bg-zinc-700 border border-zinc-300 bg-white hover:border-zinc-900 bg-[#ffcc00] text-zinc-900 hover:bg-[#ffd633] text-xs text-zinc-500 mt-1 font-mono
     text-2xl sm:text-[28px] font-bold break-all leading-tight mt-2 leading-relaxed text-red-600 text-zinc-700 mt-4 flex flex-wrap gap-2 gap-3 py-2 flex-1
     min-w-0 block text-[15px] truncate shrink-0 min-w-[2.25rem] h-7 px-2 grid place-items-center rounded-full bg-zinc-100 w-8 h-8 text-zinc-400
     hover:text-red-600 hover:bg-zinc-100 --}}
@php
    $url = route('seo.scanner-code-barre');
    $faqs = [
        ['Comment scanner un code barre en ligne gratuitement ?', 'Ouvrez cette page sur votre téléphone ou votre ordinateur, cliquez sur « Scanner avec la caméra » et autorisez l’accès à la caméra. Placez le code barre dans le cadre, à 10-20 cm : le numéro s’affiche avec son type, son pays et la vérification de la clé de contrôle. C’est gratuit, sans inscription et sans application à installer.'],
        ['Peut-on scanner un code barre avec un iPhone sans application ?', 'Oui. Ouvrez cette page dans Safari, touchez « Scanner avec la caméra » et autorisez la caméra. L’appareil photo de l’iPhone lit seul les QR codes, mais pas les codes barres produits (EAN-13) : pour ceux-là, le scanner en ligne de cette page fait le travail.'],
        ['Comment scanner un code barre sur PC sans douchette ?', 'Avec la webcam : cliquez sur « Scanner avec la caméra » et approchez le code barre. Beaucoup de webcams ont une mise au point fixe et lisent mal les petits codes : dans ce cas, prenez le code en photo avec votre téléphone, envoyez-vous la photo et ouvrez-la dans l’onglet « Photo ».'],
        ['Le scanner lit-il aussi les QR codes ?', 'Oui. Il lit les codes barres EAN-13, EAN-8, UPC-A, UPC-E, Code 128, Code 39, Code 93, ITF et Codabar, ainsi que les QR codes, Data Matrix, PDF417 et Aztec. Si un QR code contient un lien, il est affiché sans être ouvert : vérifiez l’adresse avant de cliquer.'],
        ['Comment savoir à quel produit correspond un code barre ?', 'Il n’existe pas de base publique de tous les produits. Après le scan, cliquez sur « Rechercher le produit » (recherche Google du numéro) ou, pour un produit alimentaire, sur « Open Food Facts », une base collaborative. Le préfixe affiché indique le pays où la marque a obtenu ses codes, pas le pays de fabrication.'],
        ['Ma douchette tape des caractères comme &é"\'( au lieu des chiffres, pourquoi ?', 'La douchette envoie les touches comme un clavier QWERTY alors que votre ordinateur est en AZERTY. Dans le manuel du lecteur, scannez le code de configuration « French keyboard » (ou « AZERTY »), ou passez le clavier de l’ordinateur en disposition anglaise le temps du test.'],
        ['Les codes scannés sont-ils envoyés quelque part ?', 'Non. L’image de la caméra et les photos sont lues dans votre navigateur, sur votre appareil. La liste des codes scannés reste dans ce navigateur : vous pouvez l’exporter en CSV ou l’effacer à tout moment.'],
    ];
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Générateur de code barre', 'item' => route('seo.code-barre')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Scanner code barre en ligne', 'item' => $url],
            ],
        ],
        [
            '@type' => 'WebApplication',
            'name' => 'Scanner code barre en ligne gratuit',
            'url' => $url,
            'applicationCategory' => 'UtilitiesApplication',
            'operatingSystem' => 'Tous (navigateur web : Android, iPhone, Windows, Mac)',
            'inLanguage' => 'fr',
            'isAccessibleForFree' => true,
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
            'featureList' => 'Scan à la caméra du téléphone ou de la webcam, lecture depuis une photo, test de douchette, EAN-13, EAN-8, UPC, Code 128, Code 39, ITF, QR code, Data Matrix, vérification de la clé de contrôle, pays du préfixe GS1, export CSV',
            'publisher' => ['@type' => 'Organization', 'name' => 'Stokito', 'url' => url('/')],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs),
        ],
    ]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $a = 'font-semibold text-zinc-900 underline decoration-amber-400 decoration-2 underline-offset-4';
    $h2 = 'mt-16 mb-5 pt-4 border-t border-zinc-200 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24';
    $msg = 'text-sm leading-snug data-[level=error]:text-red-600 data-[level=info]:text-zinc-600 data-[level=ok]:text-zinc-900 data-[level=ok]:font-semibold';
    $tabBtn = 'h-10 rounded-lg text-[13px] sm:text-sm font-semibold text-zinc-600 hover:text-zinc-900 aria-checked:bg-zinc-900 aria-checked:text-white aria-checked:shadow transition';
@endphp
@component('layouts.public', [
    'title' => 'Scanner code barre en ligne gratuit (téléphone, PC, photo) | Stokito',
    'description' => 'Scanner code barre en ligne gratuit : lisez un EAN-13, un Code 128 ou un QR code avec la caméra de votre téléphone ou de votre PC, ou depuis une photo. Pays, clé de contrôle, export CSV.',
    'canonical' => $url,
    'jsonLd' => $jsonLd,
])
<style>
    #sc-app[data-live="1"] .sc-off, #sc-app[data-live="0"] .sc-on { display: none !important; }
    #sc-app[data-history="0"] .sc-hist, #sc-app [hidden] { display: none !important; }
    #sc-result[data-filled="1"] .sc-empty { display: none; }
    #sc-drop[data-over="1"] { border-color: #111; background: #fff8db; }
    .sc-line { position: absolute; left: 6%; right: 6%; height: 2px; background: #ffcc00; box-shadow: 0 0 12px #ffcc00; animation: sc-scan 2.2s ease-in-out infinite; }
    @@keyframes sc-scan { 0%, 100% { top: 12%; } 50% { top: 86%; } }
    @@media (prefers-reduced-motion: reduce) { .sc-line { animation: none; top: 50%; } }
    .sc-corner { position: absolute; width: 26px; height: 26px; border: 0 solid #ffcc00; }
    .sc-tl { top: 0; left: 0; border-top-width: 4px; border-left-width: 4px; border-top-left-radius: 8px; }
    .sc-tr { top: 0; right: 0; border-top-width: 4px; border-right-width: 4px; border-top-right-radius: 8px; }
    .sc-bl { bottom: 0; left: 0; border-bottom-width: 4px; border-left-width: 4px; border-bottom-left-radius: 8px; }
    .sc-br { bottom: 0; right: 0; border-bottom-width: 4px; border-right-width: 4px; border-bottom-right-radius: 8px; }
</style>
<div>
    {{-- HERO + OUTIL --}}
    <section class="bg-zinc-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14">
            <nav aria-label="Fil d’Ariane" class="text-xs text-zinc-400">
                <a href="{{ url('/') }}" class="hover:text-white">Accueil</a> <span aria-hidden="true">/</span>
                <a href="{{ route('seo.code-barre') }}" class="hover:text-white">Générateur de code barre</a> <span aria-hidden="true">/</span>
                <span class="text-zinc-300">Scanner code barre en ligne</span>
            </nav>
            <div class="mt-8 max-w-3xl">
                <p class="text-sm font-semibold text-amber-300">Gratuit · sans application · téléphone, PC et photo</p>
                <h1 class="mt-3 text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.05]">Scanner code barre en ligne <span class="text-amber-300">gratuit</span></h1>
                <p class="mt-5 text-lg text-zinc-300 leading-relaxed">Lisez un code barre avec la caméra de votre téléphone ou de votre ordinateur, ou depuis une photo. Vous obtenez le numéro, le type de code, le pays du préfixe et la vérification de la clé de contrôle. Tout se passe dans votre navigateur : rien n’est envoyé.</p>
            </div>

            <div id="sc-app" data-live="0" data-history="0" class="mt-9 grid lg:grid-cols-[1.1fr_1fr] rounded-2xl bg-white text-zinc-900 shadow-2xl shadow-black/40 overflow-hidden [&>*]:min-w-0">
                <div class="p-5 sm:p-7">
                    <div class="grid grid-cols-3 gap-1 p-1 rounded-xl bg-zinc-100" role="radiogroup" aria-label="Mode de lecture">
                        <button type="button" role="radio" data-sc-tab="camera" aria-checked="true" class="{{ $tabBtn }}">Caméra</button>
                        <button type="button" role="radio" data-sc-tab="image" aria-checked="false" class="{{ $tabBtn }}">Photo</button>
                        <button type="button" role="radio" data-sc-tab="douchette" aria-checked="false" class="{{ $tabBtn }}">Douchette</button>
                    </div>

                    {{-- caméra --}}
                    <div data-sc-panel="camera" class="mt-4">
                        <div class="relative aspect-[4/3] rounded-xl bg-zinc-950 overflow-hidden">
                            <video id="sc-video" class="sc-on absolute inset-0 w-full h-full object-cover" playsinline muted></video>
                            <div class="sc-on absolute inset-x-[10%] inset-y-[24%] pointer-events-none" aria-hidden="true">
                                <span class="sc-corner sc-tl"></span>
                                <span class="sc-corner sc-tr"></span>
                                <span class="sc-corner sc-bl"></span>
                                <span class="sc-corner sc-br"></span>
                                <span class="sc-line"></span>
                            </div>
                            <div class="sc-off absolute inset-0 grid place-items-center p-6 text-center">
                                <div>
                                    <svg class="mx-auto w-16 h-12 text-zinc-600" viewBox="0 0 64 48" fill="currentColor" aria-hidden="true"><path d="M4 4h3v40H4zm6 0h1.5v40H10zm4 0h4v40h-4zm7 0h1.5v40H21zm4 0h3v40h-3zm6 0h1.5v40H31zm4 0h4v40h-4zm7 0h1.5v40H42zm4 0h3v40h-3zm6 0h1.5v40H52zm4 0h4v40h-4z"/></svg>
                                    <p class="mt-3 text-sm text-zinc-400">L’image de la caméra s’affichera ici.<br>Elle reste sur votre appareil.</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <button id="sc-start" type="button" class="sc-off inline-flex items-center justify-center gap-2 h-12 px-5 rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold hover:bg-[#ffd633] disabled:opacity-60 flex-1 sm:flex-none">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 8v8M10.5 8v8M14 8v8M17 8v8"/></svg>
                                Scanner avec la caméra
                            </button>
                            <button id="sc-stop" type="button" class="sc-on inline-flex items-center justify-center h-12 px-5 rounded-lg bg-zinc-900 text-white font-semibold hover:bg-zinc-700">Arrêter</button>
                            <button id="sc-torch" type="button" hidden aria-pressed="false" class="inline-flex items-center justify-center h-12 px-4 rounded-lg border border-zinc-300 font-semibold hover:border-zinc-900 aria-pressed:bg-[#ffcc00] aria-pressed:border-[#ffcc00]">Lampe</button>
                            <select id="sc-cams" hidden aria-label="Choisir la caméra" class="h-12 min-w-0 flex-1 rounded-lg border-zinc-300 text-sm focus:border-zinc-900 focus:ring-zinc-900"></select>
                        </div>
                        <label class="mt-3 flex items-center gap-3 cursor-pointer select-none">
                            <span class="relative inline-flex items-center">
                                <input id="sc-multi" type="checkbox" class="peer sr-only">
                                <span class="w-10 h-6 rounded-full bg-zinc-300 peer-checked:bg-[#ffcc00] transition"></span>
                                <span class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow peer-checked:translate-x-4 transition"></span>
                            </span>
                            <span class="text-sm">Scanner plusieurs codes à la suite <span class="text-zinc-500">(inventaire, réception)</span></span>
                        </label>
                    </div>

                    {{-- photo --}}
                    <div data-sc-panel="image" class="mt-4" hidden>
                        <label id="sc-drop" for="sc-file" class="flex flex-col items-center justify-center gap-3 aspect-[4/3] rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-6 text-center cursor-pointer hover:border-zinc-900 transition">
                            <img id="sc-thumb" alt="Photo du code barre à lire" hidden class="max-h-[55%] max-w-full rounded-lg object-contain">
                            <svg class="w-10 h-10 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h3l2-3h6l2 3h3a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z"/><circle cx="12" cy="13" r="4"/></svg>
                            <span class="text-[15px] font-semibold text-zinc-900">Choisir une photo du code barre</span>
                            <span class="text-sm text-zinc-500">ou glissez-la ici, ou collez une capture d’écran (Ctrl + V)</span>
                        </label>
                        <input id="sc-file" type="file" accept="image/*" class="sr-only">
                        <p class="mt-3 text-xs text-zinc-500">Astuce : cadrez le code barre en entier, bien droit et assez grand. Une photo floue ou prise de très loin ne se lit pas.</p>
                    </div>

                    {{-- douchette --}}
                    <div data-sc-panel="douchette" class="mt-4" hidden>
                        <div class="aspect-[4/3] flex flex-col justify-center rounded-xl bg-zinc-50 border border-zinc-200 p-5 sm:p-7">
                            <label for="sc-wedge" class="block text-sm font-semibold text-zinc-900">Tester une douchette (lecteur USB ou Bluetooth)</label>
                            <input id="sc-wedge" type="text" autocomplete="off" spellcheck="false" placeholder="Cliquez ici, puis scannez un code"
                                   class="mt-2 w-full h-14 rounded-lg border-zinc-300 text-lg sm:text-xl font-mono focus:border-zinc-900 focus:ring-zinc-900">
                            <p class="mt-3 text-sm text-zinc-600 leading-relaxed">La douchette se comporte comme un clavier : elle « tape » le code puis la touche Entrée. Si le code s’affiche dans le résultat, elle fonctionne. Vous pouvez aussi taper un numéro à la main et appuyer sur Entrée.</p>
                        </div>
                    </div>

                    <p id="sc-status" class="mt-4 min-h-[2.5rem] {{ $msg }}" aria-live="polite" data-level="info">Cliquez sur « Scanner avec la caméra » et autorisez l’accès à la caméra.</p>
                </div>

                <div class="bg-zinc-50 border-t lg:border-t-0 lg:border-s border-zinc-200 p-5 sm:p-7 flex flex-col">
                    <div id="sc-result" class="rounded-xl bg-white border border-zinc-200 p-5 min-h-[180px]" aria-live="polite">
                        <div class="sc-empty h-full grid place-items-center text-center py-6">
                            <p class="text-sm text-zinc-400">Le code lu s’affichera ici : numéro, type,<br>pays du préfixe et clé de contrôle.</p>
                        </div>
                    </div>
                    <div class="sc-hist mt-5">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                            <p class="flex-1 text-sm font-semibold text-zinc-900">Codes scannés <span id="sc-count" class="block font-normal text-xs text-zinc-500"></span></p>
                            <button id="sc-copy-all" type="button" class="h-9 px-3 rounded-lg border border-zinc-300 bg-white text-sm font-semibold hover:border-zinc-900">Copier tout</button>
                            <button id="sc-csv" type="button" class="h-9 px-3 rounded-lg bg-zinc-900 text-white text-sm font-semibold hover:bg-zinc-700">Export CSV</button>
                            <button id="sc-clear" type="button" class="h-9 px-3 rounded-lg text-sm font-semibold text-zinc-500 hover:text-red-600">Effacer</button>
                        </div>
                        <ul id="sc-history" class="mt-2 max-h-[260px] overflow-y-auto divide-y divide-zinc-200"></ul>
                    </div>
                </div>
            </div>
            <p class="mt-4 text-xs text-zinc-400">Lit les codes EAN-13, EAN-8, UPC-A, UPC-E, Code 128, Code 39, Code 93, ITF, Codabar, QR code, Data Matrix, PDF417 et Aztec. Fonctionne dans Chrome, Safari, Firefox et Edge, sur Android, iPhone, Windows et Mac.</p>
        </div>
    </section>

    {{-- SOMMAIRE --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-12">
        <div class="bg-white border border-zinc-200 shadow-sm p-5 rounded-xl">
            <p class="text-xs font-semibold text-zinc-500">Sommaire</p>
            <ol class="mt-2 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-[15px] list-decimal ps-5">
                <li><a href="#comment" class="{{ $a }}">Scanner un code barre en 3 étapes</a></li>
                <li><a href="#telephone" class="{{ $a }}">Avec son téléphone (Android, iPhone)</a></li>
                <li><a href="#pc" class="{{ $a }}">Sur PC : webcam ou douchette</a></li>
                <li><a href="#lire" class="{{ $a }}">Ce que contient un code barre</a></li>
                <li><a href="#produit" class="{{ $a }}">Trouver le produit d’un code barre</a></li>
                <li><a href="#probleme" class="{{ $a }}">Le code ne se lit pas ?</a></li>
                <li><a href="#stokito" class="{{ $a }}">Scanner pour gérer son stock</a></li>
                <li><a href="#faq" class="{{ $a }}">Questions fréquentes</a></li>
            </ol>
        </div>
    </div>

    <article class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-[17px] leading-[1.85] text-zinc-700">

        <h2 id="comment" class="mt-4 mb-5 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24">Comment scanner un code barre en ligne en 3 étapes</h2>
        <ol class="mb-6 space-y-4">
            @foreach ([
                ['Ouvrez le scanner', 'Cliquez sur « Scanner avec la caméra » en haut de cette page. Le navigateur vous demande l’autorisation d’utiliser la caméra : acceptez. Sur un téléphone, c’est la caméra arrière qui s’ouvre.'],
                ['Visez le code barre', 'Placez-le dans le cadre jaune, à 10-20 cm, bien éclairé. Inutile d’appuyer : le bip confirme que le code est lu.'],
                ['Lisez le résultat', 'Le numéro s’affiche avec son type (EAN-13, Code 128, QR code…), le pays du préfixe et la vérification de la clé de contrôle. Copiez-le, cherchez le produit ou créez son étiquette.'],
            ] as $i => [$t, $d])
                <li class="flex gap-4">
                    <span class="grid place-items-center w-9 h-9 shrink-0 rounded-full bg-[#ffcc00] text-zinc-900 font-bold">{{ $i + 1 }}</span>
                    <span><strong class="text-zinc-900">{{ $t }}.</strong> {{ $d }}</span>
                </li>
            @endforeach
        </ol>
        <p class="mb-5">Ce <strong class="text-zinc-900">scanner de code barre en ligne</strong> est gratuit, sans inscription et sans application à installer. L’image de la caméra est analysée dans votre navigateur, sur votre appareil : elle n’est ni envoyée ni enregistrée. Pour lire plusieurs produits à la suite, activez « Scanner plusieurs codes à la suite » : chaque code est ajouté à la liste avec le nombre de scans, puis vous l’exportez en CSV pour Excel.</p>

        <h2 id="telephone" class="{{ $h2 }}">Scanner un code barre avec son téléphone</h2>
        <p class="mb-5">Le téléphone est le meilleur <strong class="text-zinc-900">lecteur de code barre</strong> que vous ayez déjà : sa caméra fait la mise au point de près, ce que beaucoup de webcams ne font pas. Ouvrez simplement cette page dans le navigateur du téléphone.</p>
        <div class="my-6 grid sm:grid-cols-2 gap-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-5">
                <p class="font-bold text-zinc-900">Sur Android</p>
                <p class="mt-2 text-[15px] leading-relaxed">Ouvrez la page dans Chrome, touchez « Scanner avec la caméra », puis « Autoriser ». Si la lumière manque, le bouton « Lampe » allume le flash (sur la plupart des téléphones Android).</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5">
                <p class="font-bold text-zinc-900">Sur iPhone</p>
                <p class="mt-2 text-[15px] leading-relaxed">Ouvrez la page dans Safari, touchez « Scanner avec la caméra », puis « Autoriser ». L’appareil photo de l’iPhone lit seul les QR codes, mais pas les codes barres des produits : c’est à ça que sert ce scanner.</p>
            </div>
        </div>
        <p class="mb-5">Vous avez refusé la caméra par erreur ? Touchez l’icône à gauche de l’adresse du site (Chrome) ou « aA » (Safari), autorisez la caméra, puis rechargez la page.</p>

        <h2 id="pc" class="{{ $h2 }}">Scanner un code barre sur PC : webcam ou douchette</h2>
        <p class="mb-5">Sur un ordinateur, vous avez trois solutions, de la plus simple à la plus rapide :</p>
        <ul class="mb-6 list-disc ps-6 space-y-2">
            <li><strong class="text-zinc-900">La webcam</strong> : cliquez sur « Scanner avec la caméra ». Les QR codes et les grands codes barres passent bien ; les petits EAN-13 sont souvent flous, car beaucoup de webcams ont une mise au point fixe. Éloignez un peu le code plutôt que de le coller à l’objectif.</li>
            <li><strong class="text-zinc-900">Une photo prise avec le téléphone</strong> : envoyez-la-vous par e-mail ou WhatsApp, puis ouvrez-la dans l’onglet « Photo ». Une capture d’écran se colle directement avec Ctrl + V.</li>
            <li><strong class="text-zinc-900">Une douchette</strong> (lecteur de code barre USB ou Bluetooth) : c’est l’outil des caisses et des entrepôts. Elle lit un code en une fraction de seconde, même abîmé.</li>
        </ul>
        <p class="mb-5">Une douchette n’a besoin d’aucun pilote : l’ordinateur la voit comme un clavier qui tape le code puis Entrée. Pour vérifier la vôtre, ouvrez l’onglet « Douchette » de l’outil, cliquez dans le champ et scannez.</p>
        <div class="my-8 rounded-xl border-l-4 border-amber-300 bg-[#fff8db] px-5 py-4 text-[16px]">
            <strong class="text-zinc-900">La douchette tape &amp;é"'( au lieu de 12345 ?</strong>
            Elle est réglée en clavier QWERTY (américain) alors que votre ordinateur est en AZERTY : les chiffres sortent comme les touches de la rangée du haut sans Maj. Cherchez dans son manuel le code de configuration « French keyboard » ou « AZERTY » et scannez-le une fois : le réglage est gardé en mémoire.
        </div>

        <h2 id="lire" class="{{ $h2 }}">Ce que contient un code barre</h2>
        <p class="mb-5">Un code barre ne contient pas le nom ni le prix du produit : seulement un numéro (ou un texte, pour un QR code). C’est le logiciel du magasin qui associe ce numéro à une fiche produit. Voici ce que le scanner peut vous dire selon le type :</p>
        <div class="my-6 overflow-x-auto rounded-xl border border-zinc-200 bg-white">
            <table class="w-full text-[15px]">
                <thead class="bg-zinc-50 text-zinc-500 text-sm"><tr><th class="text-start font-semibold px-4 py-3">Type</th><th class="text-start font-semibold px-4 py-3">Où on le trouve</th><th class="text-start font-semibold px-4 py-3">Ce qu’il contient</th></tr></thead>
                <tbody class="divide-y divide-zinc-100">
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">EAN-13, EAN-8</td><td class="px-4 py-3">Produits vendus en magasin</td><td class="px-4 py-3">Le numéro GTIN du produit : préfixe pays, entreprise, article, clé de contrôle</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">UPC-A, UPC-E</td><td class="px-4 py-3">Produits américains et canadiens</td><td class="px-4 py-3">Le même numéro produit, en 12 chiffres (ou 8 pour l’UPC-E compressé)</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">ITF-14</td><td class="px-4 py-3">Cartons et palettes</td><td class="px-4 py-3">Le GTIN-14 du carton : un chiffre de conditionnement + le code du produit</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">Code 128, Code 39</td><td class="px-4 py-3">Colis, entrepôts, étiquettes internes</td><td class="px-4 py-3">Une référence libre : numéro de suivi, de lot, d’emplacement, code article</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">QR code, Data Matrix</td><td class="px-4 py-3">Affiches, médicaments, pièces industrielles</td><td class="px-4 py-3">Un texte ou un lien ; sur les boîtes de médicaments, le code produit, le lot et la date de péremption</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mb-5">Pour un EAN ou un UPC, le scanner vérifie la <strong class="text-zinc-900">clé de contrôle</strong> (le dernier chiffre, calculé à partir des autres) et affiche le pays du préfixe GS1 : 300 à 379 pour la France, 611 pour le Maroc, 690 à 699 pour la Chine… La liste complète est sur la page <a href="{{ route('seo.code-barre-pays') }}" class="{{ $a }}">code barre pays</a>. Attention : ce préfixe indique où la marque a obtenu ses codes, pas où le produit a été fabriqué.</p>

        <h2 id="produit" class="{{ $h2 }}">Trouver le produit qui correspond à un code barre</h2>
        <p class="mb-5">Il n’existe pas d’annuaire public de tous les codes barres du monde. Après un scan, l’outil propose deux recherches :</p>
        <ul class="mb-6 list-disc ps-6 space-y-2">
            <li><strong class="text-zinc-900">Rechercher le produit</strong> : une recherche Google du numéro. Les sites marchands publient souvent l’EAN dans leurs fiches, ce qui suffit à identifier la plupart des produits de grande consommation.</li>
            <li><strong class="text-zinc-900">Open Food Facts</strong> : une base de données ouverte et collaborative de produits alimentaires (ingrédients, Nutri-Score). Si le produit n’y est pas, n’importe qui peut l’ajouter.</li>
        </ul>
        <p class="mb-5">Un code qui commence par 2 (ou 02, 04) est un code interne : il a été créé par le magasin lui-même, par exemple pour un produit vendu au poids. Il ne correspond à aucun produit en dehors de ce magasin.</p>

        <h2 id="probleme" class="{{ $h2 }}">Le code barre ne se lit pas : que faire ?</h2>
        <ul class="mb-6 space-y-3">
            @foreach ([
                ['Reculez un peu', 'Trop près, la caméra ne fait pas la mise au point. 10 à 20 cm conviennent à un téléphone ; le code doit remplir à peu près le cadre.'],
                ['Évitez les reflets', 'Sur un emballage brillant ou une canette, inclinez légèrement le produit : un reflet blanc sur les barres suffit à bloquer la lecture.'],
                ['Ajoutez de la lumière', 'Activez la lampe (bouton « Lampe » sur Android) ou rapprochez-vous d’une fenêtre.'],
                ['Gardez le code droit', 'Les barres doivent traverser la ligne jaune. Pour un code très petit ou abîmé, passez par l’onglet « Photo » : l’image fixe est analysée plus finement.'],
                ['La caméra ne s’ouvre pas', 'Vérifiez que la caméra est autorisée pour ce site et qu’aucune autre application (visio, appareil photo) ne l’utilise. Changez de caméra avec le menu si votre ordinateur en a plusieurs.'],
            ] as [$t, $d])
                <li class="flex gap-3"><span class="mt-2 w-2 h-2 shrink-0 rounded-full bg-[#ffcc00]"></span><span><strong class="text-zinc-900">{{ $t }}.</strong> {{ $d }}</span></li>
            @endforeach
        </ul>

        <h2 id="stokito" class="{{ $h2 }}">Scanner ses codes barres pour gérer son stock</h2>
        <p class="mb-5">Scanner un code, c’est bien ; qu’il serve à quelque chose, c’est mieux. Dans <a href="{{ url('/') }}" class="{{ $a }}">Stokito</a>, le même scan à la caméra retrouve le produit dans votre catalogue :</p>
        <ul class="mb-6 list-disc ps-6 space-y-2">
            <li>dans la fiche d’un nouvel article, le scan remplit le champ code barre ;</li>
            <li>dans un devis, un bon de livraison, une facture ou un <a href="{{ route('seo.bon-de-commande') }}" class="{{ $a }}">bon de commande</a> fournisseur, un scan ajoute la ligne ;</li>
            <li>à la <a href="{{ route('seo.caisse') }}" class="{{ $a }}">caisse</a>, un scan à la caméra ou à la douchette ajoute l’article au ticket, et le stock baisse à chaque vente.</li>
        </ul>
        <p class="mb-5">Vos produits n’ont pas encore de code barre ? Créez-les avec le <a href="{{ route('seo.code-barre') }}" class="{{ $a }}">générateur de code barre gratuit</a> et imprimez-les sur des planches d’étiquettes A4.</p>
        <div class="my-8 rounded-2xl bg-zinc-900 text-white p-6 sm:p-8">
            <p class="text-xl font-bold">Vos codes barres, votre stock et votre caisse au même endroit</p>
            <p class="mt-2 text-zinc-300">Stokito est un <a href="{{ route('seo.logiciel-gratuit') }}" class="underline decoration-amber-400 decoration-2 underline-offset-4">logiciel de gestion de stock gratuit</a>, sans carte bancaire : produits, codes barres, devis, factures et caisse, sur ordinateur et sur téléphone.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="inline-flex items-center h-11 px-5 rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold">Créer un compte gratuit</a>
                <a href="{{ route('demo.login') }}" class="inline-flex items-center h-11 px-5 rounded-lg border border-white/25 font-semibold hover:bg-white/10">Tester la démo</a>
            </div>
        </div>

        <h2 id="faq" class="{{ $h2 }}">Questions fréquentes sur le scan de code barre</h2>
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
<script src="/js/scanner-code-barre.js?v=20261127b" defer></script>
@endcomponent
