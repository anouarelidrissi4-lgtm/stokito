@php
    $locale = app()->getLocale();
    $rtl = $locale === 'ar';
    $guide = $rtl ? '/docs/guide-stokito-ar.pdf' : '/docs/guide-stokito-fr.pdf';
    $wa = 'https://wa.me/212676035777';
    $faqs = [
        ['Stokito fonctionne-t-il sur téléphone ?', "Oui. Stokito s'ouvre dans le navigateur de votre ordinateur, de votre tablette ou de votre téléphone. Il n'y a rien à installer."],
        ['Mes factures affichent-elles les identifiants de mon entreprise ?', 'Oui, selon votre pays : SIRET, N° TVA et RCS en France ; ICE, IF, RC, Taxe professionnelle et CNSS au Maroc. Les identifiants du client, la TVA détaillée et le montant en lettres sont aussi imprimés. Faites valider vos mentions par votre comptable.'],
        ['Comment le stock se met-il à jour ?', 'Le stock baisse quand un bon de livraison passe en « Livré » ou quand vous faites une facture directe. Il augmente quand vous marquez une commande fournisseur comme reçue. L’inventaire corrige les écarts.'],
        ['Puis-je saisir une facture fournisseur en la prenant en photo ?', 'Oui. Dans « Nouveau bon de commande », touchez « Photo ou PDF » : Stokito lit le fournisseur, la date, le numéro et les lignes de la facture, puis retrouve vos produits. Vous vérifiez avant d’enregistrer. 30 factures par mois sont incluses gratuitement.'],
        ['Qu’est-ce que le Copilot IA ?', 'Un assistant dans Stokito : écrivez en français, en arabe ou en darija. Il répond avec vos chiffres (ventes, factures impayées, stock) et prépare vos devis, factures, bons de livraison et bons de commande. Il ne les enregistre jamais tout seul : le formulaire s’ouvre rempli, vous vérifiez puis vous enregistrez. 150 messages par mois sont inclus gratuitement.'],
        ['Stokito a-t-il une caisse pour vendre au comptoir ?', 'Oui. Activez la caisse dans « Ma Société » : vous scannez ou touchez les produits, encaissez en espèces, carte ou chèque, et le ticket s’imprime. Chaque ticket est numéroté, signé et ne peut plus être modifié ; une erreur se corrige par un ticket d’annulation. Les clôtures Z journalières, mensuelles et annuelles sont automatiques.'],
        ['Plusieurs employés peuvent-ils travailler en même temps ?', 'Oui. Chaque employé a son compte et vous choisissez ce qu’il peut voir : par exemple cacher les prix d’achat et les marges aux vendeurs.'],
        ['Puis-je importer mes produits depuis Excel ?', 'Oui. Exportez la liste pour obtenir le modèle, remplissez-le dans Excel puis importez-le : les catégories manquantes sont créées automatiquement.'],
        ['Mes données sont-elles sauvegardées ?', 'Oui. Une sauvegarde automatique est faite chaque nuit et chaque société ne voit que ses propres données.'],
        ['Comment obtenir un compte ?', 'Cliquez sur « Créer un compte gratuit », choisissez votre pays et votre espace est prêt. C’est gratuit, sans carte bancaire. Si vous voulez, nous vous aidons à importer vos produits sur WhatsApp.'],
    ];
    $screens = [
        ['dashboard', 'Tableau de bord'], ['devis_form', 'Devis'], ['factures', 'Factures'], ['inventaire', 'Inventaire'], ['products', 'Produits'],
    ];
    $sectors = ['Quincailleries', 'Négoce de matériaux', 'Grossistes', 'Distributeurs', 'Pièces auto', 'Sanitaire et plomberie', 'Électricité', 'Fournitures de bureau', 'Peinture et droguerie', 'Matériel agricole'];
    $waIcon = '<svg viewBox="0 0 24 24" class="h-4 w-4 fill-current" aria-hidden="true"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.2 4.78 1.2h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm5.8 14.01c-.24.68-1.4 1.3-1.93 1.38-.49.08-1.12.11-1.81-.11-.42-.13-.95-.31-1.64-.6-2.89-1.25-4.78-4.15-4.92-4.34-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.12 1.01-2.41.26-.29.57-.36.76-.36.19 0 .38 0 .55.01.18.01.41-.07.64.49.24.58.81 2.01.88 2.16.07.15.12.32.02.51-.09.19-.14.31-.28.48-.14.17-.29.37-.41.5-.14.14-.28.3-.12.58.16.28.72 1.19 1.55 1.93 1.07.95 1.96 1.25 2.24 1.39.28.14.44.12.6-.07.16-.19.68-.79.87-1.06.18-.27.37-.22.62-.13.26.09 1.63.77 1.91.91.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg>';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicons />
    <title>{{ __('Logiciel de gestion de stock et de facturation gratuit | Stokito') }}</title>
    <meta name="description" content="{{ __('Stokito, logiciel de gestion de stock en ligne gratuit : devis, bons de livraison, factures, inventaire, alertes de stock bas et relances clients. Sans installation, sans carte bancaire.') }}">
    <link rel="canonical" href="https://stokito.com/">
    <meta name="google-site-verification" content="H8XthOFRDaYoH40nAZcc3p3AuApVdpXNPTAukNDdub8" />
    <meta name="google-site-verification" content="XEy0-r-vrdATmXb-MhIP0hg5zhoY9-tW4Q5uWn-H0zY" />
    <meta property="og:title" content="Stokito">
    <meta property="og:description" content="{{ __('Gestion de stock, devis, bons de livraison et factures pour les PME. Gratuit, sans installation.') }}">
    <meta property="og:image" content="https://stokito.com/images/screens/screen-dashboard.webp?v=20261125m">
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            ['@type' => 'Organization', '@id' => 'https://stokito.com/#org', 'name' => 'Stokito', 'url' => 'https://stokito.com/', 'logo' => 'https://stokito.com/images/brand/icon-512.png', 'email' => 'contact@stokito.com'],
            ['@type' => 'WebSite', 'name' => 'Stokito', 'url' => 'https://stokito.com/', 'publisher' => ['@id' => 'https://stokito.com/#org']],
            ['@type' => 'SoftwareApplication', 'name' => 'Stokito', 'applicationCategory' => 'BusinessApplication', 'operatingSystem' => 'Web', 'url' => 'https://stokito.com', 'inLanguage' => ['fr', 'ar', 'en'],
             'description' => 'Logiciel de gestion de stock et de facturation en ligne pour les PME.', 'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR']],
            ['@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => __($f[0]), 'acceptedAnswer' => ['@type' => 'Answer', 'text' => __($f[1])]], $faqs)],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:600,700,800{{ $rtl ? '|tajawal:400,500,700,800' : '' }}&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>

        /* ---------- industrial / dépôt art direction (2026-11-23) ---------- */
        .ind-btn:hover { transform: translate(2px, 2px); box-shadow: 2px 2px 0 #111; }
        .sp-ind .ind-h, .hero-title { font-family: 'Poppins', 'Tajawal', sans-serif; }
        .hero-title { font-weight: 800; line-height: 1.06; letter-spacing: -0.03em; }
        .hero-dots { background-image: radial-gradient(rgba(255,204,0,.45) 1.6px, transparent 1.7px); background-size: 22px 22px; }
        .hero-bg { background-color: #0d0d0e; background-image:
            radial-gradient(900px 620px at 85% 8%, rgba(255,204,0,.30), transparent 60%),
            radial-gradient(700px 500px at 5% 105%, rgba(255,204,0,.14), transparent 60%),
            linear-gradient(160deg, #0b0b0c 0%, #141414 45%, #1e1a0c 100%); }
        [dir=rtl] .hero-bg { background-image:
            radial-gradient(900px 620px at 15% 8%, rgba(255,204,0,.30), transparent 60%),
            radial-gradient(700px 500px at 95% 105%, rgba(255,204,0,.14), transparent 60%),
            linear-gradient(200deg, #0b0b0c 0%, #141414 45%, #1e1a0c 100%); }
        .gd-book { transition: translate .55s cubic-bezier(.2,.8,.2,1), rotate .55s cubic-bezier(.2,.8,.2,1), box-shadow .3s; animation: gdFloat 6s ease-in-out infinite; }
        .gd-b2 { translate: -78px 10px; rotate: -12deg; animation-delay: -2s; }
        .gd-b1 { translate: -8px -8px; rotate: -3deg; animation-delay: -4s; }
        .gd-b0 { translate: 62px 6px; rotate: 8deg; }
        .gd-stack:hover .gd-b2 { translate: -120px 14px; rotate: -16deg; }
        .gd-stack:hover .gd-b0 { translate: 100px 10px; rotate: 13deg; }
        .gd-stack[data-on="0"] .gd-b0, .gd-stack[data-on="1"] .gd-b1, .gd-stack[data-on="2"] .gd-b2 { rotate: 0deg; z-index: 5; box-shadow: 0 30px 60px -12px rgba(0,0,0,.45), 0 0 0 3px #ffcc00; }
        .gd-stack[data-on="0"] .gd-b0 { translate: 40px -22px; }
        .gd-stack[data-on="1"] .gd-b1 { translate: -8px -26px; }
        .gd-stack[data-on="2"] .gd-b2 { translate: -60px -22px; }
        @keyframes gdFloat { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-8px) } }
        @media (prefers-reduced-motion: reduce) { .gd-book { animation: none; transition: none; } }
        .hero-bar { animation: heroBar 7s linear forwards; }
        @keyframes heroBar { from { width: 0 } to { width: 100% } }
        .hero-mark { animation: heroFloat 6s ease-in-out infinite; }
        @keyframes heroFloat { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-10px) } }
        @media (prefers-reduced-motion: reduce) { .hero-mark, .hero-bar { animation: none !important; } .hero-bar { width: 100%; } }
        /* ---------- small card loops (8s) ---------- */
        .lp-loop * { animation-duration: 8s; animation-iteration-count: infinite; }
        .c-line { opacity: 0; animation-name: cLine; }
        .c-line:nth-child(2) { animation-delay: .6s; } .c-line:nth-child(3) { animation-delay: 1.2s; }
        @keyframes cLine { 0%,5% { opacity: 0; transform: translateX(-6px); } 12%,88% { opacity: 1; transform: none; } 95%,100% { opacity: 0; } }
        .c-click { animation-name: cClick; }
        @keyframes cClick { 0%,30% { background: #fff; color: #4338ca; } 34%,88% { background: #059669; color: #fff; border-color: #059669; } 95%,100% { background: #fff; color: #4338ca; } }
        .c-a { animation-name: cA; } .c-b { opacity: 0; animation-name: cB; }
        @keyframes cA { 0%,34% { opacity: 1; } 38%,92% { opacity: 0; } 96%,100% { opacity: 1; } }
        @keyframes cB { 0%,34% { opacity: 0; } 38%,92% { opacity: 1; } 96%,100% { opacity: 0; } }
        .c-pay { animation-name: cPay; }
        @keyframes cPay { 0%,20% { width: 0%; } 40%,55% { width: 40%; } 75%,90% { width: 100%; } 100% { width: 0%; } }
        .c-paid { opacity: 0; animation-name: cPaid; }
        @keyframes cPaid { 0%,74% { opacity: 0; } 78%,92% { opacity: 1; } 100% { opacity: 0; } }
        .c-toggle { animation-name: cToggle; }
        @keyframes cToggle { 0%,35% { background: #4f46e5; } 40%,85% { background: #cbd5e1; } 90%,100% { background: #4f46e5; } }
        .c-knob { animation-name: cKnob; }
        @keyframes cKnob { 0%,35% { transform: translateX(14px); } 40%,85% { transform: translateX(0); } 90%,100% { transform: translateX(14px); } }
        [dir=rtl] .c-knob { animation-name: cKnobR; }
        @keyframes cKnobR { 0%,35% { transform: translateX(-14px); } 40%,85% { transform: translateX(0); } 90%,100% { transform: translateX(-14px); } }
        .c-bar { transform-origin: bottom; animation-name: cBar; }
        .c-bar:nth-child(2) { animation-delay: .2s; } .c-bar:nth-child(3) { animation-delay: .4s; } .c-bar:nth-child(4) { animation-delay: .6s; } .c-bar:nth-child(5) { animation-delay: .8s; } .c-bar:nth-child(6) { animation-delay: 1s; }
        @keyframes cBar { 0%,5% { transform: scaleY(.08); } 22%,88% { transform: scaleY(1); } 100% { transform: scaleY(.08); } }
        .c-lang-fr { animation-name: cA; } .c-lang-ar { opacity: 0; animation-name: cB; }
        .lp-marquee { animation: lpMarquee 38s linear infinite; }
        [dir=rtl] .lp-marquee { animation-name: lpMarqueeR; }
        @keyframes lpMarquee { to { transform: translateX(-50%); } }
        @keyframes lpMarqueeR { to { transform: translateX(50%); } }
        .lp-shot { display: none; } .lp-shot.on { display: block; animation: lpFade .5s ease; }
        /* scanner IA section (2026-11-24) */
        @keyframes scBeam { 0%,3% { top: 0; opacity: 0; } 5% { opacity: 1; } 27% { top: calc(100% - 4px); opacity: 1; } 30%,100% { top: calc(100% - 4px); opacity: 0; } }
        @keyframes scRow { 0%,28% { opacity: 0; transform: translateY(6px); } 33%,93% { opacity: 1; transform: none; } 98%,100% { opacity: 0; } }
        @keyframes scTag { 0%,26% { opacity: 1; } 29%,100% { opacity: 0; } }
        @keyframes scTag2 { 0%,26% { opacity: 0; } 29%,93% { opacity: 1; } 98%,100% { opacity: 0; } }
        .sc-beam { position: absolute; left: -6px; right: -6px; height: 4px; background: #ffcc00; box-shadow: 0 0 0 1px #111, 0 0 18px 6px rgba(255,204,0,.55); animation: scBeam 9s infinite; }
        .sc-row { animation: scRow 9s infinite; animation-delay: calc(var(--i) * .35s); }
        .sc-tag1 { animation: scTag 9s infinite; } .sc-tag2 { animation: scTag2 9s infinite; }
        @media (prefers-reduced-motion: reduce) {
            .sc-beam, .sc-tag1 { animation: none !important; opacity: 0 !important; }
            .sc-row, .sc-tag2 { animation: none !important; opacity: 1 !important; }
        }
        @keyframes lpFade { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) {
            .lp-loop *, .lp-marquee { animation: none !important; }
        }

        /* industrial colours for the shared loops */
        @keyframes cClick { 0%,30% { background: #fff; color: #111; } 34%,88% { background: #ffcc00; color: #111; } 95%,100% { background: #fff; color: #111; } }
        @keyframes cToggle { 0%,35% { background: #111; } 40%,85% { background: #cfcac0; } 90%,100% { background: #111; } }
    </style>
@include('partials.analytics')
</head>
<body class="antialiased sp-ind text-zinc-900" x-data="{ menu: false }">

{{-- ============ HEADER (solid, no glass) ============ --}}
<header class="fixed top-0 inset-x-0 z-40 text-white border-b transition-colors duration-300" x-data="{ sc: false }" x-init="sc = window.scrollY > 40" @scroll.window="sc = window.scrollY > 40" :class="(sc || menu) ? 'bg-[#0d0d0e]/90 backdrop-blur border-white/10' : 'bg-transparent border-transparent'">
    <div class="max-w-7xl mx-auto h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
        <a href="/" class="flex items-center gap-3 shrink-0">
            <span class="grid place-items-center w-9 h-9 rounded-lg bg-[#ffcc00] text-zinc-900"><x-sp-icon name="cube" class="w-5 h-5" /></span>
            <span class="text-base font-medium">Stokito</span>
            <span class="hidden 2xl:inline ind-mono text-[11px] text-white/50 border border-white/20 px-1.5 py-0.5 rounded-lg">v2026</span>
        </a>
        <nav class="hidden xl:flex items-center gap-6 whitespace-nowrap text-sm font-medium text-white/75">
            <a href="#parcours" class="hidden 2xl:inline hover:text-white">{{ __('Comment ça marche') }}</a>
            <a href="#fonctionnalites" class="hover:text-white">{{ __('Fonctionnalités') }}</a>
            <a href="#scanner" class="hover:text-white">{{ __('Scanner IA') }}</a>
            <a href="#copilot" class="hover:text-white">{{ __('Copilot') }}</a>
            <a href="#caisse" class="hover:text-white">{{ __('Caisse') }}</a>
            <a href="#ecrans" class="hover:text-white">{{ __('Écrans') }}</a>
            <a href="#guide" class="hover:text-white">{{ __('Guide') }}</a>
            <a href="#faq" class="hover:text-white">{{ __('Questions') }}</a>
            <a href="{{ route('blog.index') }}" class="hover:text-white">{{ __('Blog') }}</a>
        </nav>
        <div class="flex items-center gap-2">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" type="button" class="h-9 px-2.5 border border-white/20 text-xs font-medium text-white/80 hover:bg-white/10 rounded-lg">{{ strtoupper($locale) }}</button>
                <div x-show="open" @click.outside="open = false" x-cloak class="absolute {{ $rtl ? 'left-0' : 'right-0' }} mt-2 w-36 bg-white text-zinc-900 border border-zinc-200 text-sm rounded-lg">
                    <a href="{{ route('locale.switch', 'fr') }}" class="block px-3 py-2 hover:bg-zinc-100">Français</a>
                    <a href="{{ route('locale.switch', 'ar') }}" class="block px-3 py-2 hover:bg-zinc-100">العربية</a>
                    <a href="{{ route('locale.switch', 'en') }}" class="block px-3 py-2 hover:bg-zinc-100">English</a>
                </div>
            </div>
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="hidden sm:flex h-9 px-3 items-center gap-1.5 border border-white/20 text-sm font-semibold hover:border-[#25d366] hover:text-[#25d366] rounded-lg">{!! $waIcon !!} <span class="hidden 2xl:inline">WhatsApp</span></a>
            @guest<a href="{{ route('login') }}" class="flex h-9 px-2.5 sm:px-3 items-center whitespace-nowrap border border-white/20 text-sm font-medium hover:bg-white/10 rounded-lg">{{ __('Se connecter') }}</a>@endguest
            <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="{{ auth()->check() ? 'flex' : 'hidden sm:flex' }} h-9 px-3 sm:px-4 items-center whitespace-nowrap bg-[#ffcc00] text-zinc-900 text-sm font-semibold hover:bg-[#ffd633] rounded-lg">@auth<span class="sm:hidden">{{ __('Mon espace') }}</span><span class="hidden sm:inline">{{ __('Tableau de bord') }}</span>@else{{ __('Créer un compte gratuit') }}@endauth</a>
            <button type="button" @click="menu = !menu" class="xl:hidden p-2 border border-white/20 rounded-lg" aria-label="Menu">
                <x-sp-icon name="menu" class="w-6 h-6" x-show="!menu" /><x-sp-icon name="x" class="w-6 h-6" x-show="menu" x-cloak />
            </button>
        </div>
    </div>
    <div x-show="menu" x-cloak @click="menu = false" class="xl:hidden border-t border-white/10 px-4 pb-4 pt-2 text-base font-medium">
        <a href="#parcours" class="block py-2.5 border-b border-white/10">{{ __('Comment ça marche') }}</a>
        <a href="#fonctionnalites" class="block py-2.5 border-b border-white/10">{{ __('Fonctionnalités') }}</a>
        <a href="#scanner" class="block py-2.5 border-b border-white/10">{{ __('Scanner IA') }}</a>
        <a href="#copilot" class="block py-2.5 border-b border-white/10">{{ __('Copilot') }}</a>
        <a href="#caisse" class="block py-2.5 border-b border-white/10">{{ __('Caisse') }}</a>
        <a href="#ecrans" class="block py-2.5 border-b border-white/10">{{ __('Écrans') }}</a>
        <a href="#guide" class="block py-2.5 border-b border-white/10">{{ __('Guide') }}</a>
        <a href="#faq" class="block py-2.5 border-b border-white/10">{{ __('Questions') }}</a>
        <a href="{{ route('blog.index') }}" class="block py-2.5 border-b border-white/10">{{ __('Blog') }}</a>
        @guest<a href="{{ route('register') }}" class="mt-3 flex h-12 items-center justify-center rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold">{{ __('Créer un compte gratuit') }}</a>@endguest
        <div class="grid grid-cols-2 gap-2 pt-3 font-sans text-sm">
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="flex h-11 items-center justify-center gap-1.5 border border-white/20 rounded-lg">{!! $waIcon !!} WhatsApp</a>
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="flex h-11 items-center justify-center rounded-lg bg-white text-zinc-900 font-semibold">{{ auth()->check() ? __('Tableau de bord') : __('Se connecter') }}</a>
        </div>
    </div>
</header>

<a href="{{ $wa }}" target="_blank" rel="noopener" aria-label="WhatsApp" class="fixed bottom-5 {{ $rtl ? 'left-5' : 'right-5' }} z-40 h-14 w-14 grid place-items-center bg-[#25d366] text-white border border-zinc-200 shadow-md shadow-zinc-900/5 rounded-lg"><span class="[&>svg]:h-7 [&>svg]:w-7">{!! $waIcon !!}</span></a>

{{-- ============ HERO: slider (2026-11-25, after the user's reference: dark band, big title, product visual in brand rings, timed tabs) ============ --}}
@php
    $heroSlides = [
        ['tab' => __('Stock et factures'), 'eyebrow' => __('Logiciel de gestion de stock en ligne'), 'title' => __('Votre stock, vos devis et vos factures.'), 'accent' => __('Au même endroit.'),
         'text' => __('Stokito suit chaque produit du devis jusqu’à l’encaissement. Le stock se met à jour tout seul, vos factures portent vos identifiants, la TVA et le montant en lettres, et vous relancez vos clients sur WhatsApp.'),
         'cta' => [route('register'), __('Créer un compte gratuit')], 'cta2' => [route('demo.login'), __('Tester la démo')],
         'img' => 'screen-dashboard', 'w' => 1600, 'h' => 996, 'url' => 'stokito.com/dashboard', 'badge' => [__('CA du mois'), '1 104,66 €']],
        ['tab' => __('Caisse'), 'eyebrow' => __('Caisse'), 'title' => __('Une caisse pour le comptoir, reliée au stock.'), 'accent' => null,
         'text' => __('Scannez, encaissez en espèces, par carte ou par chèque : le rendu de monnaie se calcule et le ticket s’imprime. Chaque vente sort du stock et compte dans votre tableau de bord.'),
         'cta' => [route('seo.caisse'), __('Essayer la caisse')], 'cta2' => null,
         'img' => 'screen-caisse-pos', 'w' => 1272, 'h' => 800, 'url' => 'stokito.com/caisse', 'badge' => ['Total TTC', '61,00 €']],
        ['tab' => __('Scanner IA'), 'eyebrow' => __('Scanner IA'), 'title' => __('Une photo de la facture. Le bon de commande se remplit.'), 'accent' => null,
         'text' => __('Prenez en photo la facture de votre fournisseur, ou choisissez le PDF. Stokito lit le fournisseur, la date, le numéro et chaque ligne, puis retrouve vos produits. Vous vérifiez, vous enregistrez : à la réception, le stock monte tout seul.'),
         'cta' => [route('seo.scanner-facture'), __('En savoir plus')], 'cta2' => null,
         'img' => 'screen-scanner-facture', 'w' => 992, 'h' => 1092, 'url' => 'stokito.com/commande-fournisseurs', 'badge' => ['FA-24-11872', '✓ 4 / 4']],
    ];
@endphp
<section class="hero-bg relative overflow-hidden text-white"
    x-data="{ i: 0, n: {{ count($heroSlides) }}, t: null,
        start() { clearInterval(this.t); if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return; this.t = setInterval(() => this.i = (this.i + 1) % this.n, 7000) },
        go(k) { this.i = k; this.start() } }"
    x-init="start()" @mouseenter="clearInterval(t)" @mouseleave="start()">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0">
        
        <div class="absolute bottom-24 {{ $rtl ? 'left-6' : 'right-6' }} w-64 h-40 hero-dots hidden sm:block"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 pt-28 sm:pt-32 pb-10">
        <div class="grid">
            @foreach ($heroSlides as $k => $s)
                <div class="[grid-area:1/1] grid lg:grid-cols-12 gap-10 lg:gap-12 items-center transition-all duration-700"
                    :class="i === {{ $k }} ? 'opacity-100 visible translate-y-0' : 'opacity-0 invisible translate-y-3'"
                    @if ($k > 0) x-cloak @endif>
                    <div class="lg:col-span-6">
                        <p class="text-sm font-semibold text-[#ffcc00]">{{ $s['eyebrow'] }}</p>
                        @if ($k === 0)
                            <h1 class="mt-4 hero-title text-4xl sm:text-5xl xl:text-6xl">{{ $s['title'] }} @if ($s['accent'])<span class="text-[#ffcc00]">{{ $s['accent'] }}</span>@endif</h1>
                        @else
                            <h2 class="mt-4 hero-title text-4xl sm:text-5xl xl:text-6xl">{{ $s['title'] }}</h2>
                        @endif
                        <p class="mt-6 text-lg text-zinc-300 max-w-xl leading-relaxed">{{ $s['text'] }}</p>
                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            <a href="{{ $s['cta'][0] }}" class="inline-flex items-center gap-2 h-12 px-6 rounded-lg bg-[#ffcc00] text-zinc-900 font-semibold shadow-[0_4px_0_#c99f00] hover:translate-y-px hover:shadow-[0_3px_0_#c99f00] transition">{{ $s['cta'][1] }} <span aria-hidden="true">{{ $rtl ? '←' : '→' }}</span></a>
                            @if ($s['cta2'])<a href="{{ $s['cta2'][0] }}" class="inline-flex items-center h-12 px-6 rounded-lg border border-white/25 text-white font-semibold hover:bg-white/10 transition">{{ $s['cta2'][1] }}</a>@endif
                        </div>
                    </div>
                    <div class="lg:col-span-6" aria-hidden="true">
                        <div class="relative mx-auto w-full max-w-[400px] lg:max-w-[580px] aspect-square">
                            <svg class="hero-mark absolute inset-0 w-full h-full" viewBox="0 0 24 24" fill="none" stroke-linejoin="round" stroke-linecap="round">
                                <path d="M12 1.2 2.4 6.3v11.4l9.6 5.1 9.6-5.1V6.3z" fill="#1c1c20"/>
                                <path d="M12 1.2 2.4 6.3v11.4l9.6 5.1 9.6-5.1V6.3z" stroke="#ffcc00" stroke-width="1.1"/>
                                <path d="M2.4 6.3 12 11.4l9.6-5.1M12 11.4v11.4" stroke="#ffcc00" stroke-opacity=".45" stroke-width=".55"/>
                            </svg>
                            <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-full lg:w-[96%] rounded-xl bg-white shadow-2xl shadow-black/50 ring-1 ring-white/10 overflow-hidden {{ $k % 2 ? 'rotate-[1.5deg]' : '-rotate-[1.5deg]' }}">
                                <div class="h-7 flex items-center gap-1.5 px-3 bg-zinc-100 border-b border-zinc-200">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#ff5f57]"></span><span class="w-2.5 h-2.5 rounded-full bg-[#febc2e]"></span><span class="w-2.5 h-2.5 rounded-full bg-[#28c840]"></span>
                                    <span class="ms-3 text-[11px] text-zinc-500 truncate" dir="ltr">{{ $s['url'] }}</span>
                                </div>
                                <img src="/images/screens/{{ $s['img'] }}.webp?v=20261125m" width="{{ $s['w'] }}" height="{{ $s['h'] }}" alt="" class="block w-full aspect-[16/10] object-cover object-left-top" @if ($k > 0) loading="lazy" @endif>
                            </div>
                            <div class="absolute bottom-[9%] {{ $rtl ? 'right-0' : 'left-0' }} rounded-xl bg-white text-zinc-900 shadow-xl px-4 py-3 flex items-center gap-3">
                                <span class="grid place-items-center w-9 h-9 rounded-lg bg-[#ffcc00]"><x-sp-icon name="chart" class="w-5 h-5" /></span>
                                <span><span class="block text-xs text-zinc-500">{{ $s['badge'][0] }}</span><span class="block font-bold tabular-nums" dir="ltr">{{ $s['badge'][1] }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{-- timed tabs --}}
        <div class="mt-10 grid grid-cols-3 gap-3 max-w-lg" role="tablist">
            @foreach ($heroSlides as $k => $s)
                <button type="button" role="tab" @click="go({{ $k }})" :aria-selected="i === {{ $k }}" class="text-start group">
                    <span class="block h-1 rounded-full bg-white/15 overflow-hidden"><span class="block h-full bg-[#ffcc00] w-0" :class="i === {{ $k }} ? 'hero-bar' : (i > {{ $k }} ? 'w-full' : 'w-0')"></span></span>
                    <span class="mt-2 block text-sm font-medium transition-colors" :class="i === {{ $k }} ? 'text-white' : 'text-zinc-400 group-hover:text-zinc-200'">{{ $s['tab'] }}</span>
                </button>
            @endforeach
        </div>
        <a href="#scanner" class="mt-8 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-sm text-zinc-200 hover:bg-white/10">
            <span class="text-[10px] font-semibold rounded-full bg-[#ffcc00] text-zinc-900 px-2 py-0.5">{{ __('NOUVEAU') }}</span>
            {{ __('Scannez vos factures fournisseurs') }} <span aria-hidden="true">{{ $rtl ? '←' : '→' }}</span>
        </a>
    </div>
    {{-- sectors marquee, inside the hero (white, see-through) --}}
    <div class="relative border-t border-white/10 py-4 overflow-hidden">
        <div class="flex w-max lp-marquee">
            @foreach ([1, 2] as $copy)
                <div class="flex items-center gap-8 px-4">
                    @foreach ($sectors as $sec)
                        <span class="flex items-center gap-8 whitespace-nowrap text-base font-medium text-white/50">{{ __($sec) }}<span class="text-white/25">·</span></span>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ HOW IT WORKS: 4 stations ============ --}}
<section id="parcours" class="py-20 sm:py-24 scroll-mt-20 bg-zinc-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-6 border-b border-zinc-200 pb-6">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold text-amber-600">{{ __('Comment ça marche') }}</p>
                <h2 class="mt-2 ind-h text-3xl sm:text-4xl leading-tight">{{ __('Du devis à l’encaissement, en 4 étapes') }}</h2>
            </div>
            <p class="max-w-sm text-zinc-600">{{ __('Chaque document reprend le précédent : vous ne retapez jamais les mêmes lignes.') }}</p>
        </div>
        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 border border-zinc-200 bg-white lp-loop rounded-lg">
            {{-- 1 --}}
            <div class="p-5 border-b sm:border-e lg:border-b-0 border-zinc-200">
                <div class="flex items-baseline justify-between"><span class="grid place-items-center w-9 h-9 rounded-full bg-zinc-900 text-white text-sm font-semibold">01</span></div>
                <div class="mt-4 h-32 border border-zinc-200 bg-zinc-50 p-2.5 text-[11px] rounded-lg" dir="ltr">
                    <div class="ind-mono text-[10px]">DEV-00042</div>
                    <div>
                        <div class="c-line flex justify-between border-b border-zinc-200 py-1"><span>Fer rond Ø10 × 50</span><b class="ind-mono">360,00</b></div>
                        <div class="c-line flex justify-between border-b border-zinc-200 py-1"><span>Tube carré 40 × 12</span><b class="ind-mono">222,00</b></div>
                        <div class="c-line flex justify-between py-1"><span>Antirouille 4 L × 6</span><b class="ind-mono">197,40</b></div>
                    </div>
                </div>
                <h3 class="mt-4 ind-h text-lg">{{ __('Le devis') }}</h3>
                <p class="mt-1 text-sm text-zinc-600">{{ __('Tapez le nom du produit, la quantité : le prix HT, la TVA et le total se calculent seuls. Tarif normal ou tarif de gros.') }}</p>
            </div>
            {{-- 2 --}}
            <div class="p-5 border-b lg:border-b-0 lg:border-e border-zinc-200">
                <div class="flex items-baseline justify-between"><span class="grid place-items-center w-9 h-9 rounded-full bg-zinc-900 text-white text-sm font-semibold">02</span></div>
                <div class="mt-4 h-32 border border-zinc-200 bg-zinc-50 p-2.5 text-[11px] flex flex-col justify-between rounded-lg" dir="ltr">
                    <div class="flex items-center justify-between"><span class="ind-mono text-[10px]">BL-00031</span><span class="c-click border border-zinc-200 px-2 py-0.5 ind-h text-xs rounded-lg">MARQUER LIVRÉ</span></div>
                    <div class="border border-amber-200 bg-amber-50 p-2 rounded-lg">
                        <div class="ind-mono text-[10px]">STOCK · FER RD Ø10</div>
                        <div class="relative h-6 ind-h text-xl"><span class="c-a absolute">310</span><span class="c-b absolute">260 <span class="ind-mono text-xs">−50</span></span></div>
                    </div>
                </div>
                <h3 class="mt-4 ind-h text-lg">{{ __('La livraison') }}</h3>
                <p class="mt-1 text-sm text-zinc-600">{{ __('Un clic transforme le devis en bon de livraison. Quand il passe en « Livré », le stock baisse automatiquement.') }}</p>
            </div>
            {{-- 3 --}}
            <div class="p-5 border-b sm:border-b-0 sm:border-e border-zinc-200">
                <div class="flex items-baseline justify-between"><span class="grid place-items-center w-9 h-9 rounded-full bg-zinc-900 text-white text-sm font-semibold">03</span></div>
                <div class="mt-4 h-32 border border-zinc-200 bg-zinc-50 p-2.5 text-[10px] leading-4 rounded-lg" dir="ltr">
                    <div class="mx-auto w-40 h-full bg-white border border-zinc-200 p-2 relative rounded-lg">
                        <div class="flex justify-between"><b>FACTURE</b><span class="ind-mono text-zinc-500">FAC-00058</span></div>
                        <div class="ind-mono text-[9px] text-zinc-500">ICE · IF · RC · TP</div>
                        <div class="mt-1 h-0.5 bg-zinc-900"></div>
                        <div class="mt-1 flex justify-between ind-mono"><span>TVA 20 %</span><b>155,88</b></div>
                        <div class="flex justify-between ind-mono"><span>TTC</span><b>935,28 €</b></div>
                        <div class="mt-0.5 italic text-zinc-500">« six mille huit cent… »</div>
                        <div class="absolute right-1 bottom-1 rotate-[-10deg] border border-[#c62828] text-[#c62828] ind-h text-[9px] px-1 rounded-lg">CACHET</div>
                    </div>
                </div>
                <h3 class="mt-4 ind-h text-lg">{{ __('La facture') }}</h3>
                <p class="mt-1 text-sm text-zinc-600">{{ __('Facture PDF avec votre logo, vos identifiants fiscaux, la TVA détaillée et le montant en lettres.') }}</p>
            </div>
            {{-- 4 --}}
            <div class="p-5">
                <div class="flex items-baseline justify-between"><span class="grid place-items-center w-9 h-9 rounded-full bg-zinc-900 text-white text-sm font-semibold">04</span></div>
                <div class="mt-4 h-32 border border-zinc-200 bg-zinc-50 p-2.5 text-[11px] flex flex-col justify-between rounded-lg" dir="ltr">
                    <div>
                        <div class="flex justify-between"><span class="ind-mono text-[10px]">FAC-00058 · REÇU</span><span class="c-paid bg-[#0a7a3e] text-white ind-h text-[11px] px-1.5">PAYÉE</span></div>
                        <div class="mt-2 h-3 border border-zinc-200 bg-white rounded-lg"><div class="c-pay h-full bg-zinc-900"></div></div>
                        <div class="mt-1 ind-mono text-[10px] text-zinc-500">CHÈQUE · VIREMENT · ESPÈCES</div>
                    </div>
                    <div class="self-end border border-zinc-200 bg-[#dcf8c6] px-2 py-1 text-[10.5px] rounded-lg">Rappel FAC-00058 <span class="text-[#1d8ad6] font-bold">✓✓</span></div>
                </div>
                <h3 class="mt-4 ind-h text-lg">{{ __('L’encaissement') }}</h3>
                <p class="mt-1 text-sm text-zinc-600">{{ __('Enregistrez chaque paiement. Pour un retard, le bouton « Relancer » prépare le message WhatsApp pour vous.') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ============ FEATURES: rack of modules ============ --}}
<section id="fonctionnalites" class="py-20 sm:py-24 scroll-mt-20 bg-zinc-50 border-y border-zinc-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="border-b border-zinc-200 pb-6">
            <p class="text-sm font-semibold text-amber-600">{{ __('Fonctionnalités') }}</p>
            <h2 class="mt-2 ind-h text-3xl sm:text-4xl leading-tight max-w-3xl">{{ __('Ce qu’il faut pour un commerce, sans le superflu') }}</h2>
        </div>
        <div class="mt-10 grid md:grid-cols-6 border border-zinc-200 bg-white lp-loop rounded-lg">
            <div class="md:col-span-3 p-6 border-b md:border-e border-zinc-200">
                <h3 class="mt-2 ind-h text-xl">{{ __('Stock toujours à jour') }}</h3>
                <p class="mt-2 text-sm text-zinc-600">{{ __('Entrées à la réception des commandes, sorties à la livraison, alerte quand un produit passe sous son seuil.') }}</p>
                <div class="mt-5 space-y-3 text-xs" dir="ltr">
                    @foreach ([['FER-RD-10', 'Fer rond Ø10', '260', 72, 'bg-zinc-900'], ['TUB-CAR-60', 'Tube carré 60×60', '12 / 20', 38, 'bg-[#ffcc00]'], ['VANNE-1/2', 'Vanne laiton 1/2"', '0 / 10', 4, 'bg-[#c62828]']] as [$r, $n, $q, $w, $c])
                        <div><div class="flex justify-between"><span><span class="ind-mono text-[10px] text-zinc-400 me-2">{{ $r }}</span>{{ $n }}</span><b class="ind-mono">{{ $q }}</b></div><div class="mt-1 h-2.5 border border-zinc-200 rounded-lg"><div class="h-full {{ $c }}" style="width: {{ $w }}%"></div></div></div>
                    @endforeach
                </div>
            </div>
            <div class="md:col-span-3 p-6 border-b border-zinc-200">
                <h3 class="mt-2 ind-h text-xl">{{ __('Vos chiffres du mois') }}</h3>
                <p class="mt-2 text-sm text-zinc-600">{{ __('Chiffre d’affaires, encaissé, impayés et ventes des 6 derniers mois sur le tableau de bord.') }}</p>
                <div class="mt-5 h-24 flex items-end gap-2 border-b border-zinc-200" dir="ltr">
                    @foreach ([46, 62, 46, 80, 92, 34] as $h)
                        <div class="c-bar flex-1 {{ $loop->last ? 'bg-[#ffcc00]' : 'bg-zinc-800' }} rounded-md" style="height: {{ $h }}%"></div>
                    @endforeach
                </div>
                <div class="mt-1 flex justify-between ind-mono text-[10px] text-zinc-500" dir="ltr"><span>JUIN</span><span>JUIL.</span><span>AOÛT</span><span>SEPT.</span><span>OCT.</span><span>NOV.</span></div>
            </div>
            <div class="md:col-span-2 p-6 border-b md:border-b-0 md:border-e border-zinc-200">
                <h3 class="mt-2 ind-h text-lg">{{ __('Inventaire') }}</h3>
                <p class="mt-2 text-sm text-zinc-600">{{ __('Comptez, Stokito montre les écarts et corrige le stock.') }}</p>
                <div class="mt-4 border border-zinc-200 text-xs ind-mono rounded-lg" dir="ltr">
                    <div class="flex justify-between px-3 py-2 border-b border-zinc-200"><span>DISQ-115</span><span>500 → 480 <b class="text-[#c62828]">−20</b></span></div>
                    <div class="flex justify-between px-3 py-2"><span>COR-40</span><span>140 → 140 <b class="text-[#0a7a3e]">OK</b></span></div>
                </div>
            </div>
            <div class="md:col-span-2 p-6 border-b md:border-b-0 md:border-e border-zinc-200">
                <h3 class="mt-2 ind-h text-lg">{{ __('Équipe et permissions') }}</h3>
                <p class="mt-2 text-sm text-zinc-600">{{ __('Chaque employé son compte. Cachez les prix d’achat et les marges aux vendeurs.') }}</p>
                <div class="mt-4 space-y-2 text-xs" dir="ltr">
                    <div class="flex items-center justify-between border border-zinc-200 px-3 py-2 rounded-lg"><span>Devis : Créer</span><span class="w-8 h-4 bg-zinc-900 relative"><span class="absolute top-0.5 left-0.5 w-3 h-3 bg-[#ffcc00] translate-x-[14px]"></span></span></div>
                    <div class="flex items-center justify-between border border-zinc-200 px-3 py-2 rounded-lg"><span>Voir prix d'achat</span><span class="c-toggle w-8 h-4 relative"><span class="c-knob absolute top-0.5 left-0.5 w-3 h-3 bg-white"></span></span></div>
                </div>
            </div>
            <div class="md:col-span-2 p-6">
                <h3 class="mt-2 ind-h text-lg">{{ __('En français et en arabe') }}</h3>
                <p class="mt-2 text-sm text-zinc-600">{{ __('Chaque utilisateur choisit sa langue ; l’arabe s’affiche de droite à gauche.') }}</p>
                <div class="mt-4 relative h-14 border border-zinc-200 bg-zinc-50 rounded-lg">
                    <div class="c-lang-fr absolute inset-0 flex items-center px-4 font-semibold" dir="ltr">Tableau de bord · Ventes</div>
                    <div class="c-lang-ar absolute inset-0 flex items-center px-4 font-semibold" dir="rtl" style="font-family:Tajawal,sans-serif">لوحة القيادة · المبيعات</div>
                </div>
            </div>
        </div>
        {{-- MOD-06: country rules, Maroc + France tabs (2026-11-23) --}}
        <div class="mt-6 bg-zinc-900 text-white p-6 sm:p-8" x-data="{ c: 'fr' }">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-amber-300">{{ __('Conformité') }}</p>
                    <h3 class="mt-1 ind-h text-xl">{{ __('Fait pour les règles de la France et du Maroc') }}</h3>
                </div>
                <div class="flex" role="tablist">
                    <button type="button" role="tab" @click="c = 'fr'" :aria-selected="c === 'fr'" class="ind-h px-4 py-1.5 border border-amber-300 -me-0.5 rounded-lg" :class="c === 'fr' ? 'bg-[#ffcc00] text-zinc-900' : 'text-amber-300 hover:bg-zinc-800'">{{ __('France') }}</button>
                    <button type="button" role="tab" @click="c = 'ma'" :aria-selected="c === 'ma'" class="ind-h px-4 py-1.5 border border-amber-300 rounded-lg" :class="c === 'ma' ? 'bg-[#ffcc00] text-zinc-900' : 'text-amber-300 hover:bg-zinc-800'">{{ __('Maroc') }}</button>
                </div>
            </div>
            @foreach ([
                'fr' => [__('SIRET, N° TVA intracommunautaire et RCS sur vos documents, SIRET ou N° TVA du client, TVA à 20 % réglable, montants et montant en lettres en euros, conditions de paiement en pied de facture.'),
                         ['SIRET', 'N° TVA', 'RCS', 'TVA 20 %', 'EUR', __('Montant en lettres'), __('Avec ou sans en-tête')]],
                'ma' => [__('ICE, IF, RC, Taxe professionnelle et CNSS sur vos documents, ICE du client, TVA par défaut réglable, montant en lettres en dirhams, conditions de paiement en pied de facture.'),
                         ['ICE', 'IF', 'RC', 'TP', 'CNSS', 'TVA 20 %', 'MAD', __('Montant en lettres'), __('Avec ou sans en-tête')]],
            ] as $code => [$txt, $chips])
                <div x-show="c === '{{ $code }}'" @if ($code !== 'fr') x-cloak @endif class="mt-5 grid md:grid-cols-2 gap-6 items-center">
                    <p class="text-sm text-zinc-300">{{ $txt }}</p>
                    <div class="flex flex-wrap gap-2" dir="ltr">
                        @foreach ($chips as $chip)
                            <span class="border border-amber-300 text-amber-300 ind-mono text-sm px-3 py-1 rounded-lg">{{ $chip }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <p class="mt-5 pt-4 border-t border-zinc-800 ind-mono text-[11px] text-zinc-400">{{ __('Aussi : Belgique, Suisse, Luxembourg, Sénégal, Côte d’Ivoire, Cameroun, Tunisie, Algérie. Devise, TVA et identifiants s’adaptent au pays choisi.') }}</p>
        </div>
    </div>
</section>

{{-- ============ SCANNER IA (2026-11-24) ============ --}}
<section id="scanner" class="py-20 sm:py-24 scroll-mt-20 bg-zinc-50 border-b border-zinc-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-12 gap-12 items-center">
        <div class="lg:col-span-5">
            <p class="text-sm font-semibold text-amber-600"><span class="rounded-full bg-[#ffcc00] text-zinc-900 px-2 py-0.5 me-1 text-[11px]">{{ __('NOUVEAU') }}</span> {{ __('Scanner IA') }}</p>
            <h2 class="mt-3 ind-h text-3xl sm:text-4xl leading-tight">{{ __('Une photo de la facture. Le bon de commande se remplit.') }}</h2>
            <p class="mt-5 text-zinc-700">{{ __('Prenez en photo la facture de votre fournisseur, ou choisissez le PDF. Stokito lit le fournisseur, la date, le numéro et chaque ligne, puis retrouve vos produits. Vous vérifiez, vous enregistrez : à la réception, le stock monte tout seul.') }}</p>
            <ul class="mt-6 space-y-2.5 text-sm">
                @foreach ([
                    __('Fournisseur reconnu par son SIRET, son ICE ou son nom'),
                    __('Produits retrouvés par référence, code-barres ou désignation'),
                    __('Produit inconnu : créé en un clic, déjà prérempli'),
                    __('Rien n’est enregistré sans votre validation'),
                ] as $pt)
                    <li class="flex gap-3"><span class="mt-0.5 grid place-items-center w-5 h-5 shrink-0 rounded-full bg-[#ffcc00] text-zinc-900 text-[11px] font-bold">✓</span><span>{{ $pt }}</span></li>
                @endforeach
            </ul>
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('register') }}" class="ind-btn bg-zinc-900 text-white">{{ __('Créer un compte gratuit') }} <span aria-hidden="true">{{ $rtl ? '←' : '→' }}</span></a>
                <a href="{{ route('seo.scanner-facture') }}" class="ind-btn bg-white text-zinc-900">{{ __('En savoir plus') }}</a>
            </div>
            <p class="mt-4 ind-mono text-[11px] text-zinc-500">{{ __('30 factures par mois incluses gratuitement · photo JPG, PNG ou PDF · 10 Mo max.') }}</p>
        </div>

        {{-- animation: the invoice is read, then the lines appear in the purchase order --}}
        <div class="lg:col-span-7 grid sm:grid-cols-[0.9fr_1.1fr] gap-5 items-center" dir="ltr" aria-hidden="true">
            <div class="relative bg-white border border-zinc-200 p-4 shadow-lg shadow-zinc-900/5 -rotate-1 text-[11px] leading-snug rounded-xl">
                <div class="sc-beam"></div>
                <div class="flex justify-between items-start">
                    <div><b class="text-[12px]">QUINCAILLERIE RHÔNE PRO</b><div class="text-zinc-500">SIRET 812 345 678 00027</div><div class="text-zinc-500">69007 Lyon</div></div>
                    <div class="text-right"><b class="ind-h text-lg">FACTURE</b><div class="ind-mono text-[10px]">FA-24-11872</div><div class="ind-mono text-[10px]">18/11/2026</div></div>
                </div>
                <div class="mt-3 border-t border-zinc-200 pt-1 space-y-1 ind-mono text-[10px]">
                    <div class="flex justify-between"><span>VIS-440 Vis inox 4x40</span><span>10 × 6,50</span></div>
                    <div class="flex justify-between"><span>CHV-08 Chevilles 8 mm</span><span>5 × 3,20</span></div>
                    <div class="flex justify-between"><span>PSF18 Perceuse 18V</span><span>1 × 89,00</span></div>
                    <div class="flex justify-between"><span>Frais de port</span><span>1 × 12,00</span></div>
                </div>
                <div class="mt-2 border-t border-zinc-200 pt-1 flex justify-between font-bold"><span>TOTAL HT</span><span>182,00 €</span></div>
                <div class="mt-3 relative h-4 ind-mono text-[10px]">
                    <span class="sc-tag1 absolute inset-0 text-zinc-500">photo.jpg · {{ __('lecture…') }}</span>
                    <span class="sc-tag2 absolute inset-0 text-[#0a7a3e] font-semibold">✓ {{ __('4 lignes lues') }}</span>
                </div>
            </div>

            <div class="bg-white border border-zinc-200 shadow-lg shadow-zinc-900/5 text-xs rounded-xl">
                <div class="bg-zinc-900 text-white ind-mono text-[10px] px-3 py-2 flex justify-between"><span>{{ __('BON DE COMMANDE') }} · CF-00031</span><span>N° FA-24-11872</span></div>
                <div class="px-3 py-2 border-b border-zinc-100 flex justify-between"><span class="text-zinc-500">{{ __('Fournisseur') }}</span><b>Quincaillerie Rhône Pro</b></div>
                @foreach ([
                    ['Vis inox 4x40 (boîte 100)', '10', '6,50', 'ok'],
                    ['Chevilles nylon 8 mm', '5', '3,20', 'ok'],
                    ['Perceuse sans fil 18V', '1', '89,00', 'check'],
                    ['Frais de port', '1', '12,00', 'new'],
                ] as $i => [$n, $q, $pu, $st])
                    <div class="sc-row px-3 py-2 border-b border-[#eee]" style="--i: {{ $i }}">
                        <div class="flex justify-between gap-2"><span class="font-medium truncate">{{ $n }}</span><span class="ind-mono shrink-0">{{ $q }} × {{ $pu }}</span></div>
                        @if ($st === 'ok')
                            <span class="ind-mono text-[10px] text-[#0a7a3e]">✓ {{ __('Reconnu') }}</span>
                        @elseif ($st === 'check')
                            <span class="ind-mono text-[10px] text-[#a15c00]">{{ __('À vérifier') }}</span>
                        @else
                            <span class="ind-mono text-[10px] bg-[#ffcc00] border border-zinc-200 px-1 rounded-lg">+ {{ __('Créer ce produit') }}</span>
                        @endif
                    </div>
                @endforeach
                <div class="px-3 py-2.5 flex items-center justify-between">
                    <b class="ind-mono">182,00 € HT</b>
                    <span class="bg-zinc-900 text-white ind-h text-sm px-3 py-1">{{ __('Enregistrer') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ COPILOT IA (2026-11-25) ============ --}}
<section id="copilot" class="py-20 sm:py-24 scroll-mt-20 bg-zinc-50 border-y border-zinc-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-12 gap-12 items-center">
        <div class="lg:col-span-5">
            <p class="text-sm font-semibold text-amber-600"><span class="rounded-full bg-[#ffcc00] text-zinc-900 px-2 py-0.5 me-1 text-[11px]">{{ __('NOUVEAU') }}</span> {{ __('Copilot IA') }}</p>
            <h2 class="mt-3 ind-h text-3xl sm:text-4xl leading-tight">{{ __('Demandez. Le Copilot prépare, vous validez.') }}</h2>
            <p class="mt-5 text-zinc-700">{{ __('Écrivez en français, en arabe ou en darija. Le Copilot répond avec vos propres chiffres et prépare vos documents à partir de vos clients et de votre catalogue.') }}</p>
            <ul class="mt-6 space-y-2.5 text-sm">
                @foreach ([
                    __('Devis, factures, bons de livraison et bons de commande préparés en une phrase'),
                    __('Vos ventes du jour ou du mois, les factures impayées, le stock bas'),
                    __('Rien n’est enregistré sans vous : le formulaire s’ouvre rempli, vous vérifiez'),
                    __('150 messages par mois inclus gratuitement'),
                ] as $pt)
                    <li class="flex gap-3"><span class="mt-0.5 grid place-items-center w-5 h-5 shrink-0 rounded-full bg-[#ffcc00] text-zinc-900 text-[11px] font-bold">✓</span><span>{{ $pt }}</span></li>
                @endforeach
            </ul>
            <div class="mt-8"><a href="{{ route('register') }}" class="ind-btn bg-zinc-900 text-white">{{ __('Créer un compte gratuit') }} <span aria-hidden="true">{{ $rtl ? '←' : '→' }}</span></a></div>
        </div>

        {{-- coded chat (fictional demo data) --}}
        <div class="lg:col-span-7" aria-hidden="true">
            <div class="max-w-xl ms-auto border border-zinc-200 bg-white shadow-2xl shadow-zinc-900/10 rounded-xl">
                <div class="flex items-center gap-3 px-4 py-3 bg-zinc-900 text-white">
                    <span class="grid place-items-center w-9 h-9 rounded-lg bg-[#ffcc00] text-zinc-900"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9z"/></svg></span>
                    <div><div class="ind-h text-lg leading-none">Copilot Stokito</div><div class="ind-mono text-[10px] text-zinc-400">FR · العربية · darija</div></div>
                </div>
                <div class="p-4 space-y-3 text-[13.5px]" dir="ltr">
                    <div class="sc-row flex justify-end" style="--i: 0"><div class="max-w-[85%] bg-zinc-900 text-white px-3 py-2">Devis pour Bâti Plus : 50 fer rond Ø10</div></div>
                    <div class="sc-row" style="--i: 2">
                        <div class="max-w-[90%] border border-zinc-200 bg-zinc-50 px-3 py-2.5 rounded-lg">
                            <div class="font-semibold">Devis préparé pour Bâti Plus SARL</div>
                            <div class="ind-mono text-[12px] text-zinc-600 mt-0.5">50 × Fer rond à béton Ø10 · 6,83 € HT</div>
                            <div class="ind-mono text-[12px] mt-0.5"><b>341,50 € HT</b> · 409,80 € TTC</div>
                            <span class="mt-2 inline-block border border-zinc-200 bg-[#ffcc00] px-2.5 py-1 text-[12px] font-bold rounded-lg">Ouvrir le devis pré-rempli →</span>
                        </div>
                    </div>
                    <div class="sc-row flex justify-end" style="--i: 4"><div class="max-w-[85%] bg-zinc-900 text-white px-3 py-2">chhal bi3t had chhar?</div></div>
                    <div class="sc-row" style="--i: 6">
                        <div class="max-w-[90%] border border-zinc-200 bg-zinc-50 px-3 py-2.5 rounded-lg">
                            Had chhar bi3ti <b>18 420,60 € TTC</b>: 15 210,00 b les factures o 3 210,60 f la caisse. Top produit: Fer rond Ø10.
                        </div>
                    </div>
                </div>
                <div class="border-t border-zinc-200 p-2.5 flex gap-2">
                    <div class="flex-1 border border-zinc-200 px-3 py-2 text-[13px] text-zinc-400 rounded-lg">Ex. : Facture pour Café Central, 20 chaises à 175 DH HT</div>
                    <span class="ind-h border border-zinc-200 bg-[#ffcc00] px-3 py-2 text-sm rounded-lg">Envoyer</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ CAISSE (2026-11-25) ============ --}}
<section id="caisse" class="py-20 sm:py-24 scroll-mt-20 bg-zinc-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-12 gap-12 items-center">
        <div class="lg:col-span-6">
            <p class="text-sm font-semibold text-amber-600"><span class="rounded-full bg-[#ffcc00] text-zinc-900 px-2 py-0.5 me-1 text-[11px]">{{ __('NOUVEAU') }}</span> {{ __('Caisse') }}</p>
            <h2 class="mt-3 ind-h text-3xl sm:text-4xl leading-tight">{{ __('Une caisse pour le comptoir, reliée au stock.') }}</h2>
            <p class="mt-5 text-zinc-300">{{ __('Scannez, encaissez en espèces, par carte ou par chèque : le rendu de monnaie se calcule et le ticket s’imprime. Chaque vente sort du stock et compte dans votre tableau de bord.') }}</p>
            <ul class="mt-6 space-y-2.5 text-sm">
                @foreach ([
                    __('Tickets numérotés et signés : impossible de les modifier ou de les supprimer'),
                    __('Erreur ou retour : ticket d’annulation, et chaque réimpression est notée « duplicata »'),
                    __('Clôture Z chaque jour, puis chaque mois et chaque année, avec le grand total perpétuel'),
                    __('Archives signées de chaque période et contrôle d’intégrité en un clic'),
                ] as $pt)
                    <li class="flex gap-3"><span class="mt-0.5 grid place-items-center w-5 h-5 shrink-0 rounded-full bg-[#ffcc00] text-zinc-900 text-[11px] font-bold">✓</span><span>{{ $pt }}</span></li>
                @endforeach
            </ul>
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] text-zinc-900">{{ __('Créer un compte gratuit') }} <span aria-hidden="true">{{ $rtl ? '←' : '→' }}</span></a>
                <a href="{{ route('demo.login') }}" class="ind-btn bg-white text-zinc-900 shadow-sm">{{ __('Essayer la caisse') }}</a>
                <a href="{{ route('seo.caisse') }}" class="text-sm font-semibold underline decoration-amber-400 decoration-2 underline-offset-4">{{ __('En savoir plus') }}</a>
            </div>
            <p class="mt-4 ind-mono text-[11px] text-zinc-400">{{ __('En France : conçue pour les quatre conditions de la loi anti-fraude à la TVA (inaltérabilité, sécurisation, conservation, archivage). À activer dans Ma Société.') }}</p>
        </div>

        {{-- coded receipt + Z (fictional demo data) --}}
        <div class="lg:col-span-6 flex flex-col sm:flex-row items-center sm:items-start justify-center gap-6" dir="ltr" aria-hidden="true">
            <div class="w-[290px] bg-white text-zinc-900 px-5 py-6 ind-mono text-[11.5px] leading-snug shadow-sm -rotate-1">
                <div class="text-center font-bold text-[13px]">DÉMO MATÉRIAUX SAS</div>
                <div class="text-center text-[10px] text-zinc-500">12 rue de l’Industrie, 69007 Lyon<br>SIRET : 000 000 000 00000</div>
                <div class="my-2 border-t border-dashed border-zinc-200"></div>
                <div class="flex justify-between"><span>Ticket T-000128</span><span>25/11 10:15</span></div>
                <div class="my-2 border-t border-dashed border-zinc-200"></div>
                @foreach ([['Disque à tronçonner 115', '4 x 1,00', '4,00'], ['Électrodes 2,5 mm (5 kg)', '1 x 16,50', '16,50'], ['Fer rond Ø8 (12 m)', '2 x 5,50', '11,00']] as [$n, $q, $t])
                    <div>{{ $n }}</div><div class="flex justify-between ps-3 text-zinc-600"><span>{{ $q }}</span><span>{{ $t }}</span></div>
                @endforeach
                <div class="my-2 border-t border-dashed border-zinc-200"></div>
                <div class="flex justify-between font-bold text-[14px]"><span>TOTAL TTC</span><span>31,50 €</span></div>
                <div class="flex justify-between text-[10px] text-zinc-500"><span>TVA 20 % sur 26,25</span><span>5,25</span></div>
                <div class="my-2 border-t border-dashed border-zinc-200"></div>
                <div class="flex justify-between"><span>Espèces</span><span>50,00</span></div>
                <div class="flex justify-between font-bold"><span>Rendu</span><span>18,50</span></div>
                <div class="mt-3 text-center text-[9px] text-zinc-500">Stokito Caisse · Signature 7FA9095F</div>
            </div>
            <div class="w-[220px] sm:mt-16 border border-amber-300 p-4 ind-mono text-[11px] text-amber-300 rotate-1 rounded-xl">
                <div class="ind-h text-lg text-white">CLÔTURE Z</div>
                <div class="text-[#bbb]">Jour · 25/11/2026 · Z n° 42</div>
                <div class="mt-3 flex justify-between"><span>Ventes</span><span class="text-white">37</span></div>
                <div class="flex justify-between"><span>Annulations</span><span class="text-white">1</span></div>
                <div class="flex justify-between"><span>Total TTC</span><span class="text-white">1 284,60</span></div>
                <div class="mt-2 pt-2 border-t border-[#444] flex justify-between"><span>Grand total</span><span class="text-white">48 912,30</span></div>
            </div>
        </div>
    </div>
</section>

{{-- ============ SCREENS (coded replicas, 2026-11-24) ============ --}}
@include('landing.screens')

{{-- ============ GUIDE (2026-11-25: three booklets FR / AR / EN fanned out, download rows) ============ --}}
@php
    $guides = [
        ['code' => 'FR', 'href' => '/docs/guide-stokito-fr.pdf', 'name' => 'Français', 'meta' => 'PDF · 41 pages', 'cover' => "Guide d'utilisation", 'dir' => 'ltr'],
        ['code' => 'ع', 'href' => '/docs/guide-stokito-ar.pdf', 'name' => 'العربية', 'meta' => 'PDF · 41 صفحة', 'cover' => 'دليل الاستخدام', 'dir' => 'rtl'],
        ['code' => 'EN', 'href' => '/docs/guide-stokito-en.pdf', 'name' => 'English', 'meta' => 'PDF · 40 pages', 'cover' => 'User guide', 'dir' => 'ltr'],
    ];
@endphp
<section id="guide" class="py-20 sm:py-24 scroll-mt-20 bg-white border-y border-zinc-200 overflow-hidden">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 grid lg:grid-cols-2 gap-14 items-center">
        {{-- booklets --}}
        <div class="gd-stack group relative h-[300px] sm:h-[380px] flex items-center justify-center scale-[.78] sm:scale-100" aria-hidden="true">
            <div class="absolute w-[300px] h-[300px] rounded-full bg-[#ffcc00]/25 blur-3xl"></div>
            @foreach (array_reverse($guides, true) as $n => $g)
                <div class="gd-book gd-b{{ $n }} absolute w-48 sm:w-52 h-64 sm:h-72 rounded-e-xl rounded-s-md bg-[#141414] text-white shadow-2xl shadow-black/30 overflow-hidden" dir="{{ $g['dir'] }}">
                    <span class="absolute inset-y-0 start-0 w-3 bg-gradient-to-r from-black/60 to-transparent rtl:bg-gradient-to-l"></span>
                    <svg class="absolute -bottom-10 -end-10 w-44 h-44 opacity-25" viewBox="0 0 24 24" fill="none" stroke="#ffcc00" stroke-width=".6" stroke-linejoin="round"><path d="M12 2.5 3.5 7v10L12 21.5 20.5 17V7z"/><path d="M3.5 7 12 11.5 20.5 7M12 11.5v10"/></svg>
                    <div class="relative h-full flex flex-col p-5 ps-6">
                        <span class="grid place-items-center w-9 h-9 rounded-lg bg-[#ffcc00] text-zinc-900"><x-sp-icon name="cube" class="w-5 h-5" /></span>
                        <span class="mt-auto text-[11px] font-semibold text-[#ffcc00]">Stokito</span>
                        <span class="mt-1 text-xl font-bold leading-tight" style="font-family: 'Poppins', 'Tajawal', sans-serif">{{ $g['cover'] }}</span>
                        <span class="mt-4 inline-flex w-max items-center rounded-full border border-white/20 px-2 py-0.5 text-[11px] text-white/70">{{ $g['name'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- text + downloads --}}
        <div>
            <p class="text-sm font-semibold text-amber-600">{{ __('Guide') }}</p>
            <h2 class="mt-2 ind-h text-3xl sm:text-4xl leading-tight">{{ __("Guide d'utilisation gratuit") }}</h2>
            <p class="mt-4 text-zinc-600 leading-relaxed">{{ __("Tout Stokito expliqué simplement, avec des captures d'écran : produits, devis, bons de livraison, factures, encaissements, inventaire. Idéal pour former votre équipe.") }}</p>
            <div class="mt-8 space-y-3">
                @foreach ($guides as $n => $g)
                    <a href="{{ $g['href'] }}" target="_blank" rel="noopener" @mouseenter="$el.closest('section').querySelector('.gd-stack').dataset.on = '{{ $n }}'" @mouseleave="$el.closest('section').querySelector('.gd-stack').dataset.on = ''"
                        class="gd-row group/row flex items-center gap-4 rounded-xl border border-zinc-200 bg-white px-4 py-3.5 transition hover:border-zinc-900 hover:shadow-lg hover:shadow-zinc-900/5 hover:-translate-y-0.5">
                        <span class="grid place-items-center w-11 h-11 shrink-0 rounded-lg bg-zinc-900 text-[#ffcc00] font-bold">{{ $g['code'] }}</span>
                        <span class="flex-1 min-w-0 text-start">
                            <span class="block font-semibold text-zinc-900"><bdi>{{ $g['name'] }}</bdi></span>
                            <span class="block text-sm text-zinc-500"><bdi>{{ $g['meta'] }}</bdi></span>
                        </span>
                        <span class="grid place-items-center w-9 h-9 shrink-0 rounded-full bg-zinc-100 text-zinc-700 transition group-hover/row:bg-[#ffcc00] group-hover/row:text-zinc-900">
                            <svg class="w-4 h-4 transition-transform group-hover/row:translate-y-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v12m0 0-5-5m5 5 5-5M5 20h14"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============ BLOG: latest articles (internal links to the guides) ============ --}}
@php $latestPosts = \App\Models\BlogPost::published()->latest('published_at')->take(3)->get(); @endphp
@if ($latestPosts->isNotEmpty())
<section id="blog" class="py-20 scroll-mt-20 bg-zinc-50 border-b border-zinc-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-6 border-b border-zinc-200 pb-6">
            <div>
                <p class="text-sm font-semibold text-amber-600">BLOG · {{ __('Guides pratiques') }}</p>
                <h2 class="mt-2 ind-h text-3xl sm:text-4xl leading-tight">{{ __('Derniers articles') }}</h2>
            </div>
            <a href="{{ route('blog.index') }}" class="ind-h underline decoration-amber-400 decoration-2 underline-offset-4">{{ __('Tous les articles du blog') }} →</a>
        </div>
        <div class="mt-8 grid md:grid-cols-3 gap-6">
            @foreach ($latestPosts as $bp)
                <a href="{{ route('blog.show', $bp->slug) }}" class="group flex flex-col bg-white border border-zinc-200 shadow-lg shadow-zinc-900/5 hover:bg-zinc-100 rounded-xl">
                    <span class="px-5 py-2.5 border-b border-zinc-200 ind-mono text-[11px]">{{ $bp->category ?: 'Blog' }}</span>
                    <span class="p-5 flex-1 flex flex-col">
                        <span class="ind-h text-lg leading-none">{{ $bp->title }}</span>
                        <span class="mt-3 text-sm text-zinc-600 line-clamp-3">{{ $bp->description }}</span>
                        <span class="mt-auto pt-4 ind-h text-sm">{{ __('Lire l’article') }} →</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ FAQ ============ --}}
<section id="faq" class="py-20 sm:py-24 scroll-mt-20 bg-zinc-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <p class="text-sm font-semibold text-amber-600">{{ __('Questions') }}</p>
        <h2 class="mt-2 ind-h text-3xl sm:text-4xl leading-tight">{{ __('Questions fréquentes') }}</h2>
        <div class="mt-10 border border-zinc-200 bg-white rounded-lg" x-data="{ open: 0 }">
            @foreach ($faqs as $i => [$q, $a])
                <div class="{{ $loop->last ? '' : 'border-b-2 border-zinc-200' }}">
                    <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center gap-4 px-5 py-4 text-start" :class="open === {{ $i }} && 'bg-zinc-50'">
                        <span class="flex-1 font-semibold">{{ __($q) }}</span>
                        <span class="ind-h text-lg leading-none shrink-0" x-text="open === {{ $i }} ? '−' : '+'">+</span>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse @if ($i !== 0) x-cloak @endif class="px-5 pb-5 text-zinc-600">{{ __($a) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ CTA ============ --}}
<section class="bg-zinc-900 text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 grid md:grid-cols-3 gap-8 items-center">
        <div class="md:col-span-2">
            <h2 class="ind-h text-3xl sm:text-4xl leading-tight">{{ __('Commencez gratuitement') }}</h2>
            <p class="mt-3 text-zinc-300 max-w-xl">{{ __('Créez votre espace en une minute, sans carte bancaire. Ou essayez d’abord la démo avec des données fictives.') }}</p>
        </div>
        <div class="flex flex-col gap-3">
            <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] text-zinc-900 justify-center">{{ __('Créer un compte gratuit') }}</a>
            <a href="{{ route('demo.login') }}" class="ind-btn bg-white text-zinc-900 justify-center">{{ __('Tester la démo') }}</a>
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="ind-btn bg-[#25d366] text-zinc-900 justify-center">{!! $waIcon !!} {{ __('Nous écrire sur WhatsApp') }}</a>
        </div>
    </div>
</section>

{{-- ============ FOOTER (shared with the blog) ============ --}}
<x-site-footer />

@livewireScripts
@include('partials.cookie-banner')
</body>
</html>
