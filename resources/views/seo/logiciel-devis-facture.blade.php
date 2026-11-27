{{-- SEO pillar page (FR) — target cluster "facture devis" / "logiciel devis facture (gratuit)", Semrush FR 2026-11-23.
     Legal facts: entreprendre.service-public.gouv.fr F23208, F31808, F31144 (checked 2026-11-23). Facts only about Stokito; no competitor names, no invented prices or ratings. --}}
@php
    $url = route('seo.logiciel-devis-facture');
    $diff = route('blog.show', 'difference-devis-facture');
    $sp = 'https://entreprendre.service-public.gouv.fr/vosdroits/';
    $faqs = [
        ['Quel est le meilleur logiciel de devis et facture gratuit ?', 'Il n’y a pas de réponse unique : le bon logiciel est celui qui transforme un devis en facture sans ressaisie, numérote vos factures automatiquement, affiche vos mentions obligatoires et vous laisse exporter vos données. Vérifiez aussi ce que l’éditeur prévoit pour la facture électronique obligatoire en 2026-2027.'],
        ['Un logiciel de devis et facture gratuit est-il légal ?', 'Oui, à condition que les factures produites contiennent les mentions obligatoires et soient numérotées dans une suite chronologique continue. À partir du 1er septembre 2027, les PME et micro-entreprises devront en plus émettre leurs factures entre entreprises sous forme électronique, via une plateforme agréée.'],
        ['Existe-t-il un logiciel de devis et facture pour Mac ?', 'Oui. Un logiciel en ligne comme Stokito s’ouvre dans le navigateur : il fonctionne de la même façon sur Mac, sur PC Windows, sur tablette et sur téléphone, sans rien installer.'],
        ['Peut-on transformer un devis en facture ?', 'Oui, c’est même le principal intérêt d’un logiciel. Dans Stokito, un devis accepté devient un bon de livraison en un clic, puis le bon de livraison devient une facture : les produits, quantités et prix sont repris sans ressaisie.'],
        ['Un devis est-il obligatoire ?', 'Pas toujours. Il est obligatoire pour certaines activités, par exemple les travaux de dépannage et de réparation dans le bâtiment, le déménagement ou les services à la personne à partir de 100 € TTC par mois. Dans les autres cas, il reste fortement conseillé : une fois signé, il engage les deux parties.'],
        ['Combien de temps faut-il conserver ses factures ?', 'Les factures émises et reçues se conservent 10 ans. Gardez-les dans un format que vous pouvez relire et exporter, même si vous changez de logiciel.'],
    ];
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
        [
            '@type' => 'SoftwareApplication',
            'name' => 'Stokito',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web, macOS, Windows, Android, iOS',
            'url' => url('/'),
            'description' => 'Logiciel de devis et factures en ligne, relié au stock : devis, bons de livraison, factures et avoirs en PDF.',
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Logiciel devis facture gratuit', 'item' => $url],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs),
        ],
    ]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
@component('layouts.public', [
    'title' => 'Logiciel devis facture gratuit : devis et factures en ligne (2026) | Stokito',
    'description' => 'Logiciel devis facture gratuit en ligne : transformez un devis en facture en 2 clics, mentions obligatoires 2026, facture électronique, Mac et PC. Sans carte bancaire.',
    'canonical' => $url,
    'jsonLd' => $jsonLd,
])
<div>
    {{-- HERO --}}
    <section class="bg-zinc-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14 grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <nav aria-label="Fil d’Ariane" class="ind-mono text-xs text-zinc-400">
                    <a href="{{ url('/') }}" class="hover:text-zinc-900">Accueil</a> <span aria-hidden="true">/</span> <span class="text-[#d6d6d6]">Logiciel devis facture gratuit</span>
                </nav>
                <p class="mt-8 ind-mono text-xs text-amber-300">GUIDE · MIS À JOUR {{ \Illuminate\Support\Str::upper(now()->translatedFormat('F Y')) }}</p>
                <h1 class="mt-3 ind-h text-5xl sm:text-6xl leading-[0.92]">Logiciel devis facture <span class="text-amber-300">gratuit</span></h1>
                <p class="mt-5 text-lg text-zinc-300 leading-relaxed">Faire un devis, le faire accepter, puis le transformer en facture sans tout recopier : c’est le travail d’un logiciel de devis et facture. Voici ce qu’il doit faire, les mentions obligatoires en 2026 et ce qui change avec la facture électronique.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] text-zinc-900">Créer un compte gratuit</a>
                    <a href="{{ route('demo.login') }}" class="ind-btn bg-white text-zinc-900">Tester la démo</a>
                </div>
                <p class="mt-4 ind-mono text-xs text-zinc-400">Sans carte bancaire · Mac, PC et mobile · FR / AR / EN</p>
            </div>
            <figure class="border border-amber-300 bg-white shadow-sm rounded-lg">
                <div class="h-8 flex items-center px-3 border-b border-zinc-200 bg-zinc-50 ind-mono text-[11px] text-zinc-500">stokito.com/devis/create</div>
                <img src="/images/screens/screen-devis_form.webp?v=20261125m" width="1400" height="871" alt="Logiciel devis facture gratuit Stokito : formulaire de devis avec client, produits, quantités, remise et total TTC" class="w-full">
                <figcaption class="px-3 py-2 border-t border-zinc-200 text-xs text-zinc-600">Création d’un devis dans Stokito (démo, données fictives) : une fois accepté, il devient bon de livraison puis facture.</figcaption>
            </figure>
        </div>
    </section>

    {{-- SOMMAIRE --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-12">
        <div class="bg-white border border-zinc-200 shadow-lg shadow-zinc-900/5 p-5 rounded-xl">
            <p class="ind-mono text-xs text-zinc-500">SOMMAIRE</p>
            <ol class="mt-2 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-[15px] list-decimal ps-5">
                <li><a href="#role" class="underline decoration-amber-400 decoration-2 underline-offset-4">Ce que fait un logiciel de devis et facture</a></li>
                <li><a href="#difference" class="underline decoration-amber-400 decoration-2 underline-offset-4">Devis ou facture : la différence</a></li>
                <li><a href="#mentions" class="underline decoration-amber-400 decoration-2 underline-offset-4">Mentions obligatoires en 2026</a></li>
                <li><a href="#facture-electronique" class="underline decoration-amber-400 decoration-2 underline-offset-4">Facture électronique 2026-2027</a></li>
                <li><a href="#criteres" class="underline decoration-amber-400 decoration-2 underline-offset-4">6 critères pour choisir</a></li>
                <li><a href="#stokito" class="underline decoration-amber-400 decoration-2 underline-offset-4">Du devis à la facture avec Stokito</a></li>
                <li><a href="#faq" class="underline decoration-amber-400 decoration-2 underline-offset-4">Questions fréquentes</a></li>
            </ol>
        </div>
    </div>

    <article class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-[17px] leading-[1.85] text-zinc-700 [&_h2]:font-['Inter','Tajawal',sans-serif] [&_h2]:font-bold ]:tracking-tight [&_h2]:text-zinc-900 [&_h2]:text-2xl sm:[&_h2]:text-3xl [&_h2]:leading-tight [&_h2]:mt-16 [&_h2]:mb-5 [&_h2]:pt-4 [&_h2]:border-t [&_h2]:border-zinc-200 [&_h2]:scroll-mt-24 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-zinc-900 [&_h3]:mt-8 [&_h3]:mb-2 [&_p]:mb-5 [&_strong]:text-zinc-900 [&_ul]:list-disc [&_ul]:ps-6 [&_ul]:mb-6 [&_ul]:space-y-2 [&_a]:font-semibold [&_a]:text-zinc-900 [&_a]:underline [&_a]:decoration-[#ffcc00] [&_a]:decoration-2 [&_a]:underline-offset-4">

        <h2 id="role">Ce que fait un logiciel de devis et facture</h2>
        <p>Un <strong>logiciel de devis et facture</strong> vous fait gagner du temps sur trois points : il crée des documents propres à votre nom, il numérote vos factures sans erreur, et il reprend le contenu d’un devis accepté pour faire la facture, sans tout ressaisir.</p>
        <p>Au minimum, un bon logiciel de facturation et devis doit :</p>
        <ul>
            <li>tenir votre <strong>liste de clients</strong> et de produits ou prestations, avec leurs prix ;</li>
            <li>créer un <strong>devis</strong> en PDF avec votre logo, vos coordonnées et vos identifiants ;</li>
            <li><strong>transformer le devis en facture</strong> une fois accepté, sans recopier les lignes ;</li>
            <li>numéroter les factures dans une <strong>suite chronologique continue</strong>, comme l’exige la loi ;</li>
            <li>suivre les <strong>paiements</strong> : facture payée, partiellement payée ou en retard.</li>
        </ul>
        <p>S’il est relié au stock, il fait une chose de plus : les quantités livrées sortent du stock automatiquement. C’est utile dès que vous vendez de la marchandise, pas seulement des heures de travail.</p>

        <h2 id="difference">Devis ou facture : la différence en 30 secondes</h2>
        <p>Le <strong>devis</strong> vient avant la vente : c’est une proposition de prix. La <strong>facture</strong> vient après : elle constate la vente et demande le paiement.</p>
        <div class="my-8 overflow-x-auto border border-zinc-200 bg-white rounded-lg">
            <table class="w-full text-[15px]">
                <thead class="bg-zinc-900 text-white">
                    <tr><th class="text-start px-4 py-3"></th><th class="text-start px-4 py-3">Devis</th><th class="text-start px-4 py-3">Facture</th></tr>
                </thead>
                <tbody class="[&_td]:px-4 [&_td]:py-3 [&_td]:border-t [&_td]:border-zinc-100 [&_td]:align-top">
                    <tr><td class="font-semibold">Quand</td><td>Avant la vente ou les travaux</td><td>À la livraison ou à la fin de la prestation</td></tr>
                    <tr><td class="font-semibold">Rôle</td><td>Proposer un prix et des conditions</td><td>Constater la vente et demander le paiement</td></tr>
                    <tr><td class="font-semibold">Obligatoire ?</td><td>Pour certaines activités seulement</td><td>Toujours entre professionnels ; pour un particulier, selon le cas</td></tr>
                    <tr><td class="font-semibold">Valeur</td><td>Engage les deux parties une fois signé</td><td>Pièce comptable, à conserver 10 ans</td></tr>
                    <tr><td class="font-semibold">Numérotation</td><td>Libre</td><td>Suite chronologique continue, sans trou</td></tr>
                </tbody>
            </table>
        </div>
        <p>Les détails, avec un exemple, sont dans notre article <a href="{{ $diff }}">différence entre devis et facture</a>.</p>

        <h2 id="mentions">Mentions obligatoires d’une facture en 2026</h2>
        <p>Une facture doit afficher un ensemble de mentions, que le client soit un particulier ou une entreprise. Selon <a href="{{ $sp }}F31808" target="_blank" rel="noopener">la fiche officielle Service-Public</a>, les principales sont :</p>
        <ul>
            <li>la <strong>date</strong> et un <strong>numéro unique</strong> dans une suite chronologique continue ;</li>
            <li>l’identité du vendeur : nom ou dénomination, adresse, <strong>SIREN</strong>, forme juridique et capital pour une société ;</li>
            <li>l’identité du client, et le numéro de bon de commande s’il en a émis un ;</li>
            <li>le numéro de <strong>TVA intracommunautaire</strong> (sauf si le total HT est de 150 € ou moins) ;</li>
            <li>la désignation, la quantité, le <strong>prix unitaire HT</strong> et le taux de TVA de chaque ligne ;</li>
            <li>les totaux <strong>HT et TTC</strong>, les remises, la date d’échéance du paiement ;</li>
            <li>le taux des <strong>pénalités de retard</strong> et, pour un client professionnel, l’<strong>indemnité forfaitaire de 40 €</strong> pour frais de recouvrement.</li>
        </ul>
        <p>Certaines situations demandent une mention en plus, par exemple « TVA non applicable, art. 293 B du CGI » pour une entreprise en franchise de TVA, ou « Autoliquidation ».</p>
        <h3>Les 4 nouvelles mentions de la réforme</h3>
        <p>Avec la facture électronique, quatre mentions s’ajoutent : le <strong>SIREN du client</strong> (s’il est une entreprise), l’<strong>adresse de livraison</strong> si elle est différente, la <strong>nature des opérations</strong> (biens, services ou les deux) et, le cas échéant, l’option pour le paiement de la TVA d’après les débits.</p>
        <p>Une mention oubliée ou fausse coûte <strong>15 € d’amende par erreur</strong>, dans la limite du quart du montant de la facture (<a href="{{ $sp }}F23208" target="_blank" rel="noopener">source</a>). Un logiciel qui remplit ces champs à votre place évite ces oublis.</p>

        <h2 id="facture-electronique">Facture électronique : ce qui change en 2026 et 2027</h2>
        <p>La facture électronique ne veut pas dire « un PDF envoyé par email ». Il s’agit d’une facture dans un format structuré, transmise par une <strong>plateforme agréée</strong> par l’administration. Le calendrier officiel :</p>
        <div class="my-6 grid sm:grid-cols-2 gap-4">
            <div class="bg-white border border-zinc-200 shadow-sm p-4 rounded-xl">
                <div class="ind-mono text-[11px] text-zinc-500">1ER SEPTEMBRE 2026</div>
                <div class="mt-1 font-bold text-zinc-900">Toutes les entreprises reçoivent</div>
                <div class="text-[15px] leading-relaxed text-zinc-600">Toute entreprise assujettie à la TVA en France doit pouvoir recevoir des factures électroniques. Les grandes entreprises et les ETI doivent aussi en émettre.</div>
            </div>
            <div class="bg-[#ffcc00] border border-zinc-200 shadow-sm p-4 rounded-xl">
                <div class="ind-mono text-[11px] text-zinc-700">1ER SEPTEMBRE 2027</div>
                <div class="mt-1 font-bold text-zinc-900">PME et micro-entreprises émettent</div>
                <div class="text-[15px] leading-relaxed text-zinc-700">L’obligation d’émettre des factures électroniques s’étend aux PME et aux micro-entreprises.</div>
            </div>
        </div>
        <p>Une facture électronique non émise alors qu’elle était due coûte 50 € par facture, dans la limite de 15 000 € par an. Avant de choisir un logiciel de devis et facture, demandez à l’éditeur dans quel format il produit vos factures et comment il compte se connecter à une plateforme agréée. Stokito produit déjà des <a href="{{ route('seo.factur-x') }}">factures Factur-X</a>. Le détail du calendrier, des plateformes et des sanctions est dans notre article <a href="{{ route('blog.show', 'facture-electronique-obligatoire-2026-2027') }}">facture électronique obligatoire 2026-2027</a>.</p>

        <h2 id="criteres">6 critères pour choisir un logiciel de devis et facture</h2>
        <div class="my-6 grid sm:grid-cols-2 gap-4">
            @foreach ([
                ['Devis → facture sans ressaisie', 'Le devis accepté devient facture en quelques clics, avec les mêmes lignes et les mêmes prix.'],
                ['Numérotation automatique', 'Des numéros qui se suivent, sans trou ni doublon, pour les devis, les factures et les avoirs.'],
                ['Mentions et logo sur le PDF', 'Votre logo, votre SIREN, vos conditions de paiement et vos pénalités de retard apparaissent sur chaque facture.'],
                ['Mac, PC et téléphone', 'Un logiciel en ligne s’ouvre dans le navigateur : rien à installer, même sur Mac.'],
                ['Suivi des paiements', 'Voir d’un coup d’œil les factures payées, en partie payées et en retard, et relancer le client.'],
                ['Facture électronique et export', 'Ce que prévoit l’éditeur pour 2026-2027, et la possibilité de repartir avec vos données.'],
            ] as $i => [$t, $d])
                <div class="bg-white border border-zinc-200 shadow-sm p-4 rounded-xl">
                    <div class="ind-mono text-[11px] text-zinc-500">CRITÈRE {{ sprintf('%02d', $i + 1) }}</div>
                    <div class="mt-1 font-bold text-zinc-900">{{ $t }}</div>
                    <div class="text-[15px] leading-relaxed text-zinc-600">{{ $d }}</div>
                </div>
            @endforeach
        </div>
        <p>Si vous vendez aussi de la marchandise, ajoutez un septième critère : le lien avec le stock. Voir notre page <a href="{{ route('seo.logiciel-gratuit') }}">logiciel de gestion de stock gratuit</a>.</p>

        <h2 id="stokito">Du devis à la facture avec Stokito</h2>
        <p>Stokito est un logiciel en ligne de devis, factures et gestion de stock pour les commerces, grossistes et PME. Il est <strong>actuellement gratuit</strong>, sans carte bancaire. Si des offres payantes sont créées plus tard, elles seront annoncées à l’avance (voir nos <a href="{{ route('legal.terms') }}">conditions d’utilisation</a>).</p>
        <div class="my-6 bg-white border border-zinc-200 rounded-lg">
            @foreach ([
                ['1. Devis', 'Choisissez le client, ajoutez vos produits ou prestations, une remise par ligne ou sur le total. Le devis passe de « Brouillon » à « Envoyé » puis « Accepté ».'],
                ['2. Bon de livraison', 'Un clic sur « Convertir en BL » reprend toutes les lignes du devis accepté. Le stock baisse quand le bon passe en « Livré ».'],
                ['3. Facture', 'Un clic sur « Facturer » crée la facture à partir du bon de livraison, avec un numéro qui suit le précédent et une date d’échéance.'],
                ['4. Paiement', 'Enregistrez les règlements : la facture passe en payée, partiellement payée ou en retard. Relance possible sur WhatsApp.'],
            ] as [$t, $d])
                <div class="grid sm:grid-cols-[180px_1fr] gap-1 sm:gap-4 px-5 py-4 {{ $loop->last ? '' : 'border-b-2 border-zinc-200' }}">
                    <div class="ind-h text-xl text-zinc-900">{{ $t }}</div>
                    <div class="text-[15px] leading-relaxed">{{ $d }}</div>
                </div>
            @endforeach
        </div>
        <p>Votre logo, votre adresse, votre SIREN et vos conditions de paiement se règlent une fois dans « Ma Société » et apparaissent sur chaque PDF. Une même facture peut mélanger <strong>plusieurs taux de TVA</strong> (20 %, 10 %, 5,5 %…) avec une ligne de TVA par taux. Pour une entreprise française, Stokito ajoute aussi les mentions que vous activez : « TVA non applicable, art. 293 B du CGI », pénalités de retard, indemnité forfaitaire de 40 €, escompte, adresse de livraison et nature des opérations. Les avoirs, les clients, le catalogue et l’import Excel sont inclus.</p>
        <figure class="my-8 border border-zinc-200 bg-white shadow-xl shadow-zinc-900/10 rounded-xl">
            <img src="/images/screens/screen-factures.webp?v=20261125m" width="1400" height="871" loading="lazy" alt="Liste des factures dans le logiciel devis facture Stokito : numéro, client, montant TTC, montant payé et statut" class="w-full">
            <figcaption class="px-4 py-2 border-t border-zinc-200 text-sm text-zinc-600">La liste des factures dans Stokito : le statut payé, partiel ou en retard se met à jour avec les règlements (démo, données fictives).</figcaption>
        </figure>
        <h3>Ce que Stokito ne fait pas encore</h3>
        <ul>
            <li>Ce n’est <strong>pas une plateforme de facturation électronique agréée</strong> : pour une entreprise établie en France, les factures sont produites en <a href="{{ route('seo.factur-x') }}">PDF Factur-X</a> (profil EN 16931), l’un des formats que les plateformes agréées acceptent.</li>
            <li>Pas de durée de validité dédiée sur le devis (à indiquer dans les notes), ni de factures de situation pour les chantiers.</li>
        </ul>
        <p>Pour voir chaque écran pas à pas, téléchargez le <a href="/docs/guide-stokito-fr.pdf" target="_blank" rel="noopener">guide d’utilisation (PDF)</a> ou ouvrez la <a href="{{ route('demo.login') }}">démo avec des données fictives</a>.</p>

        <div class="my-12 bg-zinc-900 text-white shadow-sm">
            <div class="p-7">
                <p class="!mb-2 ind-h text-xl leading-none text-white">Faites votre premier devis</p>
                <p class="!mb-5 text-zinc-300">Votre espace est prêt en une minute. Ajoutez votre logo, un client et un produit, puis transformez le devis en facture.</p>
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
        <p>À lire aussi : <a href="{{ $diff }}">Différence entre devis et facture</a> · <a href="{{ route('blog.show', 'transformer-devis-en-facture') }}">Transformer un devis en facture</a> · <a href="{{ route('blog.show', 'facture-electronique-obligatoire-2026-2027') }}">Facture électronique 2026-2027</a> · <a href="{{ route('seo.logiciel-gratuit') }}">Logiciel de gestion de stock gratuit</a> · <a href="{{ route('blog.index') }}">tous les guides du blog</a>.</p>
        <p class="text-sm text-zinc-500">Sources : Service-Public Entreprendre, fiches <a href="{{ $sp }}F23208" target="_blank" rel="noopener">« Tout savoir sur la facturation »</a>, <a href="{{ $sp }}F31808" target="_blank" rel="noopener">« Mentions obligatoires d’une facture »</a> et <a href="{{ $sp }}F31144" target="_blank" rel="noopener">« Devis obligatoire »</a>, consultées en novembre 2026. Ce guide ne remplace pas l’avis d’un expert-comptable.</p>
    </article>
</div>
@endcomponent
