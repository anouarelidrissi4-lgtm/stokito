{{-- SEO page (FR), 2026-11-25 — cluster "bon de commande" (5 400), "exemple (de) bon de commande" (1 600 + 1 300 + 1 000),
     "modèle (de) bon de commande" (720 + 480 + 390), "bon de commande pdf / vierge" (170 + 140). Semrush FR, 2026-11-25.
     Free blank PDF + filled example made with scratchpad bdc/make_bdc.py. Legal points kept general (no invented obligations). --}}
@php
    $url = route('seo.bon-de-commande');
    $pdf = '/docs/modele-bon-de-commande-vierge.pdf';
    $pdfEx = '/docs/exemple-bon-de-commande.pdf';
    $faqs = [
        ['Où télécharger un modèle de bon de commande gratuit ?', 'Sur cette page : le modèle de bon de commande vierge est un PDF au format A4, gratuit et sans inscription. Imprimez-le et remplissez-le à la main, ou créez vos bons de commande directement dans Stokito pour qu’ils soient numérotés et calculés automatiquement.'],
        ['Quelles informations mettre sur un bon de commande ?', 'Le numéro et la date du bon de commande, l’identité de l’acheteur et du fournisseur, la référence, la désignation, la quantité et le prix unitaire HT de chaque article, la TVA, les totaux HT et TTC, la date et l’adresse de livraison, les conditions de paiement, puis les signatures.'],
        ['Un bon de commande a-t-il une valeur juridique ?', 'Oui, une fois accepté. Selon l’article 1583 du Code civil, la vente est conclue dès que l’acheteur et le vendeur sont d’accord sur la chose et sur le prix. Un bon de commande signé par l’acheteur et accepté par le fournisseur engage donc les deux parties.'],
        ['Quelle différence entre un bon de commande et un devis ?', 'Le devis est rédigé par le vendeur : il propose un prix. Le bon de commande est rédigé par l’acheteur (ou signé par lui) : il confirme ce qu’il commande. Souvent, le bon de commande reprend les lignes d’un devis accepté.'],
        ['Le bon de commande remplace-t-il la facture ?', 'Non. La facture reste obligatoire entre professionnels et doit porter des mentions précises. Le bon de commande sert à passer et à suivre la commande ; la facture vient après la livraison ou la prestation.'],
        ['Peut-on faire un bon de commande sur Excel ?', 'Oui, mais il faut recalculer les totaux, numéroter les bons à la main et ressaisir les lignes dans la facture et le stock. Un logiciel comme Stokito numérote, calcule la TVA et, à la réception, fait monter le stock automatiquement.'],
    ];
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Modèle de bon de commande', 'item' => $url],
            ],
        ],
        [
            '@type' => 'DigitalDocument',
            'name' => 'Modèle de bon de commande vierge (PDF)',
            'url' => url($pdf),
            'encodingFormat' => 'application/pdf',
            'inLanguage' => 'fr',
            'isAccessibleForFree' => true,
            'publisher' => ['@type' => 'Organization', 'name' => 'Stokito', 'url' => url('/')],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs),
        ],
    ]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $a = 'font-semibold text-zinc-900 underline decoration-amber-400 decoration-2 underline-offset-4';
@endphp
@component('layouts.public', [
    'title' => 'Modèle de bon de commande gratuit : exemple et PDF vierge | Stokito',
    'description' => 'Modèle de bon de commande gratuit en PDF vierge, exemple de bon de commande rempli, informations à indiquer et différence avec le devis et la facture.',
    'canonical' => $url,
    'jsonLd' => $jsonLd,
    'ogImage' => url('/images/bon-de-commande/exemple-bon-de-commande.webp'),
])
<div>
    {{-- HERO --}}
    <section class="bg-zinc-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14 grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <nav aria-label="Fil d’Ariane" class="text-xs text-zinc-400">
                    <a href="{{ url('/') }}" class="hover:text-white">Accueil</a> <span aria-hidden="true">/</span> <span class="text-zinc-300">Modèle de bon de commande</span>
                </nav>
                <p class="mt-8 text-sm font-semibold text-amber-300">Gratuit · PDF vierge · exemple rempli</p>
                <h1 class="mt-3 text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.05]">Modèle de bon de commande gratuit <span class="text-amber-300">(exemple + PDF vierge)</span></h1>
                <p class="mt-5 text-lg text-zinc-300 leading-relaxed">Téléchargez un modèle de bon de commande vierge au format PDF, voyez un exemple de bon de commande rempli, et vérifiez en deux minutes qu’il ne manque rien sur les vôtres.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ $pdf }}" download class="inline-flex items-center gap-2 h-12 px-6 rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold hover:bg-[#ffd633]">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12m0 0-5-5m5 5 5-5M5 20h14"/></svg>
                        Télécharger le PDF vierge
                    </a>
                    <a href="#exemple" class="inline-flex items-center h-12 px-6 rounded-lg border border-white/25 font-semibold hover:bg-white/10">Voir l’exemple</a>
                </div>
                <p class="mt-4 text-xs text-zinc-400">A4 · sans inscription · à imprimer ou à remplir dans un lecteur PDF</p>
            </div>
            <a href="{{ $pdf }}" download class="block mx-auto max-w-sm rotate-[-1.5deg] rounded-xl bg-white p-2 shadow-2xl shadow-black/40 hover:rotate-0 transition">
                <img src="/images/bon-de-commande/modele-bon-de-commande-vierge.webp" width="496" height="702" alt="Modèle de bon de commande vierge au format PDF : acheteur, fournisseur, tableau des articles, totaux HT, TVA, TTC et signatures" class="w-full rounded-md">
            </a>
        </div>
    </section>

    {{-- SOMMAIRE --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-12">
        <div class="bg-white border border-zinc-200 shadow-sm p-5 rounded-xl">
            <p class="text-xs font-semibold text-zinc-500">Sommaire</p>
            <ol class="mt-2 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-[15px] list-decimal ps-5">
                <li><a href="#definition" class="{{ $a }}">Qu’est-ce qu’un bon de commande ?</a></li>
                <li><a href="#modele" class="{{ $a }}">Modèle de bon de commande PDF</a></li>
                <li><a href="#mentions" class="{{ $a }}">Les informations à indiquer</a></li>
                <li><a href="#exemple" class="{{ $a }}">Exemple de bon de commande</a></li>
                <li><a href="#differences" class="{{ $a }}">Devis, bon de commande, BL, facture</a></li>
                <li><a href="#logiciel" class="{{ $a }}">Faire ses bons de commande avec Stokito</a></li>
                <li><a href="#faq" class="{{ $a }}">Questions fréquentes</a></li>
            </ol>
        </div>
    </div>

    <article class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-[17px] leading-[1.85] text-zinc-700">

        <h2 id="definition" class="mt-4 mb-5 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24">Qu’est-ce qu’un bon de commande ?</h2>
        <p class="mb-5">Un <strong class="text-zinc-900">bon de commande</strong> est le document par lequel un acheteur commande des marchandises ou une prestation à un fournisseur. Il précise ce qui est commandé, en quelle quantité, à quel prix et pour quand. Le fournisseur l’accepte (souvent en le signant ou en le confirmant par écrit), puis livre.</p>
        <p class="mb-5">On l’utilise dans les deux sens : un commerçant envoie un bon de commande à son fournisseur pour se réapprovisionner, et un vendeur fait signer un bon de commande à son client pour confirmer une vente. Dans les deux cas, il évite les malentendus sur les quantités et les prix, et sert de référence à la livraison puis à la facture.</p>

        <h2 id="modele" class="mt-16 mb-5 pt-4 border-t border-zinc-200 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24">Modèle de bon de commande PDF gratuit</h2>
        <p class="mb-5">Notre <strong class="text-zinc-900">modèle de bon de commande</strong> tient sur une page A4. Il contient tout ce qu’on attend d’un bon de commande professionnel : l’acheteur et le fournisseur, le numéro et la date, dix lignes d’articles avec la TVA, les totaux HT et TTC, la livraison, le paiement et deux cadres de signature.</p>
        <div class="my-8 grid sm:grid-cols-2 gap-4 not-prose">
            <a href="{{ $pdf }}" download class="group flex items-center gap-4 rounded-xl border border-zinc-200 bg-white p-4 hover:border-zinc-900 hover:shadow-lg transition">
                <span class="grid place-items-center w-12 h-12 shrink-0 rounded-lg bg-zinc-900 text-amber-300 text-sm font-bold">PDF</span>
                <span class="flex-1"><span class="block font-semibold text-zinc-900">Bon de commande vierge</span><span class="block text-sm text-zinc-500">A4 · à imprimer ou à remplir</span></span>
                <svg class="w-5 h-5 text-zinc-400 group-hover:text-zinc-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12m0 0-5-5m5 5 5-5M5 20h14"/></svg>
            </a>
            <a href="{{ $pdfEx }}" download class="group flex items-center gap-4 rounded-xl border border-zinc-200 bg-white p-4 hover:border-zinc-900 hover:shadow-lg transition">
                <span class="grid place-items-center w-12 h-12 shrink-0 rounded-lg bg-[#ffcc00] text-zinc-900 text-sm font-bold">PDF</span>
                <span class="flex-1"><span class="block font-semibold text-zinc-900">Exemple de bon de commande rempli</span><span class="block text-sm text-zinc-500">avec des données fictives</span></span>
                <svg class="w-5 h-5 text-zinc-400 group-hover:text-zinc-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12m0 0-5-5m5 5 5-5M5 20h14"/></svg>
            </a>
        </div>
        <p class="mb-5">Le bon de commande vierge convient pour quelques commandes par mois. Au-delà, le remplir à la main devient vite une source d’erreurs : numéros en double, totaux mal recalculés, lignes à recopier ensuite dans la facture et dans le stock.</p>

        <h2 id="mentions" class="mt-16 mb-5 pt-4 border-t border-zinc-200 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24">Les informations à indiquer sur un bon de commande</h2>
        <p class="mb-5">Entre professionnels, la loi n’impose pas de liste de mentions pour un bon de commande, contrairement à la facture. En pratique, un bon de commande complet comporte :</p>
        <ul class="mb-6 list-disc ps-6 space-y-2">
            <li><strong class="text-zinc-900">un numéro et une date</strong>, pour le retrouver et le rappeler sur le bon de livraison et la facture ;</li>
            <li><strong class="text-zinc-900">l’acheteur et le fournisseur</strong> : nom ou raison sociale, adresse, identifiant (SIRET en France, ICE au Maroc), contact ;</li>
            <li><strong class="text-zinc-900">chaque article</strong> : référence, désignation, quantité, unité, prix unitaire HT et taux de TVA ;</li>
            <li><strong class="text-zinc-900">les totaux</strong> : total HT, montant de TVA et total TTC ;</li>
            <li><strong class="text-zinc-900">la livraison</strong> : adresse et date souhaitée ;</li>
            <li><strong class="text-zinc-900">le paiement</strong> : délai et mode de règlement ;</li>
            <li><strong class="text-zinc-900">les signatures</strong> de l’acheteur et, pour l’accord, du fournisseur.</li>
        </ul>
        <p class="mb-5">Si vous vendez à des particuliers, surtout à domicile ou à distance, le Code de la consommation ajoute des obligations d’information (et parfois un formulaire de rétractation) : faites valider votre document par un professionnel du droit.</p>

        <h2 id="exemple" class="mt-16 mb-5 pt-4 border-t border-zinc-200 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24">Exemple de bon de commande rempli</h2>
        <p class="mb-5">Voici un <strong class="text-zinc-900">exemple de bon de commande</strong> envoyé par un négoce de matériaux à son fournisseur d’acier (entreprises et numéros fictifs). Quatre articles sont commandés, la TVA est à 20 % et la livraison est demandée sous une semaine.</p>
        <figure class="my-8">
            <img src="/images/bon-de-commande/exemple-bon-de-commande.webp" width="909" height="1287" loading="lazy" alt="Exemple de bon de commande rempli : Démo Matériaux SAS commande quatre articles en acier à Aciers du Rhône SARL, total HT 1 338 €, TVA 267,60 €, total TTC 1 605,60 €" class="w-full rounded-xl border border-zinc-200 shadow-lg">
            <figcaption class="mt-2 text-sm text-zinc-500">Exemple de bon de commande (données fictives). <a href="{{ $pdfEx }}" download class="{{ $a }}">Télécharger cet exemple en PDF</a>.</figcaption>
        </figure>
        <p class="mb-5">Dans cet exemple, chaque total de ligne est égal à la quantité multipliée par le prix unitaire HT (100 × 5,80 € = 580 €), le total HT additionne les lignes (1 338 €), et la TVA à 20 % donne 267,60 €, soit 1 605,60 € TTC.</p>

        <h2 id="differences" class="mt-16 mb-5 pt-4 border-t border-zinc-200 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24">Bon de commande, devis, bon de livraison, facture : les différences</h2>
        <div class="my-6 overflow-x-auto rounded-xl border border-zinc-200 bg-white">
            <table class="w-full text-[15px]">
                <thead class="bg-zinc-50 text-zinc-500 text-sm"><tr><th class="text-start font-semibold px-4 py-3">Document</th><th class="text-start font-semibold px-4 py-3">Qui l’établit</th><th class="text-start font-semibold px-4 py-3">À quoi il sert</th></tr></thead>
                <tbody class="divide-y divide-zinc-100">
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900">Devis</td><td class="px-4 py-3">Le vendeur</td><td class="px-4 py-3">Proposer un prix avant la vente</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900">Bon de commande</td><td class="px-4 py-3">L’acheteur</td><td class="px-4 py-3">Confirmer ce qui est commandé, à quel prix</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900">Bon de livraison</td><td class="px-4 py-3">Le vendeur</td><td class="px-4 py-3">Accompagner la marchandise, prouver la livraison</td></tr>
                    <tr><td class="px-4 py-3 font-semibold text-zinc-900">Facture</td><td class="px-4 py-3">Le vendeur</td><td class="px-4 py-3">Demander le paiement (mentions obligatoires)</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mb-5">Pour aller plus loin : <a href="{{ route('blog.show', 'difference-devis-facture') }}" class="{{ $a }}">la différence entre un devis et une facture</a> et <a href="{{ route('blog.show', 'transformer-devis-en-facture') }}" class="{{ $a }}">comment transformer un devis en facture</a>.</p>

        <h2 id="logiciel" class="mt-16 mb-5 pt-4 border-t border-zinc-200 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24">Faire ses bons de commande avec Stokito (gratuit)</h2>
        <p class="mb-5">Dans <a href="{{ url('/') }}" class="{{ $a }}">Stokito</a>, un bon de commande fournisseur se prépare en une minute, et ce qui suit se fait tout seul :</p>
        <ul class="mb-6 list-disc ps-6 space-y-2">
            <li>le <strong class="text-zinc-900">numéro</strong> est donné automatiquement, sans doublon ;</li>
            <li>vous choisissez le fournisseur et les produits de votre catalogue : prix d’achat, TVA et totaux se calculent ;</li>
            <li>vous l’envoyez au fournisseur en PDF par email ou par WhatsApp ;</li>
            <li>à la réception, le <strong class="text-zinc-900">stock monte</strong> avec les bonnes quantités et le prix d’achat est enregistré pour vos marges ;</li>
            <li>avec le <a href="{{ route('seo.scanner-facture') }}" class="{{ $a }}">scanner de factures fournisseurs</a>, une photo de la facture remplit le bon de commande à votre place.</li>
        </ul>
        <div class="my-8 rounded-2xl bg-zinc-900 text-white p-6 sm:p-8">
            <p class="text-xl font-bold">Arrêtez de remplir vos bons de commande à la main</p>
            <p class="mt-2 text-zinc-300">Stokito est gratuit, sans carte bancaire. Bons de commande, devis, bons de livraison, factures et stock au même endroit.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="inline-flex items-center h-11 px-5 rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold">Créer un compte gratuit</a>
                <a href="{{ route('demo.login') }}" class="inline-flex items-center h-11 px-5 rounded-lg border border-white/25 font-semibold hover:bg-white/10">Tester la démo</a>
            </div>
        </div>

        <h2 id="faq" class="mt-16 mb-5 pt-4 border-t border-zinc-200 text-2xl sm:text-3xl font-bold tracking-tight leading-tight text-zinc-900 scroll-mt-24">Questions fréquentes sur le bon de commande</h2>
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
