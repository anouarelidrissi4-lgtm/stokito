<!DOCTYPE html>
@php $rtl = app()->getLocale() === 'ar'; @endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <x-favicons />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Stokito') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700{{ $rtl ? '|tajawal:400,500,700,800' : '' }}&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    {{-- industrial / dépôt style, same as the landing page (2026-11-23) --}}
    <body class="sp-guest text-zinc-900 antialiased bg-zinc-50">
        <div class="min-h-screen grid lg:grid-cols-2">

            {{-- left: brand panel (hidden on phones) --}}
            <div class="hidden lg:flex relative flex-col justify-between bg-zinc-900 text-white p-12 overflow-hidden">
                <a href="{{ url('/') }}" class="flex items-center gap-3 mt-4">
                    <span class="grid place-items-center w-10 h-10 rounded-lg bg-[#ffcc00] text-zinc-900"><x-sp-icon name="cube" class="w-6 h-6" /></span>
                    <span class="ind-h text-xl">Stokito</span>
                </a>
                <div>
                    <p class="text-sm font-medium text-zinc-400">{{ __('Logiciel de gestion de stock en ligne') }}</p>
                    <h1 class="mt-4 ind-h text-4xl xl:text-5xl leading-[1.1]">{{ __('Votre stock, vos devis et vos factures.') }}<br><span class="text-[#ffcc00]">{{ __('Au même endroit.') }}</span></h1>
                </div>
                <div class="flex items-end justify-between gap-6">
                    <div class="border border-zinc-700 bg-zinc-800/60 p-3 w-56 rounded-xl">
                        <div class="text-xs text-zinc-400">Stock · FER-RD-10</div>
                        <div class="ind-h text-lg mt-1">260 barres</div>
                    </div>
                    <p class="text-xs text-zinc-500">&copy; {{ date('Y') }} Stokito</p>
                </div>
            </div>

            {{-- right: form --}}
            <div class="relative flex flex-col items-center justify-center px-4 py-10">
                <div class="lg:hidden ind-hazard absolute top-0 inset-x-0 h-2"></div>

                <div x-data="{ open: false }" class="absolute top-5 {{ $rtl ? 'left-5' : 'right-5' }} z-10">
                    <button @click="open = ! open" type="button" class="h-9 px-3 border border-zinc-200 bg-white text-xs font-medium hover:bg-zinc-100 rounded-lg">{{ strtoupper(app()->getLocale()) }}</button>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute {{ $rtl ? 'left-0' : 'right-0' }} mt-2 w-36 bg-white border border-zinc-200 text-sm rounded-lg">
                        <a href="{{ route('locale.switch', 'fr') }}" class="block px-3 py-2 hover:bg-zinc-100">Français</a>
                        <a href="{{ route('locale.switch', 'ar') }}" class="block px-3 py-2 hover:bg-zinc-100">العربية</a>
                        <a href="{{ route('locale.switch', 'en') }}" class="block px-3 py-2 hover:bg-zinc-100">English</a>
                    </div>
                </div>

                <div class="w-full sm:max-w-xl">
                    <a href="{{ url('/') }}" class="lg:hidden flex items-center justify-center gap-3 mb-8">
                        <span class="grid place-items-center w-10 h-10 bg-[#ffcc00] rounded-lg"><x-sp-icon name="cube" class="w-6 h-6" /></span>
                        <span class="ind-h text-xl">Stokito</span>
                    </a>
                    <div class="relative bg-white border border-zinc-200 shadow-xl shadow-zinc-900/10 px-6 py-8 sm:px-8 rounded-xl">
                        {{ $slot }}
                    </div>
                    <p class="lg:hidden text-center text-xs text-zinc-500 mt-6">&copy; {{ date('Y') }} Stokito</p>
                </div>
            </div>
        </div>
    </body>
</html>
