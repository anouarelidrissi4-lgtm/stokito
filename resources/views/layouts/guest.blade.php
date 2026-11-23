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
        <link href="https://fonts.bunny.net/css?family=barlow:400,500,600,700|barlow-condensed:600,700,800|ibm-plex-mono:400,500{{ $rtl ? '|tajawal:400,500,700,800' : '' }}&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    {{-- industrial / dépôt style, same as the landing page (2026-11-23) --}}
    <body class="sp-guest text-[#111] antialiased bg-[#f4f2ec]">
        <div class="min-h-screen grid lg:grid-cols-2">

            {{-- left: brand panel (hidden on phones) --}}
            <div class="hidden lg:flex relative flex-col justify-between bg-[#111] text-white p-12 overflow-hidden">
                <div class="ind-hazard absolute top-0 inset-x-0 h-3"></div>
                <a href="{{ url('/') }}" class="flex items-center gap-3 mt-4">
                    <span class="grid place-items-center w-10 h-10 bg-[#ffcc00] text-[#111]"><x-sp-icon name="cube" class="w-6 h-6" /></span>
                    <span class="ind-h text-3xl tracking-wide">STOKITO</span>
                </a>
                <div>
                    <p class="ind-mono text-xs text-[#ffcc00]">REF · SP-01 · {{ __('Logiciel de gestion de stock en ligne') }}</p>
                    <h1 class="mt-4 ind-h text-6xl xl:text-7xl uppercase leading-[0.9]">{{ __('Votre stock, vos devis et vos factures.') }}<br><span class="text-[#ffcc00]">{{ __('Au même endroit.') }}</span></h1>
                </div>
                <div class="flex items-end justify-between gap-6">
                    <div class="border-2 border-[#ffcc00] p-3 w-56">
                        <div class="ind-mono text-[10px] text-[#ffcc00]">ÉTIQUETTE STOCK · FER-RD-10</div>
                        <div class="ind-h text-2xl mt-1">260 BARRES</div>
                        <div class="ind-bc h-6 mt-2 invert"></div>
                    </div>
                    <p class="ind-mono text-[11px] text-[#777]">&copy; {{ date('Y') }} STOKITO</p>
                </div>
            </div>

            {{-- right: form --}}
            <div class="relative flex flex-col items-center justify-center px-4 py-10">
                <div class="lg:hidden ind-hazard absolute top-0 inset-x-0 h-2"></div>

                <div x-data="{ open: false }" class="absolute top-5 {{ $rtl ? 'left-5' : 'right-5' }} z-10">
                    <button @click="open = ! open" type="button" class="h-9 px-3 border-2 border-[#111] bg-white ind-mono text-xs hover:bg-[#ffcc00]">{{ strtoupper(app()->getLocale()) }}</button>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute {{ $rtl ? 'left-0' : 'right-0' }} mt-2 w-36 bg-white border-2 border-[#111] text-sm">
                        <a href="{{ route('locale.switch', 'fr') }}" class="block px-3 py-2 hover:bg-[#ffcc00]">Français</a>
                        <a href="{{ route('locale.switch', 'ar') }}" class="block px-3 py-2 hover:bg-[#ffcc00]">العربية</a>
                        <a href="{{ route('locale.switch', 'en') }}" class="block px-3 py-2 hover:bg-[#ffcc00]">English</a>
                    </div>
                </div>

                <div class="w-full sm:max-w-xl">
                    <a href="{{ url('/') }}" class="lg:hidden flex items-center justify-center gap-3 mb-8">
                        <span class="grid place-items-center w-10 h-10 bg-[#ffcc00] border-2 border-[#111]"><x-sp-icon name="cube" class="w-6 h-6" /></span>
                        <span class="ind-h text-3xl tracking-wide">STOKITO</span>
                    </a>
                    <div class="relative bg-white border-2 border-[#111] shadow-[8px_8px_0_#111] px-6 py-8 sm:px-8">
                        <div class="absolute -top-3.5 {{ $rtl ? 'right-6' : 'left-6' }} ind-mono text-[11px] bg-[#ffcc00] border-2 border-[#111] px-2 py-0.5">ACCÈS · SP</div>
                        {{ $slot }}
                    </div>
                    <p class="lg:hidden text-center ind-mono text-[11px] text-[#777] mt-6">&copy; {{ date('Y') }} STOKITO</p>
                </div>
            </div>
        </div>
    </body>
</html>
