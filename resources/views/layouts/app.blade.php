<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <x-favicons />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script>
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        </script>

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=barlow:400,500,600,700|barlow-condensed:600,700,800|ibm-plex-mono:400,500{{ app()->getLocale() === 'ar' ? '|tajawal:400,500,700,800' : '' }}&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.21.3/umd/index.min.js" defer></script>
        @livewireStyles
    </head>
    <body class="sp-app antialiased bg-[#f4f2ec]" x-data="{ nav: false }" @keydown.escape.window="nav = false">
        <div class="min-h-screen flex flex-col">

            <!-- Top bar -->
            <header class="sticky top-0 h-16 bg-[#111] border-b-4 border-[#ffcc00] flex items-center justify-between gap-2 px-3 sm:px-6 shrink-0 z-30 print:hidden">
                <div class="flex items-center gap-2 text-white min-w-0">
                    @if (auth()->user()->project_id)
                        <button type="button" @click="nav = true" class="sm:hidden p-2 -ms-1 rounded-lg text-slate-300 hover:bg-slate-800" aria-label="{{ __('Menu') }}"><x-sp-icon name="menu" class="w-6 h-6" /></button>
                    @endif
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 min-w-0">
                        <span class="grid place-items-center w-9 h-9 bg-[#ffcc00] text-[#111]"><x-sp-icon name="cube" class="w-5 h-5" /></span>
                        <span class="ind-h text-2xl tracking-wide uppercase">{{ config('app.name', 'Stokito') }}</span>
                    </a>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    @if (auth()->user()->project_id)
                        <livewire:global-search />
                    @endif
                    <!-- Dark mode toggle -->
                    <button type="button"
                        x-data="{ dark: document.documentElement.classList.contains('dark') }"
                        @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light')"
                        class="h-9 w-9 flex items-center justify-center rounded-lg text-slate-300 hover:bg-slate-800"
                        :title="dark ? '{{ __('Mode clair') }}' : '{{ __('Mode sombre') }}'">
                        <x-sp-icon name="moon" class="w-5 h-5" x-show="!dark" />
                        <x-sp-icon name="sun" class="w-5 h-5" x-show="dark" x-cloak />
                    </button>

                    <!-- Language switcher -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = ! open" type="button" class="flex items-center gap-1.5 h-9 px-2.5 rounded-lg text-slate-200 text-sm font-medium hover:bg-slate-800">
                            <x-sp-icon name="globe" class="w-4 h-4" /> {{ strtoupper(app()->getLocale()) }}
                        </button>
                        <div x-show="open" @click.outside="open = false" x-cloak
                            class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-40 bg-white rounded-lg shadow-lg overflow-hidden z-40 text-sm">
                            <a href="{{ route('locale.switch', 'fr') }}" class="block px-3 py-2 hover:bg-gray-100 text-gray-700">Français</a>
                            <a href="{{ route('locale.switch', 'en') }}" class="block px-3 py-2 hover:bg-gray-100 text-gray-700">English</a>
                            <a href="{{ route('locale.switch', 'ar') }}" class="block px-3 py-2 hover:bg-gray-100 text-gray-700">العربية</a>
                        </div>
                    </div>

                    <span class="hidden md:inline-flex items-center gap-1.5 h-9 px-3 border border-[#3a3a3a] text-slate-200 ind-mono text-xs max-w-[200px]">
                        <x-sp-icon name="user" class="w-4 h-4 shrink-0" /><span class="truncate">{{ auth()->user()->name }}</span>
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="h-9 w-9 sm:w-auto sm:px-3 flex items-center justify-center gap-1.5 rounded-lg text-slate-300 text-sm font-medium hover:bg-slate-800" title="{{ __('Deconnexion') }}">
                            <x-sp-icon name="logout" class="w-5 h-5" /><span class="hidden sm:inline">{{ __('Deconnexion') }}</span>
                        </button>
                    </form>
                </div>
            </header>

            <div class="flex flex-1 min-h-0">
                @include('layout.navigation')

                <div class="flex-1 flex flex-col min-w-0">
                    @if (auth()->user()->isDemo())
                        <div class="bg-[#ffcc00] text-[#111] border-b-2 border-[#111] ind-mono text-xs text-center py-1.5 px-4 print:hidden flex items-center justify-center gap-3 flex-wrap">
                            <span>{{ __('Mode demo - les donnees sont fictives et reinitialisees regulierement.') }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="underline hover:no-underline">{{ __('Quitter le mode demo') }}</button>
                            </form>
                        </div>
                    @endif
                    <!-- Page Heading -->
                    @if (isset($header))
                        <div class="px-4 sm:px-6 lg:px-8 pt-6 lg:pt-8 print:hidden">
                            {{ $header }}
                        </div>
                    @endif

                    <!-- Page Content -->
                    <main class="flex-1 min-w-0">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
