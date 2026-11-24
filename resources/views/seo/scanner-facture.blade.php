{{-- SEO page (FR), 2026-11-24 — cluster "scanner facture fournisseur" / "saisie automatique facture fournisseur" / "OCR facture".
     Facts only about Stokito's real feature (commit 5ee8680, tested on a fictional invoice). E-invoicing dates as verified on 2026-11-24
     (DGFiP / service-public). No competitor names, no invented stats or accuracy rates. --}}
@php
    $url = route('seo.scanner-facture');
    $fe = route('blog.show', 'facture-electronique-obligatoire-2026-2027');
    $faqs = [
        ['Le scanner de factures est-il gratuit ?', 'Oui. Chaque entreprise inscrite sur Stokito peut scanner jusqu’à 30 factures fournisseurs par mois sans payer. Le compteur repart le 1er de chaque mois, et la saisie manuelle reste toujours possible.'],
        ['Quels fichiers puis-je scanner ?', 'Une photo prise avec le téléphone (JPG ou PNG) ou un fichier PDF, jusqu’à 10 Mo. Pour une facture de plusieurs pages, le PDF est le meilleur choix.'],
        ['Le bon de commande est-il enregistré automatiquement ?', 'Non. Le scanner remplit le formulaire, puis vous vérifiez le fournisseur, les quantités et les prix avant de cliquer sur « Enregistrer ». Rien n’est enregistré sans vous.'],
        ['Que se passe-t-il si un produit de la facture n’existe pas dans mon catalogue ?', 'La ligne est signalée « produit introuvable » avec un bouton « Créer ce produit ». La fiche s’ouvre déjà remplie avec la désignation, la référence et le prix d’achat lus sur la facture : il reste à indiquer le prix de vente.'],
        ['Le scanner lit-il les factures de fournisseurs marocains ?', 'Oui. Le fournisseur est reconnu par son ICE s’il figure dans votre liste, sinon par son nom. Les montants sont repris tels qu’ils sont écrits sur la facture.'],
        ['Que devient la photo de ma facture ?', 'Elle est transmise à Anthropic, qui fournit le modèle d’intelligence artificielle Claude, uniquement pour lire son contenu, puis Stokito supprime le fichier. Seules les informations que vous enregistrez restent dans votre espace. Le détail est dans notre politique de confidentialité.'],
        ['Peut-on essayer le scanner dans la démo ?', 'Non, il est désactivé dans la démo. Créez un compte gratuit pour l’essayer sur une vraie facture fournisseur.'],
    ];
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
        [
            '@type' => 'SoftwareApplication',
            'name' => 'Stokito',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web, macOS, Windows, Android, iOS',
            'url' => url('/'),
            'description' => 'Logiciel de gestion de stock et de facturation avec scanner de factures fournisseurs : photo ou PDF lu par IA, bon de commande prérempli.',
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Scanner facture fournisseur', 'item' => $url],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs),
        ],
    ]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
@component('layouts.public', [
    'title' => 'Scanner facture fournisseur : saisie automatique par IA | Stokito',
    'description' => 'Scannez une facture fournisseur en photo ou PDF : fournisseur, date et lignes remplis par l’IA, produits retrouvés, stock à jour. 30 factures par mois gratuites.',
    'canonical' => $url,
    'jsonLd' => $jsonLd,
])
<div>
    {{-- HERO --}}
    <section class="bg-[#111] text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14 grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <nav aria-label="Fil d’Ariane" class="ind-mono text-xs text-[#9a9a9a]">
                    <a href="{{ url('/') }}" class="hover:text-[#ffcc00]">Accueil</a> <span aria-hidden="true">/</span> <span class="text-[#d6d6d6]">Scanner facture fournisseur</span>
                </nav>
                <p class="mt-8 ind-mono text-xs text-[#ffcc00]">NOUVEAU · SCANNER IA · NOVEMBRE 2026</p>
                <h1 class="mt-3 ind-h text-5xl sm:text-6xl uppercase leading-[0.92]">Scanner une facture fournisseur, <span class="text-[#ffcc00]">sans ressaisie</span></h1>
                <p class="mt-5 text-lg text-[#cfcfcf] leading-relaxed">Prenez la facture en photo ou choisissez le PDF : Stokito lit le fournisseur, le numéro, la date et chaque ligne, retrouve vos produits et prépare le bon de commande. Vous vérifiez, vous enregistrez, et le stock se met à jour à la réception.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] text-[#111]">Créer un compte gratuit</a>
                    <a href="#comment" class="ind-btn bg-white text-[#111]">Voir comment ça marche</a>
                </div>
                <p class="mt-4 ind-mono text-xs text-[#9a9a9a]">30 factures par mois incluses · photo ou PDF · France et Maroc</p>
            </div>
            <figure class="border-2 border-[#ffcc00] bg-white shadow-[10px_10px_0_#ffcc00]">
                <div class="h-8 flex items-center px-3 border-b-2 border-[#111] bg-[#f4f2ec] ind-mono text-[11px] text-[#555]">stokito.com/commande-fournisseurs/create</div>
                <img src="/images/screens/screen-scanner-facture.webp?v=20261124" width="1400" height="1557" alt="Scanner facture fournisseur dans Stokito : bon de commande rempli automatiquement avec le fournisseur, le numéro de facture, la date et quatre lignes lues sur la photo" class="w-full">
                <figcaption class="px-3 py-2 border-t-2 border-[#111] text-xs text-[#444]">Résultat réel du scanner sur une facture d’exemple (données fictives) : 4 lignes lues, 1 produit reconnu, 1 à vérifier, 2 à créer.</figcaption>
            </figure>
        </div>
        <div class="ind-hazard h-3"></div>
    </section>

    {{-- SOMMAIRE --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-12">
        <div class="bg-white border-2 border-[#111] shadow-[6px_6px_0_#111] p-5">
            <p class="ind-mono text-xs text-[#666]">SOMMAIRE</p>
            <ol class="mt-2 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-[15px] list-decimal ps-5">
                <li><a href="#pourquoi" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">Pourquoi arrêter la ressaisie</a></li>
                <li><a href="#ocr-ia" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">OCR ou lecture par IA</a></li>
                <li><a href="#comment" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">Scanner une facture dans Stokito</a></li>
                <li><a href="#conseils" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">6 conseils pour une bonne lecture</a></li>
                <li><a href="#facture-electronique" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">Et la facture électronique ?</a></li>
                <li><a href="#limites" class="underline decoration-[#ffcc00] decoration-2 underline-offset-4">Ce que le scanner ne fait pas</a></li>
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

        <h2 id="pourquoi">Pourquoi arrêter de saisir les factures fournisseurs à la main</h2>
        <p>Chaque livraison arrive avec une facture. Pour que votre stock et vos prix d’achat soient justes, son contenu doit entrer dans votre logiciel : le fournisseur, le numéro, la date, puis chaque article avec sa quantité et son prix. À la main, c’est long, et une quantité ou un prix mal recopié fausse le stock et les marges sans que personne ne s’en aperçoive.</p>
        <p>La <strong>saisie automatique des factures fournisseurs</strong> fait ce travail à votre place : vous donnez la facture (photo ou PDF), le logiciel remplit le document d’achat, et vous n’avez plus qu’à contrôler. Dans un logiciel relié au stock, cela a trois effets :</p>
        <ul>
            <li>le <strong>stock</strong> monte avec les bonnes quantités quand la marchandise est reçue ;</li>
            <li>le <strong>prix d’achat</strong> de chaque ligne est enregistré, ce qui rend les marges du tableau de bord fiables ;</li>
            <li>chaque <strong>facture fournisseur</strong> reste rattachée à son bon de commande par son numéro, ce qui facilite le suivi des soldes et le travail du comptable.</li>
        </ul>

        <h2 id="ocr-ia">OCR ou lecture par IA : quelle différence ?</h2>
        <p>L’<strong>OCR</strong> (reconnaissance optique de caractères) transforme une image en texte. C’est utile, mais le résultat est un bloc de mots : il faut encore savoir quel nom est celui du fournisseur, quelle ligne est un article et quel chiffre est un sous-total.</p>
        <p>Une <strong>lecture par IA</strong> va plus loin : le modèle comprend la mise en page de la facture et rend directement des champs rangés. Stokito utilise le modèle Claude d’Anthropic pour cette lecture.</p>
        <div class="my-8 overflow-x-auto border-2 border-[#111] bg-white">
            <table class="w-full text-[15px]">
                <thead class="bg-[#111] text-[#ffcc00]">
                    <tr><th class="text-start px-4 py-3"></th><th class="text-start px-4 py-3">OCR seul</th><th class="text-start px-4 py-3">Lecture par IA</th></tr>
                </thead>
                <tbody class="[&_td]:px-4 [&_td]:py-3 [&_td]:border-t [&_td]:border-[#ddd] [&_td]:align-top">
                    <tr><td class="font-semibold">Ce qu’on obtient</td><td>Le texte de la page</td><td>Des champs : fournisseur, numéro, date, lignes, totaux</td></tr>
                    <tr><td class="font-semibold">Fournisseur ou client ?</td><td>À trier vous-même</td><td>Distingue l’émetteur du destinataire</td></tr>
                    <tr><td class="font-semibold">Lignes d’articles</td><td>Mélangées aux sous-totaux et aux mentions</td><td>Une ligne par article, frais de port compris</td></tr>
                    <tr><td class="font-semibold">Prix TTC seulement</td><td>Recopiés tels quels</td><td>Convertis en HT avec le taux de TVA de la ligne</td></tr>
                    <tr><td class="font-semibold">Vérification</td><td>Indispensable</td><td>Toujours indispensable</td></tr>
                </tbody>
            </table>
        </div>
        <p>Dans les deux cas, une photo floue ou une écriture manuscrite peuvent produire des erreurs. C’est pour cela que Stokito ne fait que <strong>préparer</strong> le bon de commande : c’est vous qui l’enregistrez.</p>

        <h2 id="comment">Scanner une facture fournisseur dans Stokito, étape par étape</h2>
        <div class="my-6 bg-white border-2 border-[#111]">
            @foreach ([
                ['1. Ouvrir', 'Menu Achats, puis « Nouveau bon de commande ». En haut de la page, la carte jaune « Scanner une facture fournisseur ».'],
                ['2. Photo ou PDF', 'Touchez « Photo ou PDF ». Sur téléphone, vous pouvez prendre la photo directement ; sur ordinateur, choisissez le fichier. La lecture démarre seule et prend de 10 à 40 secondes.'],
                ['3. Vérifier', 'Le fournisseur, le n° de facture, la date, le taux de TVA et les lignes sont remplis. Chaque ligne indique ce qui a été lu sur la facture et si le produit a été reconnu.'],
                ['4. Enregistrer', 'Corrigez si besoin, puis « Enregistrer ». Quand la marchandise arrive, passez le bon en « Reçue » : le stock augmente des quantités commandées.'],
            ] as [$t, $d])
                <div class="grid sm:grid-cols-[170px_1fr] gap-1 sm:gap-4 px-5 py-4 {{ $loop->last ? '' : 'border-b-2 border-[#111]' }}">
                    <div class="ind-h text-xl uppercase text-[#111]">{{ $t }}</div>
                    <div class="text-[15px] leading-relaxed">{{ $d }}</div>
                </div>
            @endforeach
        </div>
        <h3>Comment Stokito retrouve vos produits et votre fournisseur</h3>
        <ul>
            <li><strong>Fournisseur</strong> : reconnu par l’identifiant enregistré dans sa fiche (SIRET, n° de TVA ou ICE), sinon par son nom. S’il est nouveau, un bouton « Créer ce fournisseur » reprend son nom, son identifiant, son adresse et son téléphone.</li>
            <li><strong>Reconnu</strong> : la référence, le code-barres ou la désignation exacte correspond à un produit de votre catalogue.</li>
            <li><strong>À vérifier</strong> : la désignation ressemble beaucoup à un produit existant ; Stokito le propose, à vous de confirmer.</li>
            <li><strong>Introuvable</strong> : le bouton « Créer ce produit » ouvre une fiche déjà remplie avec la désignation, la référence et le prix d’achat lus sur la facture.</li>
        </ul>
        <figure class="my-8 border-2 border-[#111] bg-white shadow-[8px_8px_0_#111]">
            <img src="/images/screens/screen-scanner-produit.webp?v=20261124" width="1000" height="943" loading="lazy" alt="Fiche nouveau produit préremplie par le scanner de factures : référence PSF18, désignation Perceuse sans fil 18V, prix d’achat 89 et fournisseur sélectionné" class="w-full">
            <figcaption class="px-4 py-2 border-t-2 border-[#111] text-sm text-[#444]">« Créer ce produit » : la fiche s’ouvre avec ce qui a été lu sur la facture. Il reste à indiquer le prix de vente (données fictives).</figcaption>
        </figure>

        <h2 id="conseils">6 conseils pour une lecture sans erreur</h2>
        <div class="my-6 grid sm:grid-cols-2 gap-4">
            @foreach ([
                ['Facture bien à plat', 'Posez la feuille sur une table plutôt que de la tenir en main : les lignes restent droites et lisibles.'],
                ['Toute la page dans l’image', 'Le nom du fournisseur en haut et les totaux en bas doivent être visibles sur la même photo.'],
                ['De la lumière, sans reflet', 'Évitez le flash sur un papier brillant et l’ombre du téléphone sur les chiffres.'],
                ['Le PDF quand vous l’avez', 'Si le fournisseur envoie sa facture par email, scannez le PDF : la lecture est plus sûre qu’avec une photo.'],
                ['Une facture à la fois', 'Un scan correspond à une facture. Pour plusieurs pages, utilisez un seul PDF.'],
                ['Comparez le total', 'Avant d’enregistrer, comparez le total HT de Stokito à celui de la facture : s’ils sont égaux, les lignes sont justes.'],
            ] as $i => [$t, $d])
                <div class="bg-white border-2 border-[#111] shadow-[5px_5px_0_#111] p-4">
                    <div class="ind-mono text-[11px] text-[#666]">CONSEIL {{ sprintf('%02d', $i + 1) }}</div>
                    <div class="mt-1 font-bold text-[#111]">{{ $t }}</div>
                    <div class="text-[15px] leading-relaxed text-[#444]">{{ $d }}</div>
                </div>
            @endforeach
        </div>

        <h2 id="facture-electronique">Et la facture électronique ?</h2>
        <p>En France, toute entreprise assujettie à la TVA doit pouvoir <strong>recevoir des factures électroniques depuis le 1er septembre 2026</strong>, via une plateforme agréée. L’obligation d’en <strong>émettre</strong> ne concerne les PME et les micro-entreprises qu’à partir du <strong>1er septembre 2027</strong>. D’ici là, une bonne partie de vos petits fournisseurs continueront d’envoyer des factures en PDF ou sur papier, et les factures de fournisseurs établis hors de France restent en dehors de ce circuit.</p>
        <p>Le scanner sert justement pour ces factures-là. Le calendrier complet est dans notre article <a href="{{ $fe }}">facture électronique obligatoire 2026-2027</a>.</p>

        <h2 id="limites">Ce que le scanner ne fait pas</h2>
        <ul>
            <li>Il <strong>n’enregistre rien seul</strong> : vous validez toujours le bon de commande.</li>
            <li>Il ne lit qu’<strong>une facture par scan</strong> ; il n’y a pas d’import en lot ni de lecture des emails.</li>
            <li>Ce n’est <strong>pas un logiciel de comptabilité</strong> : il ne passe pas d’écritures et ne fait pas de rapprochement bancaire.</li>
            <li>Il est limité à <strong>30 factures par mois</strong> par entreprise, et désactivé dans la démo.</li>
            <li>La photo est transmise à Anthropic pour la lecture, puis supprimée par Stokito (voir la <a href="{{ route('legal.privacy') }}">politique de confidentialité</a>). Si vous préférez éviter cette transmission, la saisie manuelle reste disponible.</li>
        </ul>
        <p>Pour le reste du parcours (bons de livraison, factures clients, inventaire), voir notre page <a href="{{ route('seo.logiciel-gratuit') }}">logiciel de gestion de stock gratuit</a> et le <a href="/docs/guide-stokito-fr.pdf" target="_blank" rel="noopener">guide d’utilisation (PDF)</a>.</p>

        <div class="my-12 bg-[#111] text-white border-2 border-[#111] shadow-[8px_8px_0_#ffcc00]">
            <div class="ind-hazard h-2"></div>
            <div class="p-7">
                <p class="!mb-2 ind-h text-3xl uppercase leading-none text-white">Scannez votre prochaine facture</p>
                <p class="!mb-5 text-[#cfcfcf]">Votre espace est prêt en une minute, sans carte bancaire. Gardez la dernière facture de votre fournisseur à portée de main.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] !text-[#111] !no-underline">Créer un compte gratuit</a>
                </div>
            </div>
        </div>

        <h2 id="faq">Questions fréquentes</h2>
        @foreach ($faqs as [$q, $a])
            <h3>{{ $q }}</h3>
            <p>{{ $a }}</p>
        @endforeach
        <p>À lire aussi : <a href="{{ route('seo.logiciel-gratuit') }}">Logiciel de gestion de stock gratuit</a> · <a href="{{ route('seo.logiciel-devis-facture') }}">Logiciel devis facture gratuit</a> · <a href="{{ $fe }}">Facture électronique 2026-2027</a> · <a href="{{ route('blog.index') }}">tous les guides du blog</a>.</p>
    </article>
</div>
@endcomponent
