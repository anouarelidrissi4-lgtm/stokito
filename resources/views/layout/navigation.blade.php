{{--
    App sidebar (redesign 2026-11-22).
    - Same markup for desktop (fixed column) and mobile (slide-in drawer opened by
      the burger in the top bar, state `nav` lives on <body> in layouts/app).
    - Sections come from the "Menu latéral" config (Societe::menu_config), so the
      mobile menu now follows that config too.
    - "Nouveau devis / BL / commande" links are shown in the "+ Nouveau" button,
      not as menu rows. Sections fold; the one holding the current page is open.
--}}
@php
    $rtl = app()->getLocale() === 'ar';
    $user = auth()->user();
    $sectionIcons = ['tableau_de_bord' => 'home', 'catalogue' => 'cube', 'ventes' => 'cart', 'achats' => 'truck', 'tresorerie' => 'wallet', 'administration' => 'cog'];
    $quickKeys = ['devis.create', 'bon-livraisons.create', 'commande-fournisseurs.create'];
    $isActive = fn ($item) => $item['active'] && request()->routeIs($item['active']);
    // industrial style (2026-11-23): active = yellow label with ink border
    $linkClass = fn ($on) => 'flex items-center gap-2.5 px-3 py-2 text-[14px] transition-colors border-2 '
        .($on ? 'bg-[#ffcc00] text-[#111] font-bold border-[#111]' : 'border-transparent text-[#333] hover:bg-white hover:border-[#111] font-medium');
@endphp

<div x-show="nav" x-cloak @click="nav = false" class="fixed inset-0 z-40 bg-slate-900/50 sm:hidden print:hidden"></div>

<aside
    class="sp-sidebar fixed sm:sticky top-0 sm:top-16 z-50 sm:z-auto h-screen sm:h-[calc(100vh-4rem)] w-72 sm:w-64 shrink-0 overflow-y-auto bg-[#f4f2ec] {{ $rtl ? 'right-0 border-l-2' : 'left-0 border-r-2' }} border-[#111] {{ $rtl ? 'translate-x-full' : '-translate-x-full' }} sm:!translate-x-0 transition-transform duration-200 print:hidden"
    :class="nav && '!translate-x-0'">

    <div class="flex items-center justify-between h-16 px-4 border-b border-slate-200 sm:hidden">
        <span class="flex items-center gap-2 ind-h text-2xl uppercase"><span class="grid place-items-center w-8 h-8 bg-[#ffcc00] border-2 border-[#111]"><x-sp-icon name="cube" class="w-5 h-5" /></span> Stokito</span>
        <button type="button" @click="nav = false" class="p-2 rounded-lg text-slate-500 hover:bg-slate-200" aria-label="Fermer le menu"><x-sp-icon name="x" /></button>
    </div>

@if (! $user->project_id)
    <nav class="p-3 space-y-1">
        <div class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ __('Administration globale') }}</div>
        <a href="{{ route('projects.index') }}" wire:navigate class="{{ $linkClass(request()->routeIs('projects.index')) }}"><x-sp-icon name="folder" class="w-4 h-4" /> {{ __('Projets') }}</a>
        <a href="{{ route('inscriptions.index') }}" wire:navigate class="{{ $linkClass(request()->routeIs('inscriptions.index')) }}"><x-sp-icon name="chart" class="w-4 h-4" /> {{ __('Inscriptions') }}</a>
        <a href="{{ route('blog-admin.index') }}" wire:navigate class="{{ $linkClass(request()->routeIs('blog-admin.*')) }}"><x-sp-icon name="doc" class="w-4 h-4" /> {{ __('Blog (site public)') }}</a>
        <a href="{{ route('profile') }}" wire:navigate class="{{ $linkClass(request()->routeIs('profile')) }}"><x-sp-icon name="user" class="w-4 h-4" /> {{ __('Profil') }}</a>
    </nav>
@else
    @php
        $societe = \App\Models\Societe::current();
        $menu = $societe->resolvedMenu();
        $visible = fn ($items) => collect($items)->filter(fn ($i) => \App\Support\MenuItems::visibleFor($i, $user, $societe));
        $all = $visible($menu['unsectioned'])->concat(collect($menu['sections'])->flatMap(fn ($s) => $visible($s['items'])));
        $quick = $all->filter(fn ($i) => in_array($i['key'], $quickKeys, true))->values();
        $unsectioned = $visible($menu['unsectioned'])->reject(fn ($i) => in_array($i['key'], $quickKeys, true))->values();
    @endphp
    <nav class="p-3 space-y-1">
        @if ($quick->isNotEmpty())
            <div x-data="{ q: false }" class="relative mb-3">
                <button type="button" @click="q = !q" class="w-full flex items-center justify-center gap-2 bg-[#111] text-[#ffcc00] border-2 border-[#111] px-3 py-2.5 ind-h text-lg uppercase tracking-wider shadow-[3px_3px_0_#ffcc00] hover:bg-black">
                    <x-sp-icon name="plus" class="w-4 h-4" /> {{ __('Nouveau') }}
                </button>
                <div x-show="q" x-cloak @click.outside="q = false" class="absolute inset-x-0 mt-2 border-2 border-[#111] bg-white py-1 z-10">
                    @foreach ($quick as $item)
                        <a href="{{ route($item['route']) }}" wire:navigate class="block px-3 py-2 text-sm font-semibold hover:bg-[#ffcc00]">+ {{ __($item['label']) }}</a>
                    @endforeach
                </div>
            </div>
        @endif

        @foreach ($unsectioned as $item)
            <a href="{{ route($item['route']) }}" wire:navigate class="{{ $linkClass($isActive($item)) }}">{{ __($item['label']) }}</a>
        @endforeach

        @foreach ($menu['sections'] as $key => $section)
            @php
                $items = $visible($section['items'])->reject(fn ($i) => in_array($i['key'], $quickKeys, true))->values();
                $super = $key === 'administration' && $user->isSuperAdmin();
                $hasActive = $items->contains(fn ($i) => $isActive($i)) || ($super && request()->routeIs('projects.index', 'inscriptions.index', 'blog-admin.*'));
                $icon = $sectionIcons[$key] ?? 'folder';
            @endphp
            @continue($items->isEmpty() && ! $super)

            @if ($items->count() === 1 && ! $super)
                {{-- one-link section (e.g. Tableau de bord): a plain row, no folding --}}
                <a href="{{ route($items[0]['route']) }}" wire:navigate class="{{ $linkClass($isActive($items[0])) }}">
                    <x-sp-icon :name="$icon" class="w-[18px] h-[18px]" /> {{ __($items[0]['label']) }}
                </a>
            @else
                <div x-data="{ open: {{ $hasActive ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open"
                        class="w-full flex items-center gap-2.5 px-3 py-2 ind-h text-[16px] uppercase tracking-wider {{ $hasActive ? 'text-[#111]' : 'text-[#333]' }} hover:bg-white">
                        <x-sp-icon :name="$icon" class="w-[18px] h-[18px]" />
                        <span class="flex-1 text-start">{{ __($section['label']) }}</span>
                        <x-sp-icon name="chevron" class="w-4 h-4 text-slate-400 transition-transform {{ $rtl ? 'rotate-180' : '' }}" x-bind:class="open && '{{ $rtl ? '!-rotate-90' : 'rotate-90' }}'" />
                    </button>
                    <div x-show="open" x-collapse.duration.150ms class="mt-0.5 space-y-0.5 {{ $rtl ? 'pr-7' : 'pl-7' }}">
                        @foreach ($items as $item)
                            <a href="{{ route($item['route']) }}" wire:navigate class="{{ $linkClass($isActive($item)) }} !py-1.5">{{ __($item['label']) }}</a>
                        @endforeach
                        @if ($super)
                            <a href="{{ route('projects.index') }}" wire:navigate class="{{ $linkClass(request()->routeIs('projects.index')) }} !py-1.5">{{ __('Projets (global)') }}</a>
                            <a href="{{ route('inscriptions.index') }}" wire:navigate class="{{ $linkClass(request()->routeIs('inscriptions.index')) }} !py-1.5">{{ __('Inscriptions') }}</a>
                            <a href="{{ route('blog-admin.index') }}" wire:navigate class="{{ $linkClass(request()->routeIs('blog-admin.*')) }} !py-1.5">{{ __('Blog (site public)') }}</a>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach

        <div class="pt-3 mt-3 border-t-2 border-[#111] space-y-0.5">
            <a href="/docs/guide-stockpilot-{{ in_array(app()->getLocale(), ['ar', 'en'], true) ? app()->getLocale() : 'fr' }}.pdf" target="_blank" rel="noopener" class="{{ $linkClass(false) }}">
                <x-sp-icon name="doc" class="w-[18px] h-[18px]" /> {{ __("Guide d'utilisation") }}
            </a>
            <a href="{{ route('profile') }}" wire:navigate class="{{ $linkClass(request()->routeIs('profile')) }}">
                <x-sp-icon name="user" class="w-[18px] h-[18px]" /> <span class="truncate">{{ $user->name }}</span>
            </a>
        </div>
    </nav>
@endif
</aside>
