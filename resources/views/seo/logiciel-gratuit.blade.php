{{-- SEO pillar page (FR) — target cluster "logiciel (de) gestion de stock (gratuit)", Semrush FR 2026-11-23. Facts only about Stokito; no competitor names, no invented prices or ratings. --}}
@php
    $url = route('seo.logiciel-gratuit');
    $faqs = [
        ['Existe-t-il un logiciel de gestion de stock vraiment gratuit ?', 'Oui. Certains logiciels sont gratuits sans limite de durée, d’autres sont des essais de quelques jours ou des versions limitées en nombre de produits, d’utilisateurs ou de documents. Lisez toujours ce qui est inclus avant d’y mettre vos données. Stokito est actuellement gratuit, sans carte bancaire.'],
        ['Quels sont les logiciels de gestion de stock les plus utilisés ?', 'On trouve trois familles : les tableurs comme Excel ou Google Sheets, les logiciels de gestion commerciale en ligne qui relient stock, devis et factures, et les ERP plus complets pour les grandes structures. Pour une PME qui vend tous les jours, un logiciel en ligne relié aux factures est souvent le bon compromis.'],
        ['Combien coûte un logiciel de gestion de stock ?', 'Cela dépend du modèle : gratuit, abonnement mensuel (souvent par utilisateur), ou licence achetée une fois avec des frais de mise à jour. Comparez le coût total sur un an pour votre nombre d’utilisateurs, et vérifiez ce qui est facturé en plus : utilisateurs, dépôts, modules, support.'],
        ['Peut-on gérer son stock avec Excel ?', 'Oui pour démarrer avec peu de références et un seul utilisateur. Dès que plusieurs personnes vendent et livrent chaque jour, la mise à jour à la main crée des écarts. Notre guide sur la gestion de stock compare les deux en détail.'],
        ['Mes données restent-elles à moi avec un logiciel gratuit ?', 'Elles doivent l’être. Vérifiez que vous pouvez exporter vos produits et vos documents, et lisez la politique de confidentialité. Chez Stokito, vos données ne sont ni vendues ni utilisées pour de la publicité, et la liste des produits s’exporte en Excel.'],
        ['Stokito fonctionne-t-il hors du Maroc ?', 'Oui. À l’inscription, vous choisissez votre pays : la devise, le taux de TVA et les identifiants de l’entreprise (SIRET et RCS en France, NINEA au Sénégal, ICE au Maroc…) s’adaptent. L’interface est en français, en arabe et en anglais.'],
    ];
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
        [
            '@type' => 'SoftwareApplication',
            'name' => 'Stokito',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'url' => url('/'),
            'description' => 'Logiciel de gestion de stock en ligne relié aux devis, bons de livraison et factures.',
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Logiciel de gestion de stock gratuit', 'item' => $url],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs),
        ],
    ]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $gs = route('blog.show', 'gestion-de-stock');
    $ice = route('blog.show', 'ice-if-rc-facturation-electronique-dgi-2026-maroc');
@endphp
@component('layouts.public', [
    'title' => 'Logiciel de gestion de stock gratuit : ce qu’il faut vérifier (2026) | Stokito',
    'description' => 'Logiciel de gestion de stock gratuit : ce qu’il doit faire, gratuit ou payant, combien ça coûte et 7 critères pour bien choisir. Essayez Stokito sans carte bancaire.',
    'canonical' => $url,
    'jsonLd' => $jsonLd,
])
<div>
    {{-- HERO --}}
    <section class="bg-[#111] text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14 grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <nav aria-label="Fil d’Ariane" class="ind-mono text-xs text-[#9a9a9a]">
                    <a href="{{ url('/') }}" class="hover:text-[#ffcc00]">Accueil</a> <span aria-hidden="true">/</span> <span class="text-[#d6d6d6]">Logiciel de gestion de stock gratuit</span>
                </nav>
                <p class="mt-8 ind-mono text-xs text-[#ffcc00]">GUIDE · MIS À JOUR {{ \Illuminate\Support\Str::upper(now()->translatedFormat('F Y')) }}</p>
                <h1 class="mt-3 ind-h text-5xl sm:text-6xl uppercase leading-[0.92]">Logiciel de gestion de stock <span class="text-[#ffcc00]">gratuit</span></h1>
                <p class="mt-5 text-lg text-[#cfcfcf] leading-relaxed">Un logiciel de gestion de stock gratuit peut suffire à une PME, à condition de vérifier ce qui est vraiment inclus. Voici ce qu’il doit faire, la différence entre gratuit et payant, et les critères pour choisir.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] text-[#111]">Créer un compte gratuit</a>
                    <a href="{{ route('demo.login') }}" class="ind-btn bg-white text-[#111]">Tester la démo</a>
                </div>
                <p class="mt-4 ind-mono text-xs text-[#9a9a9a]">Sans carte bancaire · Sans installation · FR / AR / EN</p>
            </div>
            <figure class="border-2 border-[#ffcc00] bg-white shadow-[10px_10px_0_#ffcc00]">
                <div class="h-8 flex items-center px-3 border-b-2 border-[#111] bg-[#f4f2ec] ind-mono text-[11px] text-[#555]">stokito.com/products</div>
                <img src="/images/screens/screen-products.webp?v=20261124" width="1400" height="871" alt="Logiciel de gestion de stock gratuit Stokito : liste des produits avec prix d’achat, prix de vente et quantités en stock" class="w-full">
                <figcaption class="px-3 py-2 border-t-2 border-[#111] text-xs text-[#444]">Liste des produits dans Stokito (démo, données fictives) : stock coloré selon le seuil d’alerte.</figcaption>
            </figure>
        </div>
        <div class="ind-hazard h-3"></div>
    </section>

    {{-- SOMMAIRE --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-12">
        <div class="bg-white border-2 border-[#111] shadow-[6px_6px_0_#111] p-5">
            <p class="ind-mono text-xs text-[#666]">SOMMAIRE</p>
            <ol class="mt-2 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-[15px] list-decimal ps-5">
                <li><a href="#role" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">Ce que fait un logiciel de gestion de stock</a></li>
                <li><a href="#gratuit-payant" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">Gratuit ou payant : ce qui change</a></li>
                <li><a href="#prix" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">Combien coûte un logiciel de gestion de stock</a></li>
                <li><a href="#criteres" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">7 critères pour choisir</a></li>
                <li><a href="#stokito" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">Ce que Stokito inclut gratuitement</a></li>
                <li><a href="#faq" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">Questions fréquentes</a></li>
            </ol>
        </div>
    </div>

    <article class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-[17px] leading-[1.85] text-[#333]
        [&_h2]:font-['Barlow_Condensed','Tajawal',sans-serif] [&_h2]:font-extrabold [&_h2]:text-[#111] [&_h2]:text-3xl sm:[&_h2]:text-4xl [&_h2]:uppercase [&_h2]:leading-none [&_h2]:mt-16 [&_h2]:mb-5 [&_h2]:pt-4 [&_h2]:border-t-2 [&_h2]:border-[#111] [&_h2]:scroll-mt-24
        [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-[#111] [&_h3]:mt-8 [&_h3]:mb-2
        [&_p]:mb-5 [&_strong]:text-[#111]
        [&_ul]:list-disc [&_ul]:ps-6 [&_ul]:mb-6 [&_ul]:space-y-2
        [&_a]:font-semibold [&_a]:text-[#111] [&_a]:underline [&_a]:decoration-[#ffcc00] [&_a]:decoration-[3px] [&_a]:underline-offset-4">

        <h2 id="role">Ce que fait un logiciel de gestion de stock</h2>
        <p>Un <strong>logiciel de gestion de stock</strong> enregistre chaque entrée et chaque sortie de marchandise pour que vous connaissiez, à tout moment, la quantité disponible et sa valeur. Les meilleurs relient le stock aux documents de vente et d’achat : un bon de livraison fait baisser le stock, une réception fournisseur le fait monter.</p>
        <p>Concrètement, un logiciel de gestion des stocks doit au minimum :</p>
        <ul>
            <li>tenir la <strong>liste des produits</strong> avec références, catégories, prix d’achat et de vente ;</li>
            <li>mettre le stock à jour <strong>automatiquement</strong> avec les ventes et les achats ;</li>
            <li>vous <strong>alerter</strong> quand un produit passe sous son seuil ;</li>
            <li>permettre un <strong>inventaire</strong> pour corriger les écarts entre le stock compté et le stock théorique.</li>
        </ul>
        <p>Pour les calculs (stock de sécurité, point de commande, rotation, CMUP), lisez notre <a href="{{ $gs }}">guide de la gestion de stock</a>.</p>

        <h2 id="gratuit-payant">Logiciel de gestion de stock gratuit ou payant : ce qui change</h2>
        <p>« Gratuit » ne veut pas dire la même chose partout. Avant d’importer vos produits, vérifiez dans quelle catégorie se trouve l’outil :</p>
        <div class="my-8 overflow-x-auto border-2 border-[#111] bg-white">
            <table class="w-full text-[15px]">
                <thead class="bg-[#111] text-[#ffcc00]">
                    <tr><th class="text-start px-4 py-3">Type d’offre</th><th class="text-start px-4 py-3">Ce que ça veut dire</th><th class="text-start px-4 py-3">À vérifier</th></tr>
                </thead>
                <tbody class="[&_td]:px-4 [&_td]:py-3 [&_td]:border-t [&_td]:border-[#ddd] [&_td]:align-top">
                    <tr><td class="font-semibold">Essai gratuit</td><td>Toutes les fonctions pendant une durée limitée, puis abonnement.</td><td>La durée, et ce que deviennent vos données à la fin.</td></tr>
                    <tr><td class="font-semibold">Version gratuite limitée</td><td>Gratuite sans limite de durée, mais plafonnée.</td><td>Nombre de produits, d’utilisateurs, de documents par mois, filigrane sur les PDF.</td></tr>
                    <tr><td class="font-semibold">Gratuit complet</td><td>Les fonctions principales sont gratuites.</td><td>Les conditions d’utilisation, l’export de vos données, le support.</td></tr>
                    <tr><td class="font-semibold">Logiciel payant</td><td>Abonnement mensuel ou licence.</td><td>Le coût total sur un an et ce qui est facturé en plus.</td></tr>
                </tbody>
            </table>
        </div>
        <p>Un logiciel pour gestion de stock gratuit convient très bien à une petite structure. Ce qui compte, c’est qu’il ne vous bloque pas le jour où vous grandissez : export possible, plusieurs utilisateurs, documents propres à envoyer à vos clients.</p>

        <h2 id="prix">Combien coûte un logiciel de gestion de stock ?</h2>
        <p>Le prix dépend du modèle choisi plus que de la marque. Les trois modèles les plus courants :</p>
        <ul>
            <li><strong>Gratuit</strong> : pas de coût direct, mais vérifiez les limites et les conditions.</li>
            <li><strong>Abonnement</strong> : un montant par mois, souvent par utilisateur ou par module. Multipliez par 12 et par votre nombre d’employés pour comparer.</li>
            <li><strong>Licence</strong> : un achat unique, avec souvent des frais de mise à jour, d’installation ou de maintenance.</li>
        </ul>
        <p>Ajoutez le coût caché : le temps passé à saisir. Un logiciel de gestion de stock qui fait vos devis, bons de livraison et factures évite de recopier la même information dans trois fichiers.</p>

        <h2 id="criteres">7 critères pour choisir un logiciel de gestion de stock</h2>
        <div class="my-6 grid sm:grid-cols-2 gap-4 not-prose">
            @foreach ([
                ['Stock relié aux ventes', 'Le stock doit bouger tout seul avec les bons de livraison et les factures, pas à la main.'],
                ['Plusieurs utilisateurs', 'Chaque employé a son compte, et vous choisissez ce qu’il voit (prix d’achat, marges).'],
                ['Devis, BL et factures', 'Des documents PDF propres, avec vos mentions obligatoires et votre logo.'],
                ['Alertes et inventaire', 'Un seuil par produit et un écran d’inventaire qui montre les écarts avant de corriger.'],
                ['Import et export Excel', 'Pour démarrer vite avec votre fichier actuel, et pour pouvoir repartir avec vos données.'],
                ['Téléphone et ordinateur', 'Un outil en ligne qui s’ouvre dans le navigateur, sans installation.'],
                ['Votre pays et votre langue', 'Devise, TVA et identifiants de l’entreprise de votre pays ; interface dans la langue de votre équipe.'],
            ] as $i => [$t, $d])
                <div class="bg-white border-2 border-[#111] shadow-[5px_5px_0_#111] p-4 {{ $loop->last ? 'sm:col-span-2' : '' }}">
                    <div class="ind-mono text-[11px] text-[#666]">CRITÈRE {{ sprintf('%02d', $i + 1) }}</div>
                    <div class="mt-1 font-bold text-[#111]">{{ $t }}</div>
                    <div class="text-[15px] leading-relaxed text-[#444]">{{ $d }}</div>
                </div>
            @endforeach
        </div>
        <p>Un dernier point : la <strong>facturation électronique</strong>. En France comme au Maroc, elle devient obligatoire par étapes, avec des plateformes et des formats imposés. Demandez à l’éditeur où il en est. Pour le Maroc, voir notre article sur <a href="{{ $ice }}">l’ICE, l’IF, le RC et la facturation électronique DGI</a>.</p>

        <h2 id="stokito">Stokito : ce qui est inclus gratuitement</h2>
        <p>Stokito est un logiciel de gestion de stock en ligne pensé pour les commerces, grossistes et sociétés de négoce. L’inscription et l’utilisation sont <strong>actuellement gratuites</strong>, sans carte bancaire. Si des offres payantes sont créées plus tard, elles seront annoncées à l’avance (voir nos <a href="{{ route('legal.terms') }}">conditions d’utilisation</a>).</p>
        <div class="my-6 bg-white border-2 border-[#111]">
            @foreach ([
                ['Catalogue', 'Produits, catégories, marques, code-barres, import et export Excel, seuil d’alerte par produit.'],
                ['Ventes', 'Devis, bons de livraison, factures, avoirs et règlements clients. Le stock baisse quand un bon de livraison passe en « Livré ».'],
                ['Achats', 'Commandes fournisseurs (le stock monte à la réception), règlements et chèques fournisseurs.'],
                ['Inventaire', 'Saisie du stock compté, écarts en quantité et en valeur, puis correction en un clic.'],
                ['Suivi', 'Tableau de bord : chiffre d’affaires du mois, encaissements, impayés, produits en stock bas ; relance des clients sur WhatsApp.'],
                ['Équipe', 'Plusieurs utilisateurs avec des droits : par exemple cacher les prix d’achat et les marges aux vendeurs.'],
                ['International', 'Pays choisi à l’inscription : devise, TVA et identifiants adaptés. Interface en français, arabe et anglais.'],
            ] as [$t, $d])
                <div class="grid sm:grid-cols-[160px_1fr] gap-1 sm:gap-4 px-5 py-4 {{ $loop->last ? '' : 'border-b-2 border-[#111]' }}">
                    <div class="ind-h text-xl uppercase text-[#111]">{{ $t }}</div>
                    <div class="text-[15px] leading-relaxed">{{ $d }}</div>
                </div>
            @endforeach
        </div>
        <p>Ce que Stokito ne fait pas encore : ce n’est pas une plateforme de facturation électronique agréée, et la gestion de plusieurs dépôts n’est pas disponible. Les factures sont produites en PDF.</p>
        <figure class="my-8 border-2 border-[#111] bg-white shadow-[8px_8px_0_#111]">
            <img src="/images/screens/screen-inventaire.webp?v=20261124" width="1400" height="871" loading="lazy" alt="Écran d’inventaire du logiciel de gestion de stock Stokito : stock système, quantité comptée et écart par produit" class="w-full">
            <figcaption class="px-4 py-2 border-t-2 border-[#111] text-sm text-[#444]">L’inventaire dans Stokito : l’écart apparaît avant de corriger le stock (démo, données fictives).</figcaption>
        </figure>
        <p>Pour voir chaque écran pas à pas, téléchargez le <a href="/docs/guide-stokito-fr.pdf" target="_blank" rel="noopener">guide d’utilisation (PDF)</a>, ou ouvrez la <a href="{{ route('demo.login') }}">démo avec des données fictives</a>.</p>

        <div class="my-12 bg-[#111] text-white border-2 border-[#111] shadow-[8px_8px_0_#ffcc00]">
            <div class="ind-hazard h-2"></div>
            <div class="p-7">
                <p class="!mb-2 ind-h text-3xl uppercase leading-none text-white">Essayez Stokito gratuitement</p>
                <p class="!mb-5 text-[#cfcfcf]">Votre espace est prêt en une minute. Choisissez votre pays, ajoutez vos produits ou importez votre fichier Excel.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] !text-[#111] !no-underline">Créer un compte gratuit</a>
                    <a href="{{ route('demo.login') }}" class="ind-btn bg-white !text-[#111] !no-underline">Tester la démo</a>
                </div>
            </div>
        </div>

        <h2 id="faq">Questions fréquentes</h2>
        @foreach ($faqs as [$q, $a])
            <h3>{{ $q }}</h3>
            <p>{{ $a }}</p>
        @endforeach
        <p>À lire aussi : <a href="{{ $gs }}">Gestion de stock : méthodes et calculs</a> · <a href="{{ route('blog.index') }}">tous les guides du blog</a>.</p>
    </article>
</div>
@endcomponent
