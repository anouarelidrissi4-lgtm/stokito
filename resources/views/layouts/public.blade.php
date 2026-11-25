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
        <x-favicons />
        <meta name="google-site-verification" content="XEy0-r-vrdATmXb-MhIP0hg5zhoY9-tW4Q5uWn-H0zY" />
        <meta name="description" content="{{ $description ?? 'Stokito est un logiciel de gestion de stock, de ventes et d’achats pour les PME.' }}">

        <title>{{ $title ?? 'Stokito - Logiciel de gestion de stock' }}</title>
        <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

        <meta property="og:site_name" content="Stokito">
        <meta property="og:image" content="{{ $ogImage ?? 'https://stokito.com/images/screens/screen-dashboard.webp?v=20261124' }}">
        <meta property="og:title" content="{{ $title ?? 'Stokito' }}">
        <meta property="og:description" content="{{ $description ?? 'Logiciel de gestion de stock, ventes et achats pour les PME.' }}">
        <meta property="og:type" content="{{ $ogType ?? 'website' }}">
        <meta property="og:url" content="{{ url()->current() }}">

        @if (isset($jsonLd))
            <script type="application/ld+json">{!! $jsonLd !!}</script>
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700{{ $rtl ? '|tajawal:400,500,700,800' : '' }}&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    {{-- simple / modern style, same as the landing page (2026-11-25) --}}
    <body class="sp-pub antialiased bg-zinc-50 text-zinc-900" x-data="{ menu: false }">

        <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-zinc-200 text-zinc-900">
            <div class="max-w-7xl mx-auto h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
                <a href="{{ $home }}" class="flex items-center gap-3 shrink-0">
                    <span class="grid place-items-center w-9 h-9 rounded-lg bg-[#ffcc00] text-zinc-900"><x-sp-icon name="cube" class="w-5 h-5" /></span>
                    <span class="text-base font-medium">Stokito</span>
                </a>
                <nav class="hidden xl:flex items-center gap-6 whitespace-nowrap text-sm font-medium text-zinc-600">
                    <a href="{{ $home }}#fonctionnalites" class="hover:text-zinc-900">{{ __('Fonctionnalités') }}</a>
                    <a href="{{ $home }}#ecrans" class="hover:text-zinc-900">{{ __('Écrans') }}</a>
                    <a href="{{ $home }}#guide" class="hover:text-zinc-900">{{ __('Guide') }}</a>
                    <a href="{{ $home }}#faq" class="hover:text-zinc-900">{{ __('Questions') }}</a>
                    <a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.*') ? 'text-zinc-900' : 'hover:text-zinc-900' }}">{{ __('Blog') }}</a>
                </nav>
                <div class="flex items-center gap-2">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button" class="h-9 px-2.5 border border-zinc-200 text-xs font-medium text-zinc-600 hover:border-zinc-400 rounded-lg">{{ strtoupper($locale) }}</button>
                        <div x-show="open" @click.outside="open = false" x-cloak class="absolute {{ $rtl ? 'left-0' : 'right-0' }} mt-2 w-36 bg-white text-zinc-900 border border-zinc-200 text-sm rounded-lg">
                            <a href="{{ route('locale.switch', 'fr') }}" class="block px-3 py-2 hover:bg-zinc-100">Français</a>
                            <a href="{{ route('locale.switch', 'ar') }}" class="block px-3 py-2 hover:bg-zinc-100">العربية</a>
                            <a href="{{ route('locale.switch', 'en') }}" class="block px-3 py-2 hover:bg-zinc-100">English</a>
                        </div>
                    </div>
                    @auth
                        <a href="{{ route('dashboard') }}" class="flex h-9 px-3 sm:px-4 items-center whitespace-nowrap bg-zinc-900 text-white text-sm font-medium hover:bg-zinc-700 rounded-lg"><span class="sm:hidden">{{ __('Mon espace') }}</span><span class="hidden sm:inline">{{ __('Tableau de bord') }}</span></a>
                    @else
                        <a href="{{ route('login') }}" class="flex h-9 px-2.5 sm:px-3 items-center whitespace-nowrap border border-zinc-200 text-sm font-medium hover:border-zinc-400 rounded-lg">{{ __('Se connecter') }}</a>
                        <a href="{{ route('register') }}" class="hidden sm:flex h-9 px-4 items-center whitespace-nowrap bg-zinc-900 text-white text-sm font-medium hover:bg-zinc-700 rounded-lg">{{ __('Créer un compte gratuit') }}</a>
                    @endauth
                    <button type="button" @click="menu = !menu" class="xl:hidden p-2 border border-zinc-200 rounded-lg" aria-label="Menu">
                        <x-sp-icon name="menu" class="w-6 h-6" x-show="!menu" /><x-sp-icon name="x" class="w-6 h-6" x-show="menu" x-cloak />
                    </button>
                </div>
            </div>
            <div x-show="menu" x-cloak @click="menu = false" class="xl:hidden border-t border-zinc-100 px-4 pb-4 pt-2 text-base font-medium">
                <a href="{{ $home }}#fonctionnalites" class="block py-2.5 border-b border-zinc-100">{{ __('Fonctionnalités') }}</a>
                <a href="{{ $home }}#ecrans" class="block py-2.5 border-b border-zinc-100">{{ __('Écrans') }}</a>
                <a href="{{ $home }}#guide" class="block py-2.5 border-b border-zinc-100">{{ __('Guide') }}</a>
                <a href="{{ $home }}#faq" class="block py-2.5 border-b border-zinc-100">{{ __('Questions') }}</a>
                <a href="{{ route('blog.index') }}" class="block py-2.5 border-b border-zinc-100">{{ __('Blog') }}</a>
                @guest
                    <a href="{{ route('register') }}" class="mt-3 flex h-12 items-center justify-center rounded-lg bg-zinc-900 text-white">{{ __('Créer un compte gratuit') }}</a>
                    <a href="{{ route('login') }}" class="mt-2 flex h-11 items-center justify-center border border-zinc-200 font-sans text-sm font-bold rounded-lg">{{ __('Se connecter') }}</a>
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
