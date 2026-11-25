{{-- SEO page (FR), 2026-11-25 — cluster "logiciel de caisse gratuit" / "logiciel de caisse obligatoire" / "caisse enregistreuse en ligne".
     Legal facts checked 2026-11-25: impots.gouv.fr Q&R champ d'application (scope, exclusions, 4 conditions, certificate or editor attestation,
     attestation restored by loi de finances 2026), BOFiP ACTU-2026-00073, Légifrance art. 1770 duodecies (7 500 €). Stokito is NOT NF525-certified:
     say so. No competitor names, no invented figures. --}}
@php
    $url = route('seo.caisse');
    $ig = 'https://www.impots.gouv.fr/professionnel/questions/quel-est-le-champ-dapplication-de-lobligation-de-detenir-un-logiciel-de';
    $faqs = [
        ['Un logiciel de caisse est-il obligatoire ?', 'Non, rien n’oblige à en utiliser un. Mais si une entreprise assujettie à la TVA enregistre dans un logiciel les paiements de clients particuliers, ce logiciel doit respecter quatre conditions : inaltérabilité, sécurisation, conservation et archivage des données.'],
        ['Qui n’est pas concerné par l’obligation ?', 'Les entreprises qui ne vendent qu’à des professionnels, celles en franchise en base de TVA (micro-entrepreneurs par exemple), celles au remboursement forfaitaire agricole et celles qui ne réalisent que des opérations exonérées de TVA.'],
        ['Quelle est l’amende sans attestation ni certificat ?', '7 500 € par logiciel concerné, selon l’article 1770 duodecies du Code général des impôts. L’entreprise dispose ensuite de 60 jours pour se mettre en conformité.'],
        ['L’attestation de l’éditeur est-elle encore acceptée ?', 'Oui. La loi de finances pour 2026 a rétabli la possibilité de justifier la conformité par une attestation individuelle de l’éditeur, à côté du certificat délivré par un organisme accrédité. L’attestation suit un modèle officiel (BOI-LETTRE-000242).'],
        ['La caisse Stokito est-elle certifiée NF525 ?', 'Non. Elle est conçue pour les quatre conditions de la loi : tickets signés et chaînés, impossibles à modifier ou à supprimer, clôtures, journal des événements et archives signées. Elle n’a pas de certificat NF525.'],
        ['Stokito fournit-il l’attestation de l’éditeur ?', 'Pas pour le moment. Les entreprises en franchise en base de TVA et celles qui vendent uniquement à des professionnels n’en ont pas besoin. Une entreprise assujettie à la TVA qui encaisse des particuliers en France doit utiliser pour ces encaissements un logiciel certifié ou attesté.'],
        ['Faut-il du matériel spécial ?', 'Non. Un ordinateur, une tablette ou un téléphone avec Internet suffit. Une imprimante de tickets 80 mm et une douchette code-barres USB sont pratiques mais facultatives : la caméra du téléphone peut scanner les codes-barres.'],
        ['La caisse fonctionne-t-elle sans Internet ?', 'Non. Stokito est un logiciel en ligne : la caisse a besoin d’une connexion pour enregistrer les ventes.'],
    ];
    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
        ['@type' => 'SoftwareApplication', 'name' => 'Stokito', 'applicationCategory' => 'BusinessApplication', 'operatingSystem' => 'Web, Windows, macOS, Android, iOS',
         'url' => url('/'), 'description' => 'Logiciel de caisse en ligne gratuit relié au stock : tickets signés, clôtures Z, archives, gestion de stock et facturation.',
         'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR']],
        ['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Logiciel de caisse gratuit', 'item' => $url],
        ]],
        ['@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs)],
    ]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
@component('layouts.public', [
    'title' => 'Logiciel de caisse gratuit en ligne, relié au stock | Stokito',
    'description' => 'Logiciel de caisse gratuit en ligne : tickets signés et inaltérables, clôtures Z, archives, relié au stock et à la facturation. Ce que dit la loi en 2026.',
    'canonical' => $url,
    'jsonLd' => $jsonLd,
])
<div>
    <section class="bg-zinc-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14 grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <nav aria-label="Fil d’Ariane" class="ind-mono text-xs text-zinc-400">
                    <a href="{{ url('/') }}" class="hover:text-zinc-900">Accueil</a> <span aria-hidden="true">/</span> <span class="text-[#d6d6d6]">Logiciel de caisse gratuit</span>
                </nav>
                <p class="mt-8 ind-mono text-xs text-amber-300">GUIDE · MIS À JOUR {{ \Illuminate\Support\Str::upper(now()->translatedFormat('F Y')) }}</p>
                <h1 class="mt-3 ind-h text-5xl sm:text-6xl leading-[0.92]">Logiciel de caisse <span class="text-amber-300">gratuit</span>, relié au stock</h1>
                <p class="mt-5 text-lg text-zinc-300 leading-relaxed">Vendre au comptoir, rendre la monnaie, imprimer le ticket, et que le stock suive tout seul. Voici ce que doit faire un logiciel de caisse, ce que la loi impose depuis 2018 et ce qui a changé en 2026.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] text-zinc-900">Créer un compte gratuit</a>
                    <a href="{{ route('demo.login') }}" class="ind-btn bg-white text-zinc-900">Essayer la caisse</a>
                </div>
                <p class="mt-4 ind-mono text-xs text-zinc-400">Sans carte bancaire · PC, tablette et téléphone · sans installation</p>
            </div>
            <figure class="border border-amber-300 bg-white shadow-sm rounded-lg">
                <div class="h-8 flex items-center px-3 border-b border-zinc-200 bg-zinc-50 ind-mono text-[11px] text-zinc-500">stokito.com/caisse</div>
                <img src="/images/screens/screen-caisse-pos.webp?v=20261125m" width="1272" height="800" alt="Logiciel de caisse gratuit Stokito : produits à toucher ou scanner à gauche, ticket en cours avec total TTC et bouton Encaisser à droite" class="w-full">
                <figcaption class="px-3 py-2 border-t border-zinc-200 text-xs text-zinc-600">L’écran de caisse de Stokito (démo, données fictives).</figcaption>
            </figure>
        </div>
    </section>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-12">
        <div class="bg-white border border-zinc-200 shadow-lg shadow-zinc-900/5 p-5 rounded-xl">
            <p class="ind-mono text-xs text-zinc-500">SOMMAIRE</p>
            <ol class="mt-2 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-[15px] list-decimal ps-5">
                <li><a href="#role" class="underline decoration-amber-400 decoration-2 underline-offset-4">À quoi sert un logiciel de caisse</a></li>
                <li><a href="#loi" class="underline decoration-amber-400 decoration-2 underline-offset-4">Ce que dit la loi en 2026</a></li>
                <li><a href="#stokito" class="underline decoration-amber-400 decoration-2 underline-offset-4">La caisse Stokito</a></li>
                <li><a href="#materiel" class="underline decoration-amber-400 decoration-2 underline-offset-4">Le matériel</a></li>
                <li><a href="#limites" class="underline decoration-amber-400 decoration-2 underline-offset-4">Ce qu’elle ne fait pas</a></li>
                <li><a href="#faq" class="underline decoration-amber-400 decoration-2 underline-offset-4">Questions fréquentes</a></li>
            </ol>
        </div>
    </div>

    <article class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-[17px] leading-[1.85] text-zinc-700 [&_h2]:font-['Inter','Tajawal',sans-serif] [&_h2]:font-bold ]:tracking-tight [&_h2]:text-zinc-900 [&_h2]:text-2xl sm:[&_h2]:text-3xl [&_h2]:leading-tight [&_h2]:mt-16 [&_h2]:mb-5 [&_h2]:pt-4 [&_h2]:border-t [&_h2]:border-zinc-200 [&_h2]:scroll-mt-24 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-zinc-900 [&_h3]:mt-8 [&_h3]:mb-2 [&_p]:mb-5 [&_strong]:text-zinc-900 [&_ul]:list-disc [&_ul]:ps-6 [&_ul]:mb-6 [&_ul]:space-y-2 [&_a]:font-semibold [&_a]:text-zinc-900 [&_a]:underline [&_a]:decoration-[#ffcc00] [&_a]:decoration-2 [&_a]:underline-offset-4">

        <h2 id="role">À quoi sert un logiciel de caisse</h2>
        <p>Un <strong>logiciel de caisse</strong> enregistre les ventes au comptoir : les articles, le prix, la TVA et le mode de paiement. Il calcule le rendu de monnaie, imprime le ticket et, le soir, fait la <strong>clôture de caisse</strong> (le « Z ») qui fige les totaux de la journée.</p>
        <p>Relié à la gestion de stock, il fait une chose de plus : chaque article vendu sort du stock, sans ressaisie. C’est ce qui distingue une caisse d’une simple calculatrice à tickets, et c’est utile pour les quincailleries, négoces et magasins qui vendent à la fois au comptoir et sur facture.</p>

        <h2 id="loi">Logiciel de caisse obligatoire : ce que dit la loi en 2026</h2>
        <p>Rien n’oblige à utiliser un logiciel de caisse. Mais selon <a href="{{ $ig }}" target="_blank" rel="noopener">l’administration fiscale</a>, une entreprise <strong>assujettie à la TVA</strong> qui enregistre dans un logiciel les paiements de <strong>clients particuliers</strong> doit utiliser un logiciel qui garantit quatre conditions :</p>
        <div class="my-6 grid sm:grid-cols-2 gap-4">
            @foreach ([['Inaltérabilité', 'Une vente enregistrée ne peut plus être modifiée ni effacée ; une correction se fait par une nouvelle opération.'], ['Sécurisation', 'Les données sont protégées, par exemple par une signature électronique, pour que toute modification soit détectée.'], ['Conservation', 'Les données sont gardées, avec des clôtures périodiques (jour, mois, année).'], ['Archivage', 'Les données peuvent être archivées et présentées en cas de contrôle.']] as $i => [$t, $d])
                <div class="bg-white border border-zinc-200 shadow-sm p-4 rounded-xl">
                    <div class="ind-mono text-[11px] text-zinc-500">CONDITION {{ $i + 1 }}</div>
                    <div class="mt-1 font-bold text-zinc-900">{{ $t }}</div>
                    <div class="text-[15px] leading-relaxed text-zinc-600">{{ $d }}</div>
                </div>
            @endforeach
        </div>
        <h3>Qui n’est pas concerné</h3>
        <ul>
            <li>les entreprises qui vendent <strong>uniquement à des professionnels</strong> ;</li>
            <li>les entreprises en <strong>franchise en base de TVA</strong> (micro-entrepreneurs, par exemple) ;</li>
            <li>les exploitants au remboursement forfaitaire agricole, et les entreprises qui ne réalisent que des opérations exonérées de TVA.</li>
        </ul>
        <h3>Certificat ou attestation de l’éditeur</h3>
        <p>L’entreprise doit pouvoir présenter un <strong>certificat</strong> délivré par un organisme accrédité <strong>ou</strong> une <strong>attestation individuelle de l’éditeur</strong>. La loi de finances pour 2025 avait supprimé l’attestation ; la loi de finances pour 2026 l’a <a href="https://bofip.impots.gouv.fr/bofip/15035-PGP.html/ACTU-2026-00073" target="_blank" rel="noopener">rétablie</a>. Elle suit un modèle officiel en deux volets : l’un signé par l’éditeur, l’autre par l’entreprise qui utilise le logiciel.</p>
        <p>Sans l’un ou l’autre, l’amende est de <strong>7 500 € par logiciel</strong> (<a href="https://www.legifrance.gouv.fr/codes/article_lc/LEGIARTI000051203251" target="_blank" rel="noopener">article 1770 duodecies du CGI</a>), avec 60 jours pour se mettre en règle.</p>

        <h2 id="stokito">La caisse Stokito</h2>
        <p>Stokito est un logiciel en ligne de gestion de stock, de devis et de factures, <strong>actuellement gratuit</strong>. La caisse s’active dans « Ma Société » et utilise le même catalogue, le même stock et le même tableau de bord que vos factures.</p>
        <div class="my-6 bg-white border border-zinc-200 rounded-lg">
            @foreach ([
                ['Vendre', 'Touchez un produit ou scannez son code-barres, ajustez la quantité, le prix ou une remise. Paiement en espèces, carte, chèque ou un mélange ; le rendu de monnaie se calcule.'],
                ['Ticket', 'Ticket 80 mm avec vos identifiants (SIRET, n° de TVA), la TVA par taux, le paiement et la signature du ticket. Une réimpression porte la mention « DUPLICATA ».'],
                ['Inaltérable', 'Chaque ticket est numéroté sans rupture, signé et chaîné au précédent ; la base de données refuse toute modification. Une erreur se corrige par un ticket d’annulation.'],
                ['Clôtures Z', 'Clôture du jour en un clic, ou automatique chaque nuit, puis clôtures mensuelles et annuelles avec le grand total perpétuel.'],
                ['Archives', 'Archive ZIP signée de chaque période (tickets, lignes, paiements, clôtures, journal en CSV) et contrôle d’intégrité en un clic.'],
            ] as [$t, $d])
                <div class="grid sm:grid-cols-[150px_1fr] gap-1 sm:gap-4 px-5 py-4 {{ $loop->last ? '' : 'border-b-2 border-zinc-200' }}">
                    <div class="ind-h text-xl text-zinc-900">{{ $t }}</div>
                    <div class="text-[15px] leading-relaxed">{{ $d }}</div>
                </div>
            @endforeach
        </div>
        <div class="my-8 grid sm:grid-cols-[1fr_1.2fr] gap-6 items-start">
            <figure class="border border-zinc-200 bg-white shadow-xl shadow-zinc-900/10 rounded-xl">
                <img src="/images/screens/screen-caisse-ticket.webp?v=20261125" width="616" height="944" loading="lazy" alt="Ticket de caisse Stokito : identifiants de l’entreprise, lignes, total TTC, TVA à 20 %, espèces, rendu de monnaie et signature" class="w-full">
                <figcaption class="px-3 py-2 border-t border-zinc-200 text-xs text-zinc-600">Un ticket de caisse (données fictives).</figcaption>
            </figure>
            <figure class="border border-zinc-200 bg-white shadow-xl shadow-zinc-900/10 rounded-xl">
                <img src="/images/screens/screen-caisse-clotures.webp?v=20261125m" width="1144" height="872" loading="lazy" alt="Clôtures de caisse Z dans Stokito : total non clôturé, contrôle d’intégrité, clôtures journalières avec grand total perpétuel et archives" class="w-full">
                <figcaption class="px-3 py-2 border-t border-zinc-200 text-xs text-zinc-600">Les clôtures Z et les archives (données fictives).</figcaption>
            </figure>
        </div>

        <h2 id="materiel">Le matériel</h2>
        <ul>
            <li>un <strong>ordinateur, une tablette ou un téléphone</strong> avec Internet : la caisse s’ouvre dans le navigateur ;</li>
            <li>une <strong>imprimante de tickets 80 mm</strong> (facultative) : le ticket s’imprime comme une page web ;</li>
            <li>une <strong>douchette code-barres USB</strong> (facultative) : elle tape le code comme un clavier ; sinon, la caméra du téléphone scanne.</li>
        </ul>

        <h2 id="limites">Ce que la caisse Stokito ne fait pas</h2>
        <ul>
            <li>Elle n’est <strong>pas certifiée NF525</strong>, et l’éditeur ne délivre <strong>pas encore d’attestation individuelle</strong>. Si votre entreprise est assujettie à la TVA et encaisse des particuliers en France, utilisez pour ces encaissements un logiciel certifié ou attesté. Sans attestation, la caisse Stokito convient aux entreprises en <strong>franchise en base de TVA</strong> et à celles qui vendent <strong>uniquement à des professionnels</strong>, qui ne sont pas concernées par l’obligation.</li>
            <li>Elle ne fonctionne <strong>pas hors connexion</strong>.</li>
            <li>Elle n’est pas reliée au terminal de paiement : le paiement par carte se saisit sur l’écran de caisse.</li>
            <li>Elle ne commande pas l’ouverture d’un tiroir-caisse.</li>
        </ul>
        <p>Pour le reste de la gestion (devis, bons de livraison, factures, stock, inventaire), voir notre page <a href="{{ route('seo.logiciel-gratuit') }}">logiciel de gestion de stock gratuit</a> et le <a href="/docs/guide-stokito-fr.pdf" target="_blank" rel="noopener">guide d’utilisation (PDF)</a>, chapitre « La caisse ».</p>

        <div class="my-12 bg-zinc-900 text-white shadow-sm">
            <div class="p-7">
                <p class="!mb-2 ind-h text-xl leading-none text-white">Essayez la caisse</p>
                <p class="!mb-5 text-zinc-300">La démo contient des produits et des tickets fictifs : encaissez une vente, imprimez le ticket, faites une clôture Z.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('demo.login') }}" class="ind-btn bg-[#ffcc00] !text-[#111] !no-underline">Ouvrir la démo</a>
                    <a href="{{ route('register') }}" class="ind-btn bg-white !text-[#111] !no-underline">Créer un compte gratuit</a>
                </div>
            </div>
        </div>

        <h2 id="faq">Questions fréquentes</h2>
        @foreach ($faqs as [$q, $a])
            <h3>{{ $q }}</h3>
            <p>{{ $a }}</p>
        @endforeach
        <p class="text-sm text-zinc-500">Sources : <a href="{{ $ig }}" target="_blank" rel="noopener">impots.gouv.fr, champ d’application de l’obligation</a>, <a href="https://bofip.impots.gouv.fr/bofip/15035-PGP.html/ACTU-2026-00073" target="_blank" rel="noopener">BOFiP, rétablissement de l’attestation (loi de finances 2026)</a>, <a href="https://www.legifrance.gouv.fr/codes/article_lc/LEGIARTI000051203251" target="_blank" rel="noopener">Légifrance, article 1770 duodecies du CGI</a>, consultés en novembre 2026. Cette page ne remplace pas l’avis d’un expert-comptable.</p>
    </article>
</div>
@endcomponent
