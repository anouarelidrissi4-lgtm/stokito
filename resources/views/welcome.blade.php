@php
    $locale = app()->getLocale();
    $rtl = $locale === 'ar';
    $guide = $rtl ? '/docs/guide-stockpilot-ar.pdf' : '/docs/guide-stockpilot-fr.pdf';
    $wa = 'https://wa.me/212676035777';
    $faqs = [
        ['Stokito fonctionne-t-il sur téléphone ?', "Oui. Stokito s'ouvre dans le navigateur de votre ordinateur, de votre tablette ou de votre téléphone. Il n'y a rien à installer."],
        ['Mes factures affichent-elles les identifiants de mon entreprise ?', 'Oui, selon votre pays : SIRET, N° TVA et RCS en France ; ICE, IF, RC, Taxe professionnelle et CNSS au Maroc. Les identifiants du client, la TVA détaillée et le montant en lettres sont aussi imprimés. Faites valider vos mentions par votre comptable.'],
        ['Comment le stock se met-il à jour ?', 'Le stock baisse quand un bon de livraison passe en « Livré » ou quand vous faites une facture directe. Il augmente quand vous marquez une commande fournisseur comme reçue. L’inventaire corrige les écarts.'],
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
    <title>{{ __('Logiciel de gestion de stock et de facturation gratuit | Stokito') }}</title>
    <meta name="description" content="{{ __('Stokito, logiciel de gestion de stock en ligne gratuit : devis, bons de livraison, factures, inventaire, alertes de stock bas et relances clients. Sans installation, sans carte bancaire.') }}">
    <link rel="canonical" href="https://stokito.com/">
    <meta name="google-site-verification" content="H8XthOFRDaYoH40nAZcc3p3AuApVdpXNPTAukNDdub8" />
    <meta name="google-site-verification" content="XEy0-r-vrdATmXb-MhIP0hg5zhoY9-tW4Q5uWn-H0zY" />
    <meta property="og:title" content="Stokito">
    <meta property="og:description" content="{{ __('Gestion de stock, devis, bons de livraison et factures pour les PME. Gratuit, sans installation.') }}">
    <meta property="og:image" content="https://stokito.com/images/screens/screen-dashboard.webp?v=20261123">
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            ['@type' => 'SoftwareApplication', 'name' => 'Stokito', 'applicationCategory' => 'BusinessApplication', 'operatingSystem' => 'Web', 'url' => 'https://stokito.com', 'inLanguage' => ['fr', 'ar', 'en'],
             'description' => 'Logiciel de gestion de stock et de facturation en ligne pour les PME.', 'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR']],
            ['@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => __($f[0]), 'acceptedAnswer' => ['@type' => 'Answer', 'text' => __($f[1])]], $faqs)],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=barlow:400,500,600,700|barlow-condensed:600,700,800|ibm-plex-mono:400,500,600{{ $rtl ? '|tajawal:400,500,700,800' : '' }}&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>

        /* ---------- industrial / dépôt art direction (2026-11-23) ---------- */
        .sp-ind { font-family: 'Barlow', 'Tajawal', sans-serif; background: #f4f2ec; }
        [dir=rtl] .sp-ind { font-family: 'Tajawal', 'Barlow', sans-serif; }
        .ind-h { font-family: 'Barlow Condensed', 'Tajawal', sans-serif; font-weight: 800; }
        .ind-mono { font-family: 'IBM Plex Mono', ui-monospace, monospace; }
        .ind-concrete { background-color: #e6e3dc; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 .55 0'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.18'/%3E%3C/svg%3E"); }
        .ind-hazard { background: repeating-linear-gradient(-45deg, #ffcc00 0 14px, #111 14px 28px); }
        .ind-bc { background: repeating-linear-gradient(90deg, #111 0 2px, transparent 2px 4px, #111 4px 5px, transparent 5px 7px, #111 7px 10px, transparent 10px 11px, #111 11px 12px, transparent 12px 15px); }
        .ind-btn { display: inline-flex; align-items: center; gap: .5rem; height: 3rem; padding: 0 1.4rem; border: 2px solid #111; font-family: 'Barlow Condensed', 'Tajawal', sans-serif; font-weight: 800; font-size: 1.15rem; letter-spacing: .04em; text-transform: uppercase; box-shadow: 4px 4px 0 #111; transition: transform .12s, box-shadow .12s; }
        .ind-btn:hover { transform: translate(2px, 2px); box-shadow: 2px 2px 0 #111; }
        .mono { font-family: 'IBM Plex Mono', ui-monospace, monospace; }
        /* ---------- hero loop: devis -> BL -> stock -> facture -> WhatsApp (14s) ---------- */
        .hx { --d: 14s; }
        .hx * { animation-duration: var(--d); animation-iteration-count: infinite; animation-timing-function: ease; }
        .hx .ln { opacity: 0; animation-name: hxLine; }
        .hx .ln:nth-child(2) { animation-delay: .5s; } .hx .ln:nth-child(3) { animation-delay: 1s; }
        @keyframes hxLine { 0%,6% { opacity: 0; transform: translateY(6px); } 10%,92% { opacity: 1; transform: none; } 97%,100% { opacity: 0; } }
        .hx .tot-a { animation-name: hxTotA; } .hx .tot-b { opacity: 0; animation-name: hxTotB; }
        @keyframes hxTotA { 0%,16% { opacity: 1; } 18%,100% { opacity: 0; } }
        @keyframes hxTotB { 0%,16% { opacity: 0; } 18%,92% { opacity: 1; } 97%,100% { opacity: 0; } }
        .hx .cursor { animation-name: hxCursor; }
        @keyframes hxCursor { 0%,18% { transform: translate(120px, 90px); opacity: 0; } 22% { opacity: 1; } 31% { transform: translate(0, 0); } 33% { transform: translate(0,0) scale(.85); } 35%,44% { transform: translate(0,0) scale(1); opacity: 1; } 48%,100% { opacity: 0; transform: translate(0,0); } }
        .hx .btn-bl { animation-name: hxBtn; }
        @keyframes hxBtn { 0%,32% { box-shadow: 0 0 0 0 rgba(99,102,241,0); } 34% { box-shadow: 0 0 0 7px rgba(99,102,241,.35); } 40%,100% { box-shadow: 0 0 0 0 rgba(99,102,241,0); } }
        .hx .tag-devis { animation-name: hxTagA; } .hx .tag-bl { opacity: 0; animation-name: hxTagB; }
        @keyframes hxTagA { 0%,35% { opacity: 1; } 37%,100% { opacity: 0; } }
        @keyframes hxTagB { 0%,35% { opacity: 0; } 37%,92% { opacity: 1; } 97%,100% { opacity: 0; } }
        .hx .stock { opacity: 0; animation-name: hxStock; }
        @keyframes hxStock { 0%,39% { opacity: 0; transform: translateY(8px); } 43%,56% { opacity: 1; transform: none; } 60%,100% { opacity: 0; } }
        .hx .s-old { animation-name: hxOld; } .hx .s-new { opacity: 0; animation-name: hxNew; }
        @keyframes hxOld { 0%,47% { opacity: 1; } 50%,100% { opacity: 0; } }
        @keyframes hxNew { 0%,47% { opacity: 0; } 50%,92% { opacity: 1; } 97%,100% { opacity: 0; } }
        .hx .s-bar { animation-name: hxBar; }
        @keyframes hxBar { 0%,47% { width: 78%; } 53%,92% { width: 65%; } 100% { width: 78%; } }
        .hx .pdf { opacity: 0; animation-name: hxPdf; }
        @keyframes hxPdf { 0%,54% { opacity: 0; transform: translate(14px, 24px) rotate(2deg); } 60%,92% { opacity: 1; transform: rotate(-2.5deg); } 97%,100% { opacity: 0; transform: rotate(-2.5deg); } }
        .hx .words { clip-path: inset(0 100% 0 0); animation-name: hxWords; }
        [dir=rtl] .hx .words { clip-path: inset(0 0 0 100%); animation-name: hxWordsR; }
        @keyframes hxWords { 0%,61% { clip-path: inset(0 100% 0 0); } 70%,100% { clip-path: inset(0 0 0 0); } }
        @keyframes hxWordsR { 0%,61% { clip-path: inset(0 0 0 100%); } 70%,100% { clip-path: inset(0 0 0 0); } }
        .hx .wa { opacity: 0; animation-name: hxWa; }
        @keyframes hxWa { 0%,72% { opacity: 0; transform: translateY(14px) scale(.96); } 77%,92% { opacity: 1; transform: none; } 97%,100% { opacity: 0; } }
        .hx .ticks { opacity: 0; animation-name: hxTicks; }
        @keyframes hxTicks { 0%,82% { opacity: 0; } 84%,92% { opacity: 1; } 97%,100% { opacity: 0; } }
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
        @keyframes lpFade { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) {
            .hx *, .lp-loop *, .lp-marquee { animation: none !important; }
            .hx .ln, .hx .tot-b, .hx .tag-bl, .hx .stock, .hx .s-new, .hx .pdf, .hx .wa, .hx .ticks, .c-line, .c-b, .c-paid, .c-lang-fr { opacity: 1 !important; }
            .hx .tot-a, .hx .tag-devis, .hx .s-old, .hx .cursor, .c-a, .c-lang-ar { opacity: 0 !important; }
            .hx .words { clip-path: none !important; } .hx .pdf { transform: rotate(-2.5deg); }
        }

        /* industrial colours for the shared loops */
        @keyframes cClick { 0%,30% { background: #fff; color: #111; } 34%,88% { background: #ffcc00; color: #111; } 95%,100% { background: #fff; color: #111; } }
        @keyframes cToggle { 0%,35% { background: #111; } 40%,85% { background: #cfcac0; } 90%,100% { background: #111; } }
    </style>
</head>
<body class="antialiased sp-ind text-[#111]" x-data="{ menu: false }">

{{-- ============ HEADER (solid, no glass) ============ --}}
<header class="sticky top-0 z-40 bg-[#111] text-white border-b-4 border-[#ffcc00]">
    <div class="max-w-7xl mx-auto h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
        <a href="/" class="flex items-center gap-3 shrink-0">
            <span class="grid place-items-center w-9 h-9 bg-[#ffcc00] text-[#111]"><x-sp-icon name="cube" class="w-5 h-5" /></span>
            <span class="ind-h text-2xl tracking-wide">STOKITO</span>
            <span class="hidden 2xl:inline ind-mono text-[11px] text-[#9a9a9a] border border-[#3a3a3a] px-1.5 py-0.5">v2026</span>
        </a>
        <nav class="hidden xl:flex items-center gap-6 whitespace-nowrap ind-h text-[15px] tracking-wider text-[#d6d6d6]">
            <a href="#parcours" class="hidden 2xl:inline hover:text-[#ffcc00]">{{ __('Comment ça marche') }}</a>
            <a href="#fonctionnalites" class="hover:text-[#ffcc00]">{{ __('Fonctionnalités') }}</a>
            <a href="#ecrans" class="hover:text-[#ffcc00]">{{ __('Écrans') }}</a>
            <a href="#guide" class="hover:text-[#ffcc00]">{{ __('Guide') }}</a>
            <a href="#faq" class="hover:text-[#ffcc00]">{{ __('Questions') }}</a>
            <a href="{{ route('blog.index') }}" class="hover:text-[#ffcc00]">{{ __('Blog') }}</a>
        </nav>
        <div class="flex items-center gap-2">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" type="button" class="h-9 px-2.5 border border-[#3a3a3a] ind-mono text-xs hover:border-[#ffcc00]">{{ strtoupper($locale) }}</button>
                <div x-show="open" @click.outside="open = false" x-cloak class="absolute {{ $rtl ? 'left-0' : 'right-0' }} mt-2 w-36 bg-white text-[#111] border-2 border-[#111] text-sm">
                    <a href="{{ route('locale.switch', 'fr') }}" class="block px-3 py-2 hover:bg-[#ffcc00]">Français</a>
                    <a href="{{ route('locale.switch', 'ar') }}" class="block px-3 py-2 hover:bg-[#ffcc00]">العربية</a>
                    <a href="{{ route('locale.switch', 'en') }}" class="block px-3 py-2 hover:bg-[#ffcc00]">English</a>
                </div>
            </div>
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="hidden sm:flex h-9 px-3 items-center gap-1.5 border border-[#3a3a3a] text-sm font-semibold hover:border-[#25d366] hover:text-[#25d366]">{!! $waIcon !!} <span class="hidden 2xl:inline">WhatsApp</span></a>
            @guest<a href="{{ route('login') }}" class="hidden md:flex h-9 px-3 items-center whitespace-nowrap border border-[#3a3a3a] ind-h tracking-wider text-[15px] hover:border-[#ffcc00]">{{ __('Se connecter') }}</a>@endguest
            <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="hidden sm:flex h-9 px-4 items-center whitespace-nowrap bg-[#ffcc00] text-[#111] ind-h tracking-wider text-[15px] hover:bg-white">{{ auth()->check() ? __('Tableau de bord') : __('Créer un compte gratuit') }}</a>
            <button type="button" @click="menu = !menu" class="xl:hidden p-2 border border-[#3a3a3a]" aria-label="Menu">
                <x-sp-icon name="menu" class="w-6 h-6" x-show="!menu" /><x-sp-icon name="x" class="w-6 h-6" x-show="menu" x-cloak />
            </button>
        </div>
    </div>
    <div x-show="menu" x-cloak @click="menu = false" class="xl:hidden border-t border-[#2a2a2a] px-4 pb-4 pt-2 ind-h tracking-wider text-lg">
        <a href="#parcours" class="block py-2.5 border-b border-[#2a2a2a]">{{ __('Comment ça marche') }}</a>
        <a href="#fonctionnalites" class="block py-2.5 border-b border-[#2a2a2a]">{{ __('Fonctionnalités') }}</a>
        <a href="#ecrans" class="block py-2.5 border-b border-[#2a2a2a]">{{ __('Écrans') }}</a>
        <a href="#guide" class="block py-2.5 border-b border-[#2a2a2a]">{{ __('Guide') }}</a>
        <a href="#faq" class="block py-2.5 border-b border-[#2a2a2a]">{{ __('Questions') }}</a>
        <a href="{{ route('blog.index') }}" class="block py-2.5 border-b border-[#2a2a2a]">{{ __('Blog') }}</a>
        @guest<a href="{{ route('register') }}" class="mt-3 flex h-12 items-center justify-center bg-[#ffcc00] text-[#111]">{{ __('Créer un compte gratuit') }}</a>@endguest
        <div class="grid grid-cols-2 gap-2 pt-3 font-sans text-sm">
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="flex h-11 items-center justify-center gap-1.5 border border-[#3a3a3a]">{!! $waIcon !!} WhatsApp</a>
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="flex h-11 items-center justify-center bg-[#ffcc00] text-[#111] font-bold">{{ auth()->check() ? __('Tableau de bord') : __('Se connecter') }}</a>
        </div>
    </div>
</header>

<a href="{{ $wa }}" target="_blank" rel="noopener" aria-label="WhatsApp" class="fixed bottom-5 {{ $rtl ? 'left-5' : 'right-5' }} z-40 h-14 w-14 grid place-items-center bg-[#25d366] text-white border-2 border-[#111] shadow-[4px_4px_0_#111]"><span class="[&>svg]:h-7 [&>svg]:w-7">{!! $waIcon !!}</span></a>

{{-- ============ HERO ============ --}}
<section class="relative ind-concrete overflow-hidden border-b-2 border-[#111]">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-24 sm:pt-16 lg:pb-28 grid lg:grid-cols-12 gap-12 items-center">
        <div class="lg:col-span-6">
            <div class="flex items-center gap-3 ind-mono text-xs text-[#555]">
                <span class="bg-[#111] text-[#ffcc00] px-2 py-1">REF · SP-01</span>
                <span>{{ __('Logiciel de gestion de stock en ligne') }}</span>
            </div>
            <h1 class="mt-6 ind-h text-[3.4rem] sm:text-7xl xl:text-[5.4rem] leading-[0.9] uppercase">
                {{ __('Votre stock, vos devis et vos factures.') }}
                <span class="relative inline-block mt-2"><span class="relative z-10">{{ __('Au même endroit.') }}</span><span class="absolute inset-x-0 bottom-1 h-[38%] bg-[#ffcc00] -z-0"></span></span>
            </h1>
            <p class="mt-6 text-lg text-[#333] max-w-xl">{{ __('Stokito suit chaque produit du devis jusqu’à l’encaissement. Le stock se met à jour tout seul, vos factures portent vos identifiants, la TVA et le montant en lettres, et vous relancez vos clients sur WhatsApp.') }}</p>
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('register') }}" class="ind-btn bg-[#111] text-[#ffcc00]">{{ __('Créer un compte gratuit') }} <span aria-hidden="true">{{ $rtl ? '←' : '→' }}</span></a>
                <a href="{{ route('demo.login') }}" class="ind-btn bg-white text-[#111]">{{ __('Tester la démo') }}</a>
            </div>
            <dl class="mt-10 grid grid-cols-3 max-w-lg border-2 border-[#111] bg-white">
                <div class="p-3 border-e-2 border-[#111]"><dt class="ind-mono text-[10px] text-[#777]">INSTALL.</dt><dd class="ind-h text-lg leading-tight">{{ __('Sans installation') }}</dd></div>
                <div class="p-3 border-e-2 border-[#111]"><dt class="ind-mono text-[10px] text-[#777]">LANG.</dt><dd class="ind-h text-lg leading-tight">{{ __('Français, anglais, arabe') }}</dd></div>
                <div class="p-3"><dt class="ind-mono text-[10px] text-[#777]">ÉCRAN</dt><dd class="ind-h text-lg leading-tight">{{ __('PC et téléphone') }}</dd></div>
            </dl>
        </div>

        {{-- hero animation: same story, document look --}}
        <div class="lg:col-span-6 hx relative" aria-hidden="true" dir="ltr">
            <div class="relative mx-auto max-w-[560px]">
                <div class="absolute -top-4 left-6 z-10 ind-mono text-[11px] bg-[#ffcc00] border-2 border-[#111] px-2 py-0.5">DOC 1/3</div>
                <div class="bg-white text-[#111] border-2 border-[#111] shadow-[10px_10px_0_#111]">
                    <div class="h-9 flex items-center justify-between px-4 border-b-2 border-[#111] bg-[#f4f2ec]">
                        <span class="ind-mono text-[11px] text-[#555]">stokito.com/devis</span>
                        <span class="ind-bc w-16 h-4"></span>
                    </div>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="relative h-5">
                                    <span class="tag-devis absolute inset-0 ind-mono text-[11px] font-semibold whitespace-nowrap">DEVIS · DEV-00042</span>
                                    <span class="tag-bl absolute inset-0 ind-mono text-[11px] font-semibold text-[#0a7a3e] whitespace-nowrap"><span class="hidden sm:inline">BON DE LIVRAISON · </span>BL-00031 · LIVRÉ ✓</span>
                                </div>
                                <div class="ind-h text-2xl leading-tight">BÂTI PLUS SARL</div>
                                <div class="ind-mono text-[11px] text-[#666]">SIRET 123 456 789 00012 · Lyon</div>
                            </div>
                            <div class="btn-bl shrink-0 relative bg-[#111] text-[#ffcc00] ind-h tracking-wider text-sm px-3 py-2">CONVERTIR EN BL
                                <svg class="cursor absolute -bottom-5 right-2 w-6 h-6" viewBox="0 0 24 24"><path d="M5 3l14 8-6 1.5L10 19z" fill="#ffcc00" stroke="#111" stroke-width="1.5" stroke-linejoin="round"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 border-2 border-[#111] text-[12.5px]">
                            <div class="grid grid-cols-[1fr_44px_70px_84px] gap-2 bg-[#111] text-[#ffcc00] px-3 py-1.5 ind-mono text-[10px]"><span>PRODUIT</span><span class="text-right">QTÉ</span><span class="text-right">P.U. HT</span><span class="text-right">TOTAL HT</span></div>
                            <div>
                                <div class="ln grid grid-cols-[1fr_44px_70px_84px] gap-2 px-3 py-2 border-t border-[#ddd]"><span class="truncate">Fer rond à béton Ø10</span><span class="text-right ind-mono">50</span><span class="text-right ind-mono">7,20</span><span class="text-right ind-mono font-semibold">360,00</span></div>
                                <div class="ln grid grid-cols-[1fr_44px_70px_84px] gap-2 px-3 py-2 border-t border-[#ddd]"><span class="truncate">Tube carré 40×40×2</span><span class="text-right ind-mono">12</span><span class="text-right ind-mono">18,50</span><span class="text-right ind-mono font-semibold">222,00</span></div>
                                <div class="ln grid grid-cols-[1fr_44px_70px_84px] gap-2 px-3 py-2 border-t border-[#ddd]"><span class="truncate">Peinture antirouille 4 L</span><span class="text-right ind-mono">6</span><span class="text-right ind-mono">32,90</span><span class="text-right ind-mono font-semibold">197,40</span></div>
                            </div>
                        </div>
                        <div class="mt-3 flex items-end justify-between gap-4">
                            <div class="stock w-52 border-2 border-[#111] bg-[#ffcc00] px-3 py-2 text-xs">
                                <div class="ind-mono text-[10px]">ÉTIQUETTE STOCK · FER-RD-10</div>
                                <div class="mt-1 flex items-center gap-2"><span class="relative inline-block w-24 h-5 whitespace-nowrap"><span class="s-old absolute inset-0 ind-h text-lg leading-5">310 BARRES</span><span class="s-new absolute inset-0 ind-h text-lg leading-5">260 BARRES</span></span><span class="ind-mono font-bold">−50</span></div>
                                <div class="mt-1.5 h-1.5 bg-[#111]/20"><div class="s-bar h-full bg-[#111]"></div></div>
                            </div>
                            <div class="text-right text-xs leading-5 ind-mono">
                                <div>HT <b>779,40</b></div>
                                <div>TVA 20 % <b>155,88</b></div>
                                <div class="relative h-6 text-sm"><span class="tot-a absolute right-0 whitespace-nowrap text-[#999]">TTC …</span><span class="tot-b absolute right-0 whitespace-nowrap font-bold bg-[#111] text-[#ffcc00] px-1.5">TTC 935,28 €</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pdf absolute -bottom-10 -left-4 sm:-left-10 w-60 bg-white text-[#111] border-2 border-[#111] shadow-[6px_6px_0_#111] p-4 text-[10px] leading-4">
                    <div class="flex justify-between"><b class="text-[11px]">Démo Matériaux SARL</b><b class="ind-mono">FACTURE</b></div>
                    <div class="ind-mono text-[9px] text-[#666]">SIRET · N° TVA · RCS</div>
                    <div class="mt-1 text-right ind-mono text-[#666]">N° FAC-00058</div>
                    <div class="mt-2 h-0.5 bg-[#111]"></div>
                    <div class="mt-2 flex justify-between ind-mono"><span>HT</span><b>779,40</b></div>
                    <div class="flex justify-between ind-mono"><span>TVA 20 %</span><b>155,88</b></div>
                    <div class="flex justify-between ind-mono text-[11px]"><span>TTC</span><b>935,28 €</b></div>
                    <div class="words mt-2 text-[#444]">Arrêtée la présente facture à la somme de : <b>neuf cent trente-cinq euros et vingt-huit centimes TTC</b>.</div>
                    <div class="absolute -right-3 -bottom-3 rotate-[-12deg] border-2 border-[#c62828] text-[#c62828] ind-h text-xs px-2 py-0.5 bg-white/80">PAYÉE</div>
                </div>

                <div class="wa absolute -bottom-14 right-0 sm:-right-6 w-64 bg-[#dcf8c6] text-[#111] border-2 border-[#111] shadow-[6px_6px_0_#111] p-3 text-[11.5px] leading-4">
                    <div class="flex items-center gap-1.5 ind-mono text-[10px] text-[#0a7a3e]"><span class="[&>svg]:h-3.5 [&>svg]:w-3.5">{!! $waIcon !!}</span>WHATSAPP · BÂTI PLUS</div>
                    <p class="mt-1">Bonjour, la facture <b>FAC-00058</b> du 22/11 présente un reste à payer de <b>935,28 €</b>, échéance le 22/12. Merci.</p>
                    <div class="mt-1 text-right ind-mono text-[10px] text-[#666]">10:42 <span class="ticks text-[#1d8ad6] font-bold">✓✓</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ HAZARD STRIP: sectors ============ --}}
<div class="ind-hazard h-3"></div>
<section class="bg-[#111] text-[#ffcc00] py-3.5 overflow-hidden">
    <div class="flex w-max lp-marquee">
        @foreach ([1, 2] as $copy)
            <div class="flex items-center gap-8 px-4">
                @foreach ($sectors as $s)
                    <span class="flex items-center gap-8 whitespace-nowrap ind-h text-lg tracking-widest uppercase">{{ __($s) }}<span class="text-[#555]">/</span></span>
                @endforeach
            </div>
        @endforeach
    </div>
</section>
<div class="ind-hazard h-3"></div>

{{-- ============ HOW IT WORKS: 4 stations ============ --}}
<section id="parcours" class="py-20 sm:py-24 scroll-mt-20 bg-[#f4f2ec]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-6 border-b-2 border-[#111] pb-6">
            <div class="max-w-2xl">
                <p class="ind-mono text-xs text-[#666]">SECTION 02 · {{ __('Comment ça marche') }}</p>
                <h2 class="mt-2 ind-h text-4xl sm:text-5xl uppercase leading-[0.95]">{{ __('Du devis à l’encaissement, en 4 étapes') }}</h2>
            </div>
            <p class="max-w-sm text-[#444]">{{ __('Chaque document reprend le précédent : vous ne retapez jamais les mêmes lignes.') }}</p>
        </div>
        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 border-2 border-[#111] bg-white lp-loop">
            {{-- 1 --}}
            <div class="p-5 border-b-2 sm:border-e-2 lg:border-b-0 border-[#111]">
                <div class="flex items-baseline justify-between"><span class="ind-h text-6xl leading-none text-[#ffcc00] [-webkit-text-stroke:1.5px_#111]">01</span><span class="ind-mono text-[10px] text-[#777]">DEV</span></div>
                <div class="mt-4 h-32 border-2 border-[#111] bg-[#f4f2ec] p-2.5 text-[11px]" dir="ltr">
                    <div class="ind-mono text-[10px]">DEV-00042</div>
                    <div>
                        <div class="c-line flex justify-between border-b border-[#ccc] py-1"><span>Fer rond Ø10 × 50</span><b class="ind-mono">360,00</b></div>
                        <div class="c-line flex justify-between border-b border-[#ccc] py-1"><span>Tube carré 40 × 12</span><b class="ind-mono">222,00</b></div>
                        <div class="c-line flex justify-between py-1"><span>Antirouille 4 L × 6</span><b class="ind-mono">197,40</b></div>
                    </div>
                </div>
                <h3 class="mt-4 ind-h text-2xl uppercase">{{ __('Le devis') }}</h3>
                <p class="mt-1 text-sm text-[#444]">{{ __('Tapez le nom du produit, la quantité : le prix HT, la TVA et le total se calculent seuls. Tarif normal ou tarif de gros.') }}</p>
            </div>
            {{-- 2 --}}
            <div class="p-5 border-b-2 lg:border-b-0 lg:border-e-2 border-[#111]">
                <div class="flex items-baseline justify-between"><span class="ind-h text-6xl leading-none text-[#ffcc00] [-webkit-text-stroke:1.5px_#111]">02</span><span class="ind-mono text-[10px] text-[#777]">BL</span></div>
                <div class="mt-4 h-32 border-2 border-[#111] bg-[#f4f2ec] p-2.5 text-[11px] flex flex-col justify-between" dir="ltr">
                    <div class="flex items-center justify-between"><span class="ind-mono text-[10px]">BL-00031</span><span class="c-click border-2 border-[#111] px-2 py-0.5 ind-h text-xs">MARQUER LIVRÉ</span></div>
                    <div class="border-2 border-[#111] bg-[#ffcc00] p-2">
                        <div class="ind-mono text-[10px]">STOCK · FER RD Ø10</div>
                        <div class="relative h-6 ind-h text-xl"><span class="c-a absolute">310</span><span class="c-b absolute">260 <span class="ind-mono text-xs">−50</span></span></div>
                    </div>
                </div>
                <h3 class="mt-4 ind-h text-2xl uppercase">{{ __('La livraison') }}</h3>
                <p class="mt-1 text-sm text-[#444]">{{ __('Un clic transforme le devis en bon de livraison. Quand il passe en « Livré », le stock baisse automatiquement.') }}</p>
            </div>
            {{-- 3 --}}
            <div class="p-5 border-b-2 sm:border-b-0 sm:border-e-2 border-[#111]">
                <div class="flex items-baseline justify-between"><span class="ind-h text-6xl leading-none text-[#ffcc00] [-webkit-text-stroke:1.5px_#111]">03</span><span class="ind-mono text-[10px] text-[#777]">FAC</span></div>
                <div class="mt-4 h-32 border-2 border-[#111] bg-[#f4f2ec] p-2.5 text-[10px] leading-4" dir="ltr">
                    <div class="mx-auto w-40 h-full bg-white border border-[#111] p-2 relative">
                        <div class="flex justify-between"><b>FACTURE</b><span class="ind-mono text-[#777]">FAC-00058</span></div>
                        <div class="ind-mono text-[9px] text-[#777]">ICE · IF · RC · TP</div>
                        <div class="mt-1 h-0.5 bg-[#111]"></div>
                        <div class="mt-1 flex justify-between ind-mono"><span>TVA 20 %</span><b>155,88</b></div>
                        <div class="flex justify-between ind-mono"><span>TTC</span><b>935,28 €</b></div>
                        <div class="mt-0.5 italic text-[#555]">« six mille huit cent… »</div>
                        <div class="absolute right-1 bottom-1 rotate-[-10deg] border border-[#c62828] text-[#c62828] ind-h text-[9px] px-1">CACHET</div>
                    </div>
                </div>
                <h3 class="mt-4 ind-h text-2xl uppercase">{{ __('La facture') }}</h3>
                <p class="mt-1 text-sm text-[#444]">{{ __('Facture PDF avec votre logo, vos identifiants fiscaux, la TVA détaillée et le montant en lettres.') }}</p>
            </div>
            {{-- 4 --}}
            <div class="p-5">
                <div class="flex items-baseline justify-between"><span class="ind-h text-6xl leading-none text-[#ffcc00] [-webkit-text-stroke:1.5px_#111]">04</span><span class="ind-mono text-[10px] text-[#777]">REG</span></div>
                <div class="mt-4 h-32 border-2 border-[#111] bg-[#f4f2ec] p-2.5 text-[11px] flex flex-col justify-between" dir="ltr">
                    <div>
                        <div class="flex justify-between"><span class="ind-mono text-[10px]">FAC-00058 · REÇU</span><span class="c-paid bg-[#0a7a3e] text-white ind-h text-[11px] px-1.5">PAYÉE</span></div>
                        <div class="mt-2 h-3 border border-[#111] bg-white"><div class="c-pay h-full bg-[#111]"></div></div>
                        <div class="mt-1 ind-mono text-[10px] text-[#666]">CHÈQUE · VIREMENT · ESPÈCES</div>
                    </div>
                    <div class="self-end border border-[#111] bg-[#dcf8c6] px-2 py-1 text-[10.5px]">Rappel FAC-00058 <span class="text-[#1d8ad6] font-bold">✓✓</span></div>
                </div>
                <h3 class="mt-4 ind-h text-2xl uppercase">{{ __('L’encaissement') }}</h3>
                <p class="mt-1 text-sm text-[#444]">{{ __('Enregistrez chaque paiement. Pour un retard, le bouton « Relancer » prépare le message WhatsApp pour vous.') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ============ FEATURES: rack of modules ============ --}}
<section id="fonctionnalites" class="py-20 sm:py-24 scroll-mt-20 ind-concrete border-y-2 border-[#111]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="border-b-2 border-[#111] pb-6">
            <p class="ind-mono text-xs text-[#666]">SECTION 03 · {{ __('Fonctionnalités') }}</p>
            <h2 class="mt-2 ind-h text-4xl sm:text-5xl uppercase leading-[0.95] max-w-3xl">{{ __('Ce qu’il faut pour un commerce, sans le superflu') }}</h2>
        </div>
        <div class="mt-10 grid md:grid-cols-6 border-2 border-[#111] bg-white lp-loop">
            <div class="md:col-span-3 p-6 border-b-2 md:border-e-2 border-[#111]">
                <div class="flex justify-between ind-mono text-[10px] text-[#777]"><span>MOD-01</span><span>STOCK</span></div>
                <h3 class="mt-2 ind-h text-3xl uppercase">{{ __('Stock toujours à jour') }}</h3>
                <p class="mt-2 text-sm text-[#444]">{{ __('Entrées à la réception des commandes, sorties à la livraison, alerte quand un produit passe sous son seuil.') }}</p>
                <div class="mt-5 space-y-3 text-xs" dir="ltr">
                    @foreach ([['FER-RD-10', 'Fer rond Ø10', '260', 72, 'bg-[#111]'], ['TUB-CAR-60', 'Tube carré 60×60', '12 / 20', 38, 'bg-[#ffcc00]'], ['VANNE-1/2', 'Vanne laiton 1/2"', '0 / 10', 4, 'bg-[#c62828]']] as [$r, $n, $q, $w, $c])
                        <div><div class="flex justify-between"><span><span class="ind-mono text-[10px] text-[#888] me-2">{{ $r }}</span>{{ $n }}</span><b class="ind-mono">{{ $q }}</b></div><div class="mt-1 h-2.5 border border-[#111]"><div class="h-full {{ $c }}" style="width: {{ $w }}%"></div></div></div>
                    @endforeach
                </div>
            </div>
            <div class="md:col-span-3 p-6 border-b-2 border-[#111]">
                <div class="flex justify-between ind-mono text-[10px] text-[#777]"><span>MOD-02</span><span>CHIFFRES</span></div>
                <h3 class="mt-2 ind-h text-3xl uppercase">{{ __('Vos chiffres du mois') }}</h3>
                <p class="mt-2 text-sm text-[#444]">{{ __('Chiffre d’affaires, encaissé, impayés et ventes des 6 derniers mois sur le tableau de bord.') }}</p>
                <div class="mt-5 h-24 flex items-end gap-2 border-b-2 border-[#111]" dir="ltr">
                    @foreach ([46, 62, 46, 80, 92, 34] as $h)
                        <div class="c-bar flex-1 {{ $loop->last ? 'bg-[#ffcc00] border-2 border-b-0 border-[#111]' : 'bg-[#111]' }}" style="height: {{ $h }}%"></div>
                    @endforeach
                </div>
                <div class="mt-1 flex justify-between ind-mono text-[10px] text-[#777]" dir="ltr"><span>JUIN</span><span>JUIL.</span><span>AOÛT</span><span>SEPT.</span><span>OCT.</span><span>NOV.</span></div>
            </div>
            <div class="md:col-span-2 p-6 border-b-2 md:border-b-0 md:border-e-2 border-[#111]">
                <div class="flex justify-between ind-mono text-[10px] text-[#777]"><span>MOD-03</span><span>INV.</span></div>
                <h3 class="mt-2 ind-h text-2xl uppercase">{{ __('Inventaire') }}</h3>
                <p class="mt-2 text-sm text-[#444]">{{ __('Comptez, Stokito montre les écarts et corrige le stock.') }}</p>
                <div class="mt-4 border-2 border-[#111] text-xs ind-mono" dir="ltr">
                    <div class="flex justify-between px-3 py-2 border-b border-[#ccc]"><span>DISQ-115</span><span>500 → 480 <b class="text-[#c62828]">−20</b></span></div>
                    <div class="flex justify-between px-3 py-2"><span>COR-40</span><span>140 → 140 <b class="text-[#0a7a3e]">OK</b></span></div>
                </div>
            </div>
            <div class="md:col-span-2 p-6 border-b-2 md:border-b-0 md:border-e-2 border-[#111]">
                <div class="flex justify-between ind-mono text-[10px] text-[#777]"><span>MOD-04</span><span>ÉQUIPE</span></div>
                <h3 class="mt-2 ind-h text-2xl uppercase">{{ __('Équipe et permissions') }}</h3>
                <p class="mt-2 text-sm text-[#444]">{{ __('Chaque employé son compte. Cachez les prix d’achat et les marges aux vendeurs.') }}</p>
                <div class="mt-4 space-y-2 text-xs" dir="ltr">
                    <div class="flex items-center justify-between border-2 border-[#111] px-3 py-2"><span>Devis : Créer</span><span class="w-8 h-4 bg-[#111] relative"><span class="absolute top-0.5 left-0.5 w-3 h-3 bg-[#ffcc00] translate-x-[14px]"></span></span></div>
                    <div class="flex items-center justify-between border-2 border-[#111] px-3 py-2"><span>Voir prix d'achat</span><span class="c-toggle w-8 h-4 relative"><span class="c-knob absolute top-0.5 left-0.5 w-3 h-3 bg-white"></span></span></div>
                </div>
            </div>
            <div class="md:col-span-2 p-6">
                <div class="flex justify-between ind-mono text-[10px] text-[#777]"><span>MOD-05</span><span>FR / AR</span></div>
                <h3 class="mt-2 ind-h text-2xl uppercase">{{ __('En français et en arabe') }}</h3>
                <p class="mt-2 text-sm text-[#444]">{{ __('Chaque utilisateur choisit sa langue ; l’arabe s’affiche de droite à gauche.') }}</p>
                <div class="mt-4 relative h-14 border-2 border-[#111] bg-[#f4f2ec]">
                    <div class="c-lang-fr absolute inset-0 flex items-center px-4 font-semibold" dir="ltr">Tableau de bord · Ventes</div>
                    <div class="c-lang-ar absolute inset-0 flex items-center px-4 font-semibold" dir="rtl" style="font-family:Tajawal,sans-serif">لوحة القيادة · المبيعات</div>
                </div>
            </div>
        </div>
        {{-- MOD-06: country rules, Maroc + France tabs (2026-11-23) --}}
        <div class="mt-6 border-2 border-[#111] bg-[#111] text-white p-6 sm:p-8" x-data="{ c: 'fr' }">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="ind-mono text-[10px] text-[#ffcc00]">MOD-06 · CONFORMITÉ</p>
                    <h3 class="mt-1 ind-h text-3xl uppercase">{{ __('Fait pour les règles de la France et du Maroc') }}</h3>
                </div>
                <div class="flex" role="tablist">
                    <button type="button" role="tab" @click="c = 'fr'" :aria-selected="c === 'fr'" class="ind-h uppercase tracking-wider px-4 py-1.5 border-2 border-[#ffcc00] -me-0.5" :class="c === 'fr' ? 'bg-[#ffcc00] text-[#111]' : 'text-[#ffcc00] hover:bg-[#222]'">{{ __('France') }}</button>
                    <button type="button" role="tab" @click="c = 'ma'" :aria-selected="c === 'ma'" class="ind-h uppercase tracking-wider px-4 py-1.5 border-2 border-[#ffcc00]" :class="c === 'ma' ? 'bg-[#ffcc00] text-[#111]' : 'text-[#ffcc00] hover:bg-[#222]'">{{ __('Maroc') }}</button>
                </div>
            </div>
            @foreach ([
                'fr' => [__('SIRET, N° TVA intracommunautaire et RCS sur vos documents, SIRET ou N° TVA du client, TVA à 20 % réglable, montants et montant en lettres en euros, conditions de paiement en pied de facture.'),
                         ['SIRET', 'N° TVA', 'RCS', 'TVA 20 %', 'EUR', __('Montant en lettres'), __('Avec ou sans en-tête')]],
                'ma' => [__('ICE, IF, RC, Taxe professionnelle et CNSS sur vos documents, ICE du client, TVA par défaut réglable, montant en lettres en dirhams, conditions de paiement en pied de facture.'),
                         ['ICE', 'IF', 'RC', 'TP', 'CNSS', 'TVA 20 %', 'MAD', __('Montant en lettres'), __('Avec ou sans en-tête')]],
            ] as $code => [$txt, $chips])
                <div x-show="c === '{{ $code }}'" @if ($code !== 'fr') x-cloak @endif class="mt-5 grid md:grid-cols-2 gap-6 items-center">
                    <p class="text-sm text-[#cfcfcf]">{{ $txt }}</p>
                    <div class="flex flex-wrap gap-2" dir="ltr">
                        @foreach ($chips as $chip)
                            <span class="border-2 border-[#ffcc00] text-[#ffcc00] ind-mono text-sm px-3 py-1">{{ $chip }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <p class="mt-5 pt-4 border-t border-[#2a2a2a] ind-mono text-[11px] text-[#9a9a9a]">{{ __('Aussi : Belgique, Suisse, Luxembourg, Sénégal, Côte d’Ivoire, Cameroun, Tunisie, Algérie. Devise, TVA et identifiants s’adaptent au pays choisi.') }}</p>
        </div>
    </div>
</section>

{{-- ============ REAL SCREENS ============ --}}
<section id="ecrans" class="py-20 sm:py-24 scroll-mt-20 bg-[#f4f2ec]" x-data="{ tab: 0, n: {{ count($screens) }}, t: null }" x-init="t = setInterval(() => tab = (tab + 1) % n, 5000)">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-6 border-b-2 border-[#111] pb-6">
            <div>
                <p class="ind-mono text-xs text-[#666]">SECTION 04 · {{ __('Écrans') }}</p>
                <h2 class="mt-2 ind-h text-4xl sm:text-5xl uppercase leading-[0.95]">{{ __('Les vrais écrans de l’application') }}</h2>
            </div>
            <p class="max-w-sm text-[#444]">{{ __('Ce que vous voyez ici est la démo en ligne, avec des données fictives.') }}</p>
        </div>
        <div class="mt-8 flex flex-wrap gap-0">
            @foreach ($screens as $i => [$key, $label])
                <button type="button" @click="tab = {{ $i }}; clearInterval(t)" class="ind-h tracking-wider uppercase px-4 py-2 border-2 border-[#111] -me-0.5 -mb-0.5" :class="tab === {{ $i }} ? 'bg-[#111] text-[#ffcc00]' : 'bg-white text-[#111] hover:bg-[#ffcc00]'">{{ __($label) }}</button>
            @endforeach
        </div>
        <div class="border-2 border-[#111] bg-white shadow-[10px_10px_0_#111]">
            <div class="h-9 flex items-center justify-between px-4 border-b-2 border-[#111] bg-[#f4f2ec]" dir="ltr"><span class="ind-mono text-[11px] text-[#555]">stokito.com</span><span class="ind-bc w-20 h-4"></span></div>
            <div class="grid">
            @foreach ($screens as $i => [$key, $label])
                <img src="/images/screens/screen-{{ $key }}.webp?v=20261123" alt="{{ __('Capture d’écran Stokito') }} : {{ __($label) }}" width="1400" @if ($i) loading="lazy" @endif
                    class="col-start-1 row-start-1 w-full self-start transition-opacity duration-500" :class="tab === {{ $i }} ? 'opacity-100' : 'opacity-0 pointer-events-none'" @if ($i) style="opacity:0" @endif x-bind:style="''">
            @endforeach
            </div>
        </div>
        <div class="mt-10"><a href="{{ route('demo.login') }}" class="ind-btn bg-[#ffcc00] text-[#111]">{{ __('Ouvrir la démo') }} <span aria-hidden="true">{{ $rtl ? '←' : '→' }}</span></a></div>
    </div>
</section>

{{-- ============ GUIDE ============ --}}
<section id="guide" class="py-20 scroll-mt-20 ind-concrete border-y-2 border-[#111]">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 grid md:grid-cols-5 gap-10 items-center">
        <div class="md:col-span-2 flex justify-center">
            <div class="relative w-44 h-60 bg-[#ffcc00] border-2 border-[#111] shadow-[8px_8px_0_#111] p-4 -rotate-2">
                <div class="ind-hazard absolute top-0 inset-x-0 h-3"></div>
                <div class="mt-4 ind-mono text-[10px]">DOC · SP-GUIDE</div>
                <div class="mt-12 ind-h text-2xl uppercase leading-none">{{ __("Guide d'utilisation") }}</div>
                <div class="mt-2 ind-mono text-[10px]">{{ __('23 pages, pas à pas') }}</div>
                <div class="absolute bottom-4 left-4 right-4 ind-bc h-6"></div>
            </div>
        </div>
        <div class="md:col-span-3">
            <p class="ind-mono text-xs text-[#666]">SECTION 05 · {{ __('Guide') }}</p>
            <h2 class="mt-2 ind-h text-4xl uppercase leading-[0.95]">{{ __("Guide d'utilisation gratuit") }}</h2>
            <p class="mt-3 text-[#444]">{{ __("Tout Stokito expliqué simplement, avec des captures d'écran : produits, devis, bons de livraison, factures, encaissements, inventaire. Idéal pour former votre équipe.") }}</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="/docs/guide-stockpilot-fr.pdf" target="_blank" rel="noopener" class="ind-btn bg-[#111] text-[#ffcc00]"><x-sp-icon name="doc" class="w-5 h-5" /> {{ __('Guide en français (PDF)') }}</a>
                <a href="/docs/guide-stockpilot-ar.pdf" target="_blank" rel="noopener" class="ind-btn bg-white text-[#111]"><x-sp-icon name="doc" class="w-5 h-5" /> <span dir="rtl">الدليل بالعربية (PDF)</span></a>
                <a href="/docs/guide-stockpilot-en.pdf" target="_blank" rel="noopener" class="ind-btn bg-white text-[#111]"><x-sp-icon name="doc" class="w-5 h-5" /> English (PDF)</a>
            </div>
        </div>
    </div>
</section>

{{-- ============ BLOG: latest articles (internal links to the guides) ============ --}}
@php $latestPosts = \App\Models\BlogPost::published()->latest('published_at')->take(3)->get(); @endphp
@if ($latestPosts->isNotEmpty())
<section id="blog" class="py-20 scroll-mt-20 bg-[#f4f2ec] border-b-2 border-[#111]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-6 border-b-2 border-[#111] pb-6">
            <div>
                <p class="ind-mono text-xs text-[#666]">BLOG · {{ __('Guides pratiques') }}</p>
                <h2 class="mt-2 ind-h text-4xl sm:text-5xl uppercase leading-[0.95]">{{ __('Derniers articles') }}</h2>
            </div>
            <a href="{{ route('blog.index') }}" class="ind-h uppercase tracking-wider underline decoration-[#ffcc00] decoration-[3px] underline-offset-4">{{ __('Tous les articles du blog') }} →</a>
        </div>
        <div class="mt-8 grid md:grid-cols-3 gap-6">
            @foreach ($latestPosts as $bp)
                <a href="{{ route('blog.show', $bp->slug) }}" class="group flex flex-col bg-white border-2 border-[#111] shadow-[6px_6px_0_#111] hover:bg-[#ffcc00]">
                    <span class="px-5 py-2.5 border-b-2 border-[#111] ind-mono text-[11px] uppercase">{{ $bp->category ?: 'Blog' }}</span>
                    <span class="p-5 flex-1 flex flex-col">
                        <span class="ind-h text-2xl uppercase leading-none">{{ $bp->title }}</span>
                        <span class="mt-3 text-sm text-[#444] line-clamp-3">{{ $bp->description }}</span>
                        <span class="mt-auto pt-4 ind-h uppercase tracking-wider text-sm">{{ __('Lire l’article') }} →</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ FAQ ============ --}}
<section id="faq" class="py-20 sm:py-24 scroll-mt-20 bg-[#f4f2ec]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <p class="ind-mono text-xs text-[#666]">SECTION 06 · {{ __('Questions') }}</p>
        <h2 class="mt-2 ind-h text-4xl sm:text-5xl uppercase leading-[0.95]">{{ __('Questions fréquentes') }}</h2>
        <div class="mt-10 border-2 border-[#111] bg-white" x-data="{ open: 0 }">
            @foreach ($faqs as $i => [$q, $a])
                <div class="{{ $loop->last ? '' : 'border-b-2 border-[#111]' }}">
                    <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center gap-4 px-5 py-4 text-start" :class="open === {{ $i }} && 'bg-[#ffcc00]'">
                        <span class="ind-mono text-xs text-[#666] shrink-0">Q{{ sprintf('%02d', $i + 1) }}</span>
                        <span class="flex-1 font-semibold">{{ __($q) }}</span>
                        <span class="ind-h text-2xl leading-none shrink-0" x-text="open === {{ $i }} ? '−' : '+'">+</span>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse @if ($i !== 0) x-cloak @endif class="px-5 pb-5 ps-14 text-[#444]">{{ __($a) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ CTA ============ --}}
<section class="bg-[#111] text-white">
    <div class="ind-hazard h-4"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 grid md:grid-cols-3 gap-8 items-center">
        <div class="md:col-span-2">
            <h2 class="ind-h text-4xl sm:text-5xl uppercase leading-[0.95]">{{ __('Commencez gratuitement') }}</h2>
            <p class="mt-3 text-[#cfcfcf] max-w-xl">{{ __('Créez votre espace en une minute, sans carte bancaire. Ou essayez d’abord la démo avec des données fictives.') }}</p>
        </div>
        <div class="flex flex-col gap-3">
            <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] text-[#111] justify-center">{{ __('Créer un compte gratuit') }}</a>
            <a href="{{ route('demo.login') }}" class="ind-btn bg-white text-[#111] justify-center">{{ __('Tester la démo') }}</a>
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="ind-btn bg-[#25d366] text-[#111] justify-center">{!! $waIcon !!} {{ __('Nous écrire sur WhatsApp') }}</a>
        </div>
    </div>
</section>

{{-- ============ FOOTER (shared with the blog) ============ --}}
<x-site-footer />

@livewireScripts
</body>
</html>
