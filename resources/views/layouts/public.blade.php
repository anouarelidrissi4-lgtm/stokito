@php
    $locale = app()->getLocale();
    $rtl = $locale === 'ar';
    $home = url('/');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="google-site-verification" content="XEy0-r-vrdATmXb-MhIP0hg5zhoY9-tW4Q5uWn-H0zY" />
        <meta name="description" content="{{ $description ?? 'Stokito est un logiciel de gestion de stock, de ventes et d’achats pour les PME.' }}">

        <title>{{ $title ?? 'Stokito - Logiciel de gestion de stock' }}</title>
        <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

        <meta property="og:site_name" content="Stokito">
        <meta property="og:image" content="{{ $ogImage ?? 'https://stokito.com/images/screens/screen-dashboard.webp?v=20261123' }}">
        <meta property="og:title" content="{{ $title ?? 'Stokito' }}">
        <meta property="og:description" content="{{ $description ?? 'Logiciel de gestion de stock, ventes et achats pour les PME.' }}">
        <meta property="og:type" content="{{ $ogType ?? 'website' }}">
        <meta property="og:url" content="{{ url()->current() }}">

        @if (isset($jsonLd))
            <script type="application/ld+json">{!! $jsonLd !!}</script>
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=barlow:400,500,600,700|barlow-condensed:600,700,800|ibm-plex-mono:400,500{{ $rtl ? '|tajawal:400,500,700,800' : '' }}&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <style>
            .sp-pub { font-family: 'Barlow', 'Tajawal', sans-serif; }
            [dir=rtl] .sp-pub { font-family: 'Tajawal', 'Barlow', sans-serif; }
            .ind-btn { display: inline-flex; align-items: center; gap: .5rem; height: 3rem; padding: 0 1.4rem; border: 2px solid #111; font-family: 'Barlow Condensed', 'Tajawal', sans-serif; font-weight: 800; font-size: 1.15rem; letter-spacing: .04em; text-transform: uppercase; box-shadow: 4px 4px 0 #111; transition: transform .12s, box-shadow .12s; }
            .ind-btn:hover { transform: translate(2px, 2px); box-shadow: 2px 2px 0 #111; }
        </style>
    </head>
    {{-- industrial / dépôt style, same as the landing page (2026-11-23) --}}
    <body class="sp-pub antialiased bg-[#f4f2ec] text-[#111]" x-data="{ menu: false }">

        <header class="sticky top-0 z-30 bg-[#111] text-white border-b-4 border-[#ffcc00]">
            <div class="max-w-7xl mx-auto h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
                <a href="{{ $home }}" class="flex items-center gap-3 shrink-0">
                    <span class="grid place-items-center w-9 h-9 bg-[#ffcc00] text-[#111]"><x-sp-icon name="cube" class="w-5 h-5" /></span>
                    <span class="ind-h text-2xl tracking-wide">STOKITO</span>
                </a>
                <nav class="hidden xl:flex items-center gap-6 whitespace-nowrap ind-h text-[15px] tracking-wider text-[#d6d6d6]">
                    <a href="{{ $home }}#fonctionnalites" class="hover:text-[#ffcc00]">{{ __('Fonctionnalités') }}</a>
                    <a href="{{ $home }}#ecrans" class="hover:text-[#ffcc00]">{{ __('Écrans') }}</a>
                    <a href="{{ $home }}#guide" class="hover:text-[#ffcc00]">{{ __('Guide') }}</a>
                    <a href="{{ $home }}#faq" class="hover:text-[#ffcc00]">{{ __('Questions') }}</a>
                    <a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.*') ? 'text-[#ffcc00]' : 'hover:text-[#ffcc00]' }}">{{ __('Blog') }}</a>
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
                    @auth
                        <a href="{{ route('dashboard') }}" class="hidden sm:flex h-9 px-4 items-center whitespace-nowrap bg-[#ffcc00] text-[#111] ind-h tracking-wider text-[15px] hover:bg-white">{{ __('Tableau de bord') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden md:flex h-9 px-3 items-center whitespace-nowrap border border-[#3a3a3a] ind-h tracking-wider text-[15px] hover:border-[#ffcc00]">{{ __('Se connecter') }}</a>
                        <a href="{{ route('register') }}" class="hidden sm:flex h-9 px-4 items-center whitespace-nowrap bg-[#ffcc00] text-[#111] ind-h tracking-wider text-[15px] hover:bg-white">{{ __('Créer un compte gratuit') }}</a>
                    @endauth
                    <button type="button" @click="menu = !menu" class="xl:hidden p-2 border border-[#3a3a3a]" aria-label="Menu">
                        <x-sp-icon name="menu" class="w-6 h-6" x-show="!menu" /><x-sp-icon name="x" class="w-6 h-6" x-show="menu" x-cloak />
                    </button>
                </div>
            </div>
            <div x-show="menu" x-cloak @click="menu = false" class="xl:hidden border-t border-[#2a2a2a] px-4 pb-4 pt-2 ind-h tracking-wider text-lg">
                <a href="{{ $home }}#fonctionnalites" class="block py-2.5 border-b border-[#2a2a2a]">{{ __('Fonctionnalités') }}</a>
                <a href="{{ $home }}#ecrans" class="block py-2.5 border-b border-[#2a2a2a]">{{ __('Écrans') }}</a>
                <a href="{{ $home }}#guide" class="block py-2.5 border-b border-[#2a2a2a]">{{ __('Guide') }}</a>
                <a href="{{ $home }}#faq" class="block py-2.5 border-b border-[#2a2a2a]">{{ __('Questions') }}</a>
                <a href="{{ route('blog.index') }}" class="block py-2.5 border-b border-[#2a2a2a]">{{ __('Blog') }}</a>
                @guest
                    <a href="{{ route('register') }}" class="mt-3 flex h-12 items-center justify-center bg-[#ffcc00] text-[#111]">{{ __('Créer un compte gratuit') }}</a>
                    <a href="{{ route('login') }}" class="mt-2 flex h-11 items-center justify-center border border-[#3a3a3a] font-sans text-sm font-bold">{{ __('Se connecter') }}</a>
                @endguest
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <x-site-footer />
        @livewireScripts
    </body>
</html>
