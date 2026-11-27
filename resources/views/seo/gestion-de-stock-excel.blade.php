{{-- SEO page (FR), 2026-11-27 — cluster "gestion de stock excel", "logiciel gestion de stock gratuit excel" (260, KD 13),
     "tableau de gestion de stock excel gratuit", "modèle gestion de stock excel". Semrush FR 2026-11-25.
     Free template built by stockpilot-seo/excel/build_excel.py (+ Excel recalculation/save) → public/docs/modele-gestion-de-stock-excel.xlsx;
     it imports as it is into Stokito (Catalogue › Importer Excel reads the first sheet, formula results). --}}
@php
    $url = route('seo.stock-excel');
    $xlsx = '/docs/modele-gestion-de-stock-excel.xlsx';
    $faqs = [
        ['Comment faire une gestion de stock sur Excel ?', 'Il faut deux tableaux : la liste des produits (référence, désignation, stock de départ, seuil d’alerte) et le journal des mouvements (date, référence, entrée ou sortie, quantité). Une formule SOMME.SI.ENS additionne les entrées et les sorties de chaque produit, et le stock actuel vaut stock de départ + entrées − sorties. Le modèle gratuit de cette page est déjà construit ainsi.'],
        ['Existe-t-il un modèle de gestion de stock Excel gratuit ?', 'Oui, celui de cette page : un fichier .xlsx gratuit, sans inscription, avec la feuille Produits, la feuille Mouvements, un tableau de bord et un mode d’emploi. Le stock actuel, le statut (OK, À commander, Rupture) et la valeur du stock se calculent tout seuls.'],
        ['Comment calculer le stock actuel dans Excel ?', 'Stock actuel = stock initial + total des entrées − total des sorties. Dans le modèle, les entrées et les sorties sont additionnées depuis la feuille Mouvements avec =SOMME.SI.ENS(Mouvements!D:D;Mouvements!B:B;A2;Mouvements!C:C;"Entrée"), puis le stock est calculé avec =G2+H2-I2.'],
        ['Comment calculer le seuil d’alerte (point de commande) ?', 'Seuil d’alerte = consommation moyenne par jour × délai de livraison du fournisseur en jours + stock de sécurité. Par exemple, 5 ventes par jour, 7 jours de délai et 15 unités de sécurité donnent un seuil de 50 : dès que le stock descend à 50, il faut commander.'],
        ['Le modèle fonctionne-t-il avec Google Sheets ou LibreOffice ?', 'Oui. Le fichier s’ouvre dans Google Sheets et dans LibreOffice Calc, et ses formules (SOMME.SI.ENS, INDEX, EQUIV, NB.SI) y fonctionnent. Pour Google Sheets, importez le fichier dans Google Drive puis ouvrez-le.'],
        ['Combien de produits peut-on suivre avec ce fichier ?', 'Le modèle est prêt pour 300 produits et 2 000 mouvements. Pour aller au-delà, copiez la dernière ligne vers le bas : les formules suivent. Au-delà de quelques centaines de produits ou à plusieurs utilisateurs, un logiciel de gestion de stock devient plus sûr.'],
        ['Peut-on passer du fichier Excel à un logiciel sans tout ressaisir ?', 'Avec Stokito, oui : le fichier s’importe tel quel dans Catalogue › Produits › Importer Excel. Références, désignations, catégories, unités, prix d’achat, prix de vente TTC, stock et seuils d’alerte sont repris.'],
    ];
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Logiciel de gestion de stock gratuit', 'item' => route('seo.logiciel-gratuit')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Gestion de stock Excel', 'item' => $url],
            ],
        ],
        [
            '@type' => 'DigitalDocument',
            'name' => 'Modèle de gestion de stock Excel (gratuit)',
            'url' => url($xlsx),
            'encodingFormat' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inLanguage' => 'fr',
            'isAccessibleForFree' => true,
            'image' => url('/images/excel/gestion-stock-excel-produits.webp'),
            'publisher' => ['@type' => 'Organization', 'name' => 'Stokito', 'url' => url('/')],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs),
        ],
    ]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $a = 'font-semibold text-zinc-900 underline decoration-amber-400 decoration-2 underline-offset-4';
    $h2 = 'mt-16 mb-5 pt-4 border-t border-zinc-200 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24';
    $code = 'block my-3 overflow-x-auto rounded-lg bg-zinc-900 px-4 py-3 font-mono text-[14px] leading-relaxed text-amber-200 whitespace-pre';
@endphp
@component('layouts.public', [
    'title' => 'Gestion de stock Excel : modèle gratuit à télécharger (2026) | Stokito',
    'description' => 'Modèle de gestion de stock Excel gratuit : entrées, sorties, stock actuel, seuil d’alerte et valeur du stock calculés automatiquement. Téléchargez le fichier .xlsx, formules expliquées.',
    'canonical' => $url,
    'jsonLd' => $jsonLd,
    'ogImage' => url('/images/excel/gestion-stock-excel-produits.webp'),
])
<div>
    {{-- HERO --}}
    <section class="bg-zinc-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14">
            <nav aria-label="Fil d’Ariane" class="text-xs text-zinc-400">
                <a href="{{ url('/') }}" class="hover:text-white">Accueil</a> <span aria-hidden="true">/</span>
                <a href="{{ route('seo.logiciel-gratuit') }}" class="hover:text-white">Logiciel de gestion de stock gratuit</a> <span aria-hidden="true">/</span>
                <span class="text-zinc-300">Gestion de stock Excel</span>
            </nav>
            <div class="mt-8 max-w-3xl">
                <p class="text-sm font-semibold text-amber-300">Fichier .xlsx gratuit · sans inscription · formules prêtes</p>
                <h1 class="mt-3 text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.05]">Gestion de stock Excel : <span class="text-amber-300">le modèle gratuit</span></h1>
                <p class="mt-5 text-lg text-zinc-300 leading-relaxed">Un tableau de gestion de stock Excel prêt à l’emploi : vous notez vos entrées et vos sorties, le fichier calcule le stock actuel, signale les produits à commander et donne la valeur de votre stock.</p>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="{{ $xlsx }}" download class="inline-flex items-center gap-2 h-12 px-6 rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold hover:bg-[#ffd633]">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12m0 0-5-5m5 5 5-5M5 20h14"/></svg>
                        Télécharger le modèle Excel
                    </a>
                    <span class="text-sm text-zinc-400">.xlsx · 100 Ko · Excel, Google Sheets, LibreOffice</span>
                </div>
            </div>
            <figure class="mt-10 rounded-2xl bg-white p-3 sm:p-4 shadow-2xl shadow-black/40">
                <div class="overflow-x-auto">
                    <img src="/images/excel/gestion-stock-excel-produits.webp" width="1748" height="338" alt="Modèle de gestion de stock Excel : feuille Produits avec stock actuel, statut OK, À commander ou Rupture et valeur du stock" class="block min-w-[720px] w-full h-auto">
                </div>
                <figcaption class="mt-2 px-1 text-xs text-zinc-500">La feuille « Produits » : les colonnes grises (entrées, sorties, stock, statut, valeur) se calculent toutes seules.</figcaption>
            </figure>
        </div>
    </section>

    {{-- SOMMAIRE --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-12">
        <div class="bg-white border border-zinc-200 shadow-sm p-5 rounded-xl">
            <p class="text-xs font-semibold text-zinc-500">Sommaire</p>
            <ol class="mt-2 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-[15px] list-decimal ps-5">
                <li><a href="#contenu" class="{{ $a }}">Ce que contient le modèle</a></li>
                <li><a href="#utiliser" class="{{ $a }}">L’utiliser en 4 étapes</a></li>
                <li><a href="#formules" class="{{ $a }}">Les formules Excel expliquées</a></li>
                <li><a href="#seuil" class="{{ $a }}">Calculer le seuil d’alerte</a></li>
                <li><a href="#valeur" class="{{ $a }}">Valoriser son stock</a></li>
                <li><a href="#limites" class="{{ $a }}">Les limites d’Excel</a></li>
                <li><a href="#logiciel" class="{{ $a }}">Passer à un logiciel gratuit</a></li>
                <li><a href="#faq" class="{{ $a }}">Questions fréquentes</a></li>
            </ol>
        </div>
    </div>

    <article class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-[17px] leading-[1.85] text-zinc-700">

        <h2 id="contenu" class="mt-4 mb-5 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24">Ce que contient le modèle de gestion de stock Excel</h2>
        <div class="my-6 grid sm:grid-cols-2 gap-4">
            @foreach ([
                ['Produits', 'Une ligne par article : référence, désignation, catégorie, unité, prix d’achat, prix de vente TTC, stock initial et seuil d’alerte. Le stock actuel, le statut et la valeur se calculent.'],
                ['Mouvements', 'Le journal des entrées et sorties : date, référence choisie dans une liste, « Entrée » ou « Sortie », quantité. La désignation s’affiche toute seule.'],
                ['Tableau de bord', 'Nombre de produits, valeur du stock au prix d’achat, produits à commander, produits en rupture, entrées et sorties du mois.'],
                ['Mode d’emploi', 'Les règles du fichier en une page, pour vous et pour la personne qui le remplira demain.'],
            ] as [$t, $d])
                <div class="rounded-xl border border-zinc-200 bg-white p-5">
                    <p class="font-bold text-zinc-900">{{ $t }}</p>
                    <p class="mt-2 text-[15px] leading-relaxed">{{ $d }}</p>
                </div>
            @endforeach
        </div>
        <figure class="my-8 max-w-md mx-auto">
            <img src="/images/excel/gestion-stock-excel-tableau-de-bord.webp" width="816" height="523" loading="lazy" alt="Tableau de bord du modèle de gestion de stock Excel : valeur du stock, produits à commander et en rupture" class="w-full h-auto rounded-xl border border-zinc-200 shadow-sm">
            <figcaption class="mt-2 text-center text-sm text-zinc-500">Le tableau de bord se met à jour à chaque mouvement.</figcaption>
        </figure>

        <h2 id="utiliser" class="{{ $h2 }}">Utiliser le tableau de gestion de stock en 4 étapes</h2>
        <ol class="mb-6 space-y-4">
            @foreach ([
                ['Listez vos produits', 'Dans « Produits », remplacez les lignes d’exemple par vos articles. Comptez votre stock une première fois et notez-le dans « Stock initial ».'],
                ['Fixez un seuil d’alerte', 'Pour chaque produit, la quantité en dessous de laquelle il faut recommander (voir le calcul plus bas).'],
                ['Notez chaque mouvement', 'Une réception fournisseur ou un retour client = une « Entrée » ; une vente, une casse, une consommation interne = une « Sortie ».'],
                ['Regardez le statut', '« À commander » apparaît quand le stock atteint le seuil, « Rupture » quand il tombe à zéro. Filtrez la colonne Statut pour préparer votre commande fournisseur.'],
            ] as $i => [$t, $d])
                <li class="flex gap-4">
                    <span class="grid place-items-center w-9 h-9 shrink-0 rounded-full bg-[#ffcc00] text-zinc-900 font-bold">{{ $i + 1 }}</span>
                    <span><strong class="text-zinc-900">{{ $t }}.</strong> {{ $d }}</span>
                </li>
            @endforeach
        </ol>
        <p class="mb-5">Une règle simple évite la plupart des erreurs : ne modifiez jamais le stock à la main. Tout passe par un mouvement, et le stock en découle. Une fois par mois ou par trimestre, faites un <strong class="text-zinc-900">inventaire</strong> : comptez, comparez avec la colonne « Stock » et enregistrez l’écart comme une entrée ou une sortie « Correction inventaire ».</p>

        <h2 id="formules" class="{{ $h2 }}">Les formules Excel de la gestion de stock</h2>
        <p class="mb-3">Le fichier fonctionne avec quelques formules classiques (affichées ici dans un Excel en français ; la ligne 2 correspond au premier produit) :</p>
        <p class="mb-1"><strong class="text-zinc-900">Total des entrées d’un produit</strong> (même formule avec « Sortie » pour les sorties) :</p>
        <code class="{{ $code }}">=SOMME.SI.ENS(Mouvements!D:D;Mouvements!B:B;A2;Mouvements!C:C;"Entrée")</code>
        <p class="mb-1"><strong class="text-zinc-900">Stock actuel</strong> = stock initial + entrées − sorties :</p>
        <code class="{{ $code }}">=G2+H2-I2</code>
        <p class="mb-1"><strong class="text-zinc-900">Statut</strong> du produit :</p>
        <code class="{{ $code }}">=SI(J2&lt;=0;"Rupture";SI(J2&lt;=K2;"À commander";"OK"))</code>
        <p class="mb-1"><strong class="text-zinc-900">Valeur du stock</strong> au prix d’achat :</p>
        <code class="{{ $code }}">=J2*E2</code>
        <p class="mb-1"><strong class="text-zinc-900">Désignation</strong> retrouvée à partir de la référence, dans « Mouvements » :</p>
        <code class="{{ $code }}">=INDEX(Produits!B:B;EQUIV(B2;Produits!A:A;0))</code>
        <p class="mt-5 mb-5">Une mise en forme conditionnelle colore le statut (rouge pour « Rupture », orange pour « À commander »), et des listes déroulantes empêchent de taper une référence qui n’existe pas.</p>

        <h2 id="seuil" class="{{ $h2 }}">Calculer le seuil d’alerte et le stock de sécurité</h2>
        <p class="mb-3">Le seuil d’alerte, ou point de commande, est le niveau de stock auquel il faut passer commande pour ne pas tomber en rupture pendant le délai de livraison :</p>
        <div class="my-5 rounded-xl border-l-4 border-amber-300 bg-[#fff8db] px-5 py-4 text-[16px]">
            <strong class="text-zinc-900">Seuil d’alerte = consommation moyenne par jour × délai de livraison (jours) + stock de sécurité</strong>
        </div>
        <p class="mb-5">Exemple : vous vendez en moyenne 5 boîtes de vis par jour, le fournisseur livre en 7 jours et vous gardez 15 boîtes de sécurité. Seuil = 5 × 7 + 15 = <strong class="text-zinc-900">50</strong>. Le stock de sécurité couvre les jours de forte vente et les retards de livraison : plus le fournisseur est irrégulier, plus il doit être élevé. Pour aller plus loin, notre <a href="{{ route('blog.index') }}" class="{{ $a }}">guide de la gestion de stock</a> détaille les méthodes et les calculs.</p>

        <h2 id="valeur" class="{{ $h2 }}">Valoriser son stock dans Excel</h2>
        <p class="mb-5">La colonne « Valeur du stock » multiplie le stock actuel par le prix d’achat HT du produit. C’est le chiffre à connaître pour votre bilan ou votre assurance. Si vos prix d’achat changent souvent, la méthode du <strong class="text-zinc-900">coût unitaire moyen pondéré</strong> (CUMP) est plus juste : à chaque réception, nouveau prix moyen = (valeur du stock + valeur de l’entrée) ÷ (quantité en stock + quantité entrée). Dans le modèle, mettez simplement à jour le prix d’achat avec ce prix moyen.</p>

        <h2 id="limites" class="{{ $h2 }}">Les limites d’un fichier Excel pour gérer un stock</h2>
        <ul class="mb-6 space-y-3">
            @foreach ([
                ['Une seule personne à la fois', 'Deux vendeurs qui modifient deux copies du fichier, et le stock n’est plus juste. Un fichier partagé en ligne aide, mais les erreurs de saisie restent.'],
                ['Rien n’est relié aux ventes', 'Chaque facture, chaque bon de livraison doit être recopié en sortie de stock. Un oubli, et le stock affiché est faux.'],
                ['Pas de scan des codes barres', 'Tout se tape à la main, référence par référence.'],
                ['Pas d’alerte qui vient à vous', 'Le statut « À commander » ne s’affiche que si vous ouvrez le fichier au bon moment.'],
            ] as [$t, $d])
                <li class="flex gap-3"><span class="mt-2 w-2 h-2 shrink-0 rounded-full bg-[#ffcc00]"></span><span><strong class="text-zinc-900">{{ $t }}.</strong> {{ $d }}</span></li>
            @endforeach
        </ul>
        <p class="mb-5">Excel reste un très bon point de départ pour une petite boutique avec quelques dizaines de références. Au-delà, ou dès que plusieurs personnes vendent, un logiciel évite les ressaisies.</p>

        <h2 id="logiciel" class="{{ $h2 }}">Passer d’Excel à un logiciel de gestion de stock gratuit</h2>
        <p class="mb-5"><a href="{{ url('/') }}" class="{{ $a }}">Stokito</a> reprend le même principe que ce fichier, sans les ressaisies : le stock baisse tout seul à chaque bon de livraison, facture ou vente en <a href="{{ route('seo.caisse') }}" class="{{ $a }}">caisse</a>, il monte à chaque réception de <a href="{{ route('seo.bon-de-commande') }}" class="{{ $a }}">bon de commande</a>, et les produits sous le seuil d’alerte apparaissent sur le tableau de bord.</p>
        <p class="mb-5">Vous n’avez rien à ressaisir : <strong class="text-zinc-900">ce modèle Excel s’importe tel quel</strong> dans Stokito (Catalogue › Produits › Importer Excel). Références, désignations, catégories, unités, prix, stock et seuils d’alerte sont repris. Vos codes barres s’impriment ensuite avec le <a href="{{ route('seo.etiquette-code-barre') }}" class="{{ $a }}">créateur d’étiquettes code barre</a>.</p>
        <div class="my-8 rounded-2xl bg-zinc-900 text-white p-6 sm:p-8">
            <p class="text-xl font-bold">Votre stock, vos devis et vos factures au même endroit</p>
            <p class="mt-2 text-zinc-300">Stokito est un <a href="{{ route('seo.logiciel-gratuit') }}" class="underline decoration-amber-400 decoration-2 underline-offset-4">logiciel de gestion de stock gratuit</a>, sans carte bancaire. Importez ce fichier Excel et votre catalogue est prêt en une minute.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="inline-flex items-center h-11 px-5 rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold">Créer un compte gratuit</a>
                <a href="{{ route('demo.login') }}" class="inline-flex items-center h-11 px-5 rounded-lg border border-white/25 font-semibold hover:bg-white/10">Tester la démo</a>
            </div>
        </div>

        <h2 id="faq" class="{{ $h2 }}">Questions fréquentes sur la gestion de stock Excel</h2>
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
@endcomponent
