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
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700{{ app()->getLocale() === 'ar' ? '|tajawal:400,500,700,800' : '' }}&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.21.3/umd/index.min.js" defer></script>
        @livewireStyles
        @if (is_array(session('gtm_event')))@include('partials.analytics')@endif
    </head>
    <body class="sp-app antialiased bg-zinc-50" x-data="{ nav: false }" @keydown.escape.window="nav = false">
        <div class="min-h-screen flex flex-col">

            <!-- Top bar -->
            <header class="sp-topbar sticky top-0 h-16 backdrop-blur border-b border-zinc-200 flex items-center justify-between gap-2 px-3 sm:px-6 shrink-0 z-30 print:hidden">
                <div class="flex items-center gap-2 text-zinc-900 min-w-0">
                    @if (auth()->user()->project_id)
                        <button type="button" @click="nav = true" class="sm:hidden p-2 -ms-1 rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900" aria-label="{{ __('Menu') }}"><x-sp-icon name="menu" class="w-6 h-6" /></button>
                    @endif
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 min-w-0">
                        <span class="grid place-items-center w-9 h-9 rounded-lg bg-[#ffcc00] text-zinc-900"><x-sp-icon name="cube" class="w-5 h-5" /></span>
                        <span class="ind-h text-xl">{{ config('app.name', 'Stokito') }}</span>
                    </a>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    @if (auth()->user()->project_id)
                        <livewire:global-search />
                        @if (auth()->user()?->hasPermission('copilot'))
                            <a href="{{ route('copilot') }}" wire:navigate title="Copilot IA" class="hidden lg:flex items-center gap-1.5 h-9 px-3 border border-zinc-200 text-zinc-800 hover:bg-zinc-50 text-sm font-medium {{ request()->routeIs('copilot') ? '!bg-zinc-900 !text-white !border-zinc-900' : '' }} rounded-lg">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9z"/></svg>
                                Copilot
                            </a>
                        @endif
                    @endif
                    <!-- Dark mode toggle -->
                    <button type="button"
                        x-data="{ dark: document.documentElement.classList.contains('dark') }"
                        @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light')"
                        class="h-9 w-9 flex items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900"
                        :title="dark ? '{{ __('Mode clair') }}' : '{{ __('Mode sombre') }}'">
                        <x-sp-icon name="moon" class="w-5 h-5" x-show="!dark" />
                        <x-sp-icon name="sun" class="w-5 h-5" x-show="dark" x-cloak />
                    </button>

                    <!-- Language switcher -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = ! open" type="button" class="flex items-center gap-1.5 h-9 px-2.5 rounded-lg text-zinc-600 text-sm font-medium hover:bg-zinc-100">
                            <x-sp-icon name="globe" class="w-4 h-4" /> {{ strtoupper(app()->getLocale()) }}
                        </button>
                        <div x-show="open" @click.outside="open = false" x-cloak
                            class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-40 bg-white rounded-lg shadow-lg overflow-hidden z-40 text-sm">
                            <a href="{{ route('locale.switch', 'fr') }}" class="block px-3 py-2 hover:bg-gray-100 text-gray-700">Français</a>
                            <a href="{{ route('locale.switch', 'en') }}" class="block px-3 py-2 hover:bg-gray-100 text-gray-700">English</a>
                            <a href="{{ route('locale.switch', 'ar') }}" class="block px-3 py-2 hover:bg-gray-100 text-gray-700">العربية</a>
                        </div>
                    </div>

                    <span class="hidden md:inline-flex items-center gap-1.5 h-9 px-3 border border-zinc-200 text-zinc-600 text-xs max-w-[200px] rounded-lg">
                        <x-sp-icon name="user" class="w-4 h-4 shrink-0" /><span class="truncate">{{ auth()->user()->name }}</span>
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="h-9 w-9 sm:w-auto sm:px-3 flex items-center justify-center gap-1.5 rounded-lg text-zinc-500 text-sm font-medium hover:bg-zinc-100 hover:text-zinc-900" title="{{ __('Deconnexion') }}">
                            <x-sp-icon name="logout" class="w-5 h-5" /><span class="hidden sm:inline">{{ __('Deconnexion') }}</span>
                        </button>
                    </form>
                </div>
            </header>

            <div class="flex flex-1 min-h-0">
                @include('layout.navigation')

                <div class="flex-1 flex flex-col min-w-0">
                    @if (auth()->user()->isDemo())
                        <div class="bg-amber-50 text-amber-900 border-b border-amber-200 text-xs text-center py-1.5 px-4 print:hidden flex items-center justify-center gap-3 flex-wrap">
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
                    @if (auth()->user()?->hasPermission('copilot') && ! request()->routeIs('copilot', 'caisse.*'))
                        <a href="{{ route('copilot') }}" wire:navigate title="Copilot IA" class="lg:hidden fixed bottom-4 end-4 z-30 inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-900 text-white shadow-lg hover:bg-zinc-700 print:hidden">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9z"/></svg>
                            <span class="sr-only">Copilot</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
