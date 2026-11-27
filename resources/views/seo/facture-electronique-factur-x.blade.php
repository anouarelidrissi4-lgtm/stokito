{{-- SEO page (FR) — cluster "factur-x" / "logiciel facture électronique gratuit" / "facture électronique 2027" (2026-11-28).
     Product page; the full reform calendar lives in the blog article facture-electronique-obligatoire-2026-2027 (no cannibalisation).
     Facts: impots.gouv.fr "Je découvre la facturation électronique" (formats UBL / CII / Factur-X, plateformes agréées), checked 2026-11-28.
     Stokito facts: App\Support\FacturX, profile EN 16931, validated with the CEN EN 16931 CII schematron 1.3.17 and the FNFE Factur-X EN16931 schematron.
     No competitor names, no invented prices or ratings. --}}
@php
    $url = route('seo.factur-x');
    $blog = route('blog.show', 'facture-electronique-obligatoire-2026-2027');
    $dgfip = 'https://www.impots.gouv.fr/professionnel/je-decouvre-la-facturation-electronique';
    $faqs = [
        ['Qu’est-ce que Factur-X ?', 'Factur-X est un format de facture franco-allemand : un PDF lisible par une personne, dans lequel est joint un fichier XML (factur-x.xml) lisible par un logiciel. Le XML reprend les données de la norme européenne EN 16931 : vendeur, client, lignes, TVA et totaux. C’est l’un des trois formats acceptés pour la facture électronique en France, avec UBL et CII.'],
        ['Factur-X est-il obligatoire ?', 'Aucun des trois formats n’est imposé : Factur-X, UBL et CII sont tous acceptés par les plateformes agréées. Factur-X a un avantage pour les petites entreprises : le même fichier se lit comme un PDF classique et se traite automatiquement par les logiciels.'],
        ['Un PDF Factur-X envoyé par email suffit-il ?', 'Pas une fois votre obligation d’émission commencée (le 1er septembre 2027 pour les PME et micro-entreprises) : vos factures à des entreprises établies en France devront alors passer par une plateforme agréée. D’ici là, un PDF Factur-X envoyé par email reste une facture PDF normale, que le logiciel de votre client peut en plus lire automatiquement.'],
        ['Comment vérifier qu’un PDF contient bien Factur-X ?', 'Ouvrez le PDF dans Adobe Acrobat Reader et cliquez sur le trombone (Pièces jointes) : un fichier factur-x.xml doit apparaître. Dans Stokito, le menu Actions de chaque facture propose aussi « XML Factur-X » pour télécharger ce fichier seul.'],
        ['Factur-X est-il payant dans Stokito ?', 'Non. Factur-X est inclus dans toutes les formules de Stokito, y compris l’offre gratuite, pour toutes les entreprises établies en France.'],
        ['Je suis auto-entrepreneur sans TVA, ça marche ?', 'Oui. Si la franchise en base de TVA est cochée dans Ma Société, le XML indique la catégorie de TVA « exonérée » avec la mention « TVA non applicable, art. 293 B du CGI », et votre SIRET sert d’identifiant fiscal si vous n’avez pas de numéro de TVA.'],
    ];
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
        [
            '@type' => 'SoftwareApplication',
            'name' => 'Stokito',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web, macOS, Windows, Android, iOS',
            'url' => url('/'),
            'description' => 'Logiciel de facturation en ligne qui produit des factures Factur-X (profil EN 16931) : PDF/A-3 avec le XML joint.',
            'featureList' => 'Factur-X EN 16931, PDF/A-3, export XML, plusieurs taux de TVA, franchise en base 293 B, devis, bons de livraison, stock',
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Facture électronique Factur-X', 'item' => $url],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs),
        ],
    ]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
@component('layouts.public', [
    'title' => 'Factur-X gratuit : facture électronique EN 16931 prête pour 2027 | Stokito',
    'description' => 'Créez vos factures Factur-X gratuitement : PDF/A-3 avec XML EN 16931 joint, validé par les règles officielles. Prêt pour la facture électronique 2027.',
    'canonical' => $url,
    'jsonLd' => $jsonLd,
])
<div>
    {{-- HERO --}}
    <section class="bg-zinc-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14 grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <nav aria-label="Fil d’Ariane" class="ind-mono text-xs text-zinc-400">
                    <a href="{{ url('/') }}" class="hover:text-zinc-900">Accueil</a> <span aria-hidden="true">/</span> <span class="text-[#d6d6d6]">Facture électronique Factur-X</span>
                </nav>
                <p class="mt-8 ind-mono text-xs text-amber-300">NOUVEAU · NOVEMBRE 2026</p>
                <h1 class="mt-3 ind-h text-5xl sm:text-6xl leading-[0.92]">Facture électronique <span class="text-amber-300">Factur-X</span> gratuite</h1>
                <p class="mt-5 text-lg text-zinc-300 leading-relaxed">Chaque facture que vous créez dans Stokito est maintenant un PDF Factur-X : la facture que vous connaissez, avec à l’intérieur un fichier XML au format européen EN 16931. Le format de la facture électronique obligatoire, sans rien changer à vos habitudes.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] text-zinc-900">Créer un compte gratuit</a>
                    <a href="{{ route('demo.login') }}" class="ind-btn bg-white text-zinc-900">Tester la démo</a>
                </div>
                <p class="mt-4 ind-mono text-xs text-zinc-400">Profil EN 16931 · PDF/A-3 · Sans carte bancaire</p>
            </div>
            <figure class="border border-amber-300 bg-white shadow-sm rounded-lg">
                <img src="/images/screens/facture-electronique-factur-x.webp?v=20261128" width="1400" height="900" alt="Facture Factur-X générée par Stokito : le PDF de la facture et le fichier factur-x.xml joint au format EN 16931" class="w-full">
                <figcaption class="px-3 py-2 border-t border-zinc-200 text-xs text-zinc-600">Une facture de la démo Stokito (données fictives) : le PDF à gauche, le XML Factur-X qu’il contient à droite.</figcaption>
            </figure>
        </div>
    </section>

    {{-- SOMMAIRE --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-12">
        <div class="bg-white border border-zinc-200 shadow-lg shadow-zinc-900/5 p-5 rounded-xl">
            <p class="ind-mono text-xs text-zinc-500">SOMMAIRE</p>
            <ol class="mt-2 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-[15px] list-decimal ps-5">
                <li><a href="#factur-x" class="underline decoration-amber-400 decoration-2 underline-offset-4">Factur-X, c’est quoi ?</a></li>
                <li><a href="#calendrier" class="underline decoration-amber-400 decoration-2 underline-offset-4">Facture électronique : les dates</a></li>
                <li><a href="#stokito" class="underline decoration-amber-400 decoration-2 underline-offset-4">Factur-X dans Stokito</a></li>
                <li><a href="#contenu" class="underline decoration-amber-400 decoration-2 underline-offset-4">Ce que contient le XML</a></li>
                <li><a href="#verifie" class="underline decoration-amber-400 decoration-2 underline-offset-4">Validé par les règles officielles</a></li>
                <li><a href="#reglages" class="underline decoration-amber-400 decoration-2 underline-offset-4">Les 4 réglages à faire</a></li>
                <li><a href="#plateforme" class="underline decoration-amber-400 decoration-2 underline-offset-4">Et la plateforme agréée ?</a></li>
                <li><a href="#faq" class="underline decoration-amber-400 decoration-2 underline-offset-4">Questions fréquentes</a></li>
            </ol>
        </div>
    </div>

    <article class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-[17px] leading-[1.85] text-zinc-700 [&_h2]:font-['Inter','Tajawal',sans-serif] [&_h2]:font-bold ]:tracking-tight [&_h2]:text-zinc-900 [&_h2]:text-2xl sm:[&_h2]:text-3xl [&_h2]:leading-tight [&_h2]:mt-16 [&_h2]:mb-5 [&_h2]:pt-4 [&_h2]:border-t [&_h2]:border-zinc-200 [&_h2]:scroll-mt-24 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-zinc-900 [&_h3]:mt-8 [&_h3]:mb-2 [&_p]:mb-5 [&_strong]:text-zinc-900 [&_ul]:list-disc [&_ul]:ps-6 [&_ul]:mb-6 [&_ul]:space-y-2 [&_a]:font-semibold [&_a]:text-zinc-900 [&_a]:underline [&_a]:decoration-[#ffcc00] [&_a]:decoration-2 [&_a]:underline-offset-4">

        <h2 id="factur-x">Factur-X, c’est quoi ?</h2>
        <p><strong>Factur-X</strong> est un format de facture électronique « mixte », créé en France et en Allemagne. Un fichier Factur-X est un <strong>PDF</strong> (au standard d’archivage PDF/A-3) qui contient en pièce jointe un fichier <strong>XML</strong> nommé <code>factur-x.xml</code> :</p>
        <ul>
            <li>le <strong>PDF</strong> se lit comme n’importe quelle facture : vous pouvez l’imprimer, l’envoyer, l’archiver ;</li>
            <li>le <strong>XML</strong> contient les mêmes informations sous une forme qu’un logiciel lit sans erreur : numéro, vendeur, client, lignes, TVA, totaux, échéance.</li>
        </ul>
        <p>L’administration fiscale retient trois formats pour la facture électronique entre entreprises : <strong>UBL, CII et Factur-X</strong>, tous fondés sur la norme européenne EN 16931 (<a href="{{ $dgfip }}" target="_blank" rel="noopener">source : impots.gouv.fr</a>). Factur-X est le plus simple pour une petite entreprise, parce qu’il reste une facture lisible à l’œil nu.</p>
        <h3>Les profils Factur-X</h3>
        <p>Factur-X existe en plusieurs niveaux de détail : MINIMUM, BASIC WL, BASIC, <strong>EN 16931</strong> et EXTENDED. Les deux premiers ne contiennent pas le détail des lignes de la facture. Stokito utilise le profil <strong>EN 16931</strong>, qui reprend toute la norme européenne.</p>

        <h2 id="calendrier">Facture électronique : les dates à retenir</h2>
        <div class="my-6 grid sm:grid-cols-2 gap-4">
            <div class="bg-white border border-zinc-200 shadow-sm p-4 rounded-xl">
                <div class="ind-mono text-[11px] text-zinc-500">DEPUIS LE 1ER SEPTEMBRE 2026</div>
                <div class="mt-1 font-bold text-zinc-900">Toutes les entreprises reçoivent</div>
                <div class="text-[15px] leading-relaxed text-zinc-600">Toute entreprise assujettie à la TVA en France doit pouvoir recevoir des factures électroniques. Les grandes entreprises et les ETI doivent aussi en émettre.</div>
            </div>
            <div class="bg-[#ffcc00] border border-zinc-200 shadow-sm p-4 rounded-xl">
                <div class="ind-mono text-[11px] text-zinc-700">1ER SEPTEMBRE 2027</div>
                <div class="mt-1 font-bold text-zinc-900">PME et micro-entreprises émettent</div>
                <div class="text-[15px] leading-relaxed text-zinc-700">Les PME, TPE et micro-entreprises devront émettre leurs factures entre entreprises sous forme électronique, via une plateforme agréée.</div>
            </div>
        </div>
        <p>Le calendrier complet, le e-reporting et les nouvelles mentions obligatoires sont expliqués dans notre guide <a href="{{ $blog }}">facture électronique obligatoire 2026-2027</a>.</p>

        <h2 id="stokito">Factur-X dans Stokito : rien à faire de plus</h2>
        <p>Si votre entreprise est établie en <strong>France</strong> (pays choisi dans « Ma Société »), Stokito produit automatiquement un PDF Factur-X partout où une facture sort du logiciel :</p>
        <div class="my-6 bg-white border border-zinc-200 rounded-lg">
            @foreach ([
                ['PDF', 'Le bouton PDF et « Imprimer » donnent un PDF/A-3 avec le fichier factur-x.xml joint.'],
                ['Email', 'La facture envoyée par email depuis Stokito, et les relances automatiques, partent en Factur-X.'],
                ['WhatsApp', 'Le lien de la facture envoyé sur WhatsApp ouvre le même PDF Factur-X.'],
                ['XML seul', 'Le menu Actions propose « XML Factur-X » pour télécharger le fichier XML, par exemple pour votre expert-comptable.'],
            ] as [$t, $d])
                <div class="grid sm:grid-cols-[150px_1fr] gap-1 sm:gap-4 px-5 py-4 {{ $loop->last ? '' : 'border-b-2 border-zinc-200' }}">
                    <div class="ind-h text-xl text-zinc-900">{{ $t }}</div>
                    <div class="text-[15px] leading-relaxed">{{ $d }}</div>
                </div>
            @endforeach
        </div>
        <figure class="my-8 border border-zinc-200 bg-white shadow-xl shadow-zinc-900/10 rounded-xl">
            <img src="/images/screens/screen-factur-x-menu.webp?v=20261128" width="1042" height="500" loading="lazy" alt="Menu Actions d’une facture dans Stokito avec l’option XML Factur-X pour télécharger le fichier EN 16931" class="w-full">
            <figcaption class="px-4 py-2 border-t border-zinc-200 text-sm text-zinc-600">La liste des factures dans Stokito : « XML Factur-X » dans le menu Actions de chaque facture (démo, données fictives).</figcaption>
        </figure>
        <p>Le PDF garde exactement la même apparence : votre logo, vos couleurs, vos mentions. Seul le fichier XML s’ajoute à l’intérieur. Pour une entreprise établie hors de France, rien ne change : la facture reste un PDF classique.</p>

        <h2 id="contenu">Ce que contient le XML de vos factures</h2>
        <div class="my-8 overflow-x-auto border border-zinc-200 bg-white rounded-lg">
            <table class="w-full text-[15px]">
                <thead class="bg-zinc-900 text-white">
                    <tr><th class="text-start px-4 py-3">Dans Stokito</th><th class="text-start px-4 py-3">Dans le XML Factur-X</th></tr>
                </thead>
                <tbody class="[&_td]:px-4 [&_td]:py-3 [&_td]:border-t [&_td]:border-zinc-100 [&_td]:align-top">
                    <tr><td class="font-semibold">Ma Société</td><td>Raison sociale, adresse, <strong>SIREN</strong> (tiré du SIRET), numéro de TVA intracommunautaire, téléphone, email, IBAN</td></tr>
                    <tr><td class="font-semibold">Fiche client</td><td>Nom, adresse, <strong>SIREN ou numéro de TVA</strong> du client (champ « SIRET / N° TVA »)</td></tr>
                    <tr><td class="font-semibold">Lignes</td><td>Désignation, référence, quantité avec son <strong>unité</strong> (kg, m², pièce, heure…), prix unitaire HT, remise de la ligne</td></tr>
                    <tr><td class="font-semibold">TVA</td><td>Un détail par taux (20 %, 10 %, 5,5 %, 0 %), remise globale répartie par taux, ou exonération 293 B</td></tr>
                    <tr><td class="font-semibold">Totaux</td><td>Total HT, TVA, TTC et montant à payer, au centime près comme sur le PDF</td></tr>
                    <tr><td class="font-semibold">Mentions</td><td>Échéance et conditions de paiement, adresse de livraison, nature des opérations, TVA sur les débits, pénalités de retard, indemnité de 40 €, escompte</td></tr>
                </tbody>
            </table>
        </div>
        <p>Les quatre nouvelles mentions de la réforme (SIREN du client, adresse de livraison, nature des opérations, option pour la TVA d’après les débits) sont donc à la fois sur le PDF et dans le XML.</p>

        <h2 id="verifie">Validé par les règles officielles</h2>
        <p>Un XML qui « a l’air correct » ne suffit pas : une plateforme agréée vérifie chaque facture avec les règles de la norme, par exemple que le total HT est bien égal à la somme des lignes moins les remises, ou que la TVA de chaque taux est bien calculée.</p>
        <p>Nous avons vérifié les factures Factur-X de Stokito avec :</p>
        <ul>
            <li>le <strong>schéma XSD</strong> Factur-X du profil EN 16931 ;</li>
            <li>les <strong>règles officielles EN 16931</strong> (schematron du CEN, version 1.3.17) ;</li>
            <li>les <strong>règles Factur-X EN 16931</strong> publiées avec la norme Factur-X.</li>
        </ul>
        <p>Sur nos factures de test (plusieurs taux de TVA, remises par ligne et globales, franchise de TVA, client étranger) comme sur les factures de la démo : <strong>zéro erreur</strong>. Les règles de calcul sont aussi contrôlées automatiquement à chaque mise à jour de Stokito.</p>

        <h2 id="reglages">Les 4 réglages à faire une fois</h2>
        <div class="my-6 grid sm:grid-cols-2 gap-4">
            @foreach ([
                ['SIRET et TVA', 'Dans « Ma Société », saisissez votre SIRET et votre numéro de TVA intracommunautaire. Sans numéro de TVA, Stokito le calcule à partir de votre SIREN.'],
                ['Adresse complète', 'Rue, code postal et ville de votre entreprise et de vos clients : le code postal est repris dans le XML.'],
                ['SIRET des clients', 'Dans chaque fiche client professionnel, renseignez le SIRET ou le numéro de TVA : c’est une mention obligatoire de la réforme.'],
                ['IBAN', 'Votre IBAN dans le champ RIB : il est repris comme moyen de paiement par virement dans le XML.'],
            ] as $i => [$t, $d])
                <div class="bg-white border border-zinc-200 shadow-sm p-4 rounded-xl">
                    <div class="ind-mono text-[11px] text-zinc-500">RÉGLAGE {{ sprintf('%02d', $i + 1) }}</div>
                    <div class="mt-1 font-bold text-zinc-900">{{ $t }}</div>
                    <div class="text-[15px] leading-relaxed text-zinc-600">{{ $d }}</div>
                </div>
            @endforeach
        </div>

        <h2 id="plateforme">Et la plateforme agréée ?</h2>
        <p>À partir de votre date d’obligation, une facture entre entreprises établies en France doit être transmise par une <strong>plateforme agréée</strong> (la liste est publiée sur impots.gouv.fr). Pour être clair :</p>
        <ul>
            <li>Stokito <strong>n’est pas une plateforme agréée</strong>. Il produit des factures au format Factur-X EN 16931, l’un des trois formats que les plateformes agréées doivent accepter ;</li>
            <li>si votre plateforme accepte le dépôt de fichiers Factur-X, vous pouvez y déposer le PDF tel quel ;</li>
            <li>la connexion directe de Stokito à une plateforme agréée est à l’étude.</li>
        </ul>
        <h3>Ce que Stokito ne fait pas encore</h3>
        <ul>
            <li>Les <strong>avoirs</strong> restent pour l’instant en PDF classique (sans XML).</li>
            <li>L’<strong>autoliquidation</strong> (ventes à une entreprise d’un autre pays de l’UE sans TVA française) n’est pas gérée automatiquement.</li>
        </ul>

        <div class="my-12 bg-zinc-900 text-white shadow-sm">
            <div class="p-7">
                <p class="!mb-2 ind-h text-xl leading-none text-white">Votre première facture Factur-X</p>
                <p class="!mb-5 text-zinc-300">Créez votre compte, renseignez votre SIRET dans Ma Société, puis faites une facture : le PDF contient déjà son XML.</p>
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
        <p>À lire aussi : <a href="{{ $blog }}">Facture électronique obligatoire 2026-2027</a> · <a href="{{ route('seo.logiciel-devis-facture') }}">Logiciel devis facture gratuit</a> · <a href="{{ route('seo.scanner-facture') }}">Scanner de factures fournisseurs</a> · <a href="{{ route('seo.logiciel-gratuit') }}">Logiciel de gestion de stock gratuit</a>.</p>
        <p class="text-sm text-zinc-500">Sources : <a href="{{ $dgfip }}" target="_blank" rel="noopener">impots.gouv.fr, « Je découvre la facturation électronique »</a>, consulté en novembre 2026 ; norme EN 16931 et spécification Factur-X (profil EN 16931). Ce guide ne remplace pas l’avis d’un expert-comptable.</p>
    </article>
</div>
@endcomponent
