{{--
    App sidebar (redesign 2026-11-22).
    - Same markup for desktop (fixed column) and mobile (slide-in drawer opened by
      the burger in the top bar, state `nav` lives on <body> in layouts/app).
    - Sections come from the "Menu latéral" config (Societe::menu_config), so the
      mobile menu now follows that config too.
    - The "Nouveau ..." items are menu rows under their list (like Nouveau Devis under Devis);
      quick create buttons live on the dashboard (2026-11-24). Sections fold; the one holding the current page is open.
--}}
@php
    $rtl = app()->getLocale() === 'ar';
    $user = auth()->user();
    $sectionIcons = ['tableau_de_bord' => 'home', 'catalogue' => 'cube', 'caisse' => 'receipt', 'ventes' => 'cart', 'achats' => 'truck', 'tresorerie' => 'wallet', 'administration' => 'cog'];
    $quickKeys = ['devis.create', 'bon-livraisons.create', 'commande-fournisseurs.create'];
    $isActive = fn ($item) => $item['active'] && request()->routeIs($item['active']);
    // every row gets an icon so it reads as a page link, not a heading (2026-11-25)
    $itemIcons = [
        'dashboard' => 'home', 'copilot' => 'chart', 'scanner.facture' => 'doc',
        'products.index' => 'cube', 'products.labels' => 'barcode', 'categories.index' => 'folder', 'brands.index' => 'folder', 'inventaire.index' => 'clock',
        'clients.index' => 'user', 'bons-de-chargement.index' => 'truck', 'reglements-clients.index' => 'coins', 'soldes-clients.index' => 'wallet', 'plafonds-clients.index' => 'alert',
        'fournisseurs.index' => 'user', 'reglements-fournisseurs.index' => 'coins', 'soldes-fournisseurs.index' => 'wallet',
        'caisse.pos' => 'receipt', 'caisse.tickets' => 'doc', 'caisse.clotures' => 'clock',
        'depenses.index' => 'wallet', 'depenses.recurrentes' => 'clock', 'tresorerie.comptes-bancaires' => 'wallet', 'tresorerie.porte-feuilles' => 'coins', 'cheques-fournisseurs.index' => 'doc', 'tresorerie.mouvements-bancaires' => 'chart',
        'statistiques.index' => 'chart', 'documents-externes.index' => 'folder', 'rappels.index' => 'clock', 'utilisateurs.index' => 'user', 'societe.edit' => 'cog',
    ];
    // document sub-groups (2026-11-25): an "Ajouter" row and a "Liste" row folded under the document name
    $docGroups = [
        'devis' => ['Devis', 'doc'], 'bon-livraisons' => ['Bons de livraison', 'truck'], 'factures' => ['Factures', 'doc'],
        'avoirs-clients' => ['Avoirs', 'coins'], 'commande-fournisseurs' => ['Bons de commande', 'cart'], 'avoirs-fournisseurs' => ['Avoirs', 'coins'],
    ];
    $groupOf = function ($item) use ($docGroups) {
        $k = $item['key'];
        foreach (['.create', '.index'] as $suf) {
            if (str_ends_with($k, $suf) && isset($docGroups[substr($k, 0, -strlen($suf))])) {
                return substr($k, 0, -strlen($suf));
            }
        }

        return null;
    };
    // consecutive items of the same group -> one entry ['group' => key, 'items' => [...]]
    $grouped = function ($items) use ($groupOf) {
        $out = [];
        foreach ($items as $it) {
            $g = $groupOf($it);
            $last = count($out) - 1;
            if ($g && $last >= 0 && ($out[$last]['group'] ?? null) === $g) {
                $out[$last]['items'][] = $it;
            } else {
                $out[] = ['group' => $g, 'items' => [$it]];
            }
        }

        return $out;
    };
    // industrial style (2026-11-23): active = yellow label with ink border
    $linkClass = fn ($on) => 'flex items-center gap-2.5 px-3 py-2 text-[14px] transition-colors rounded-lg '
        .($on ? 'bg-white text-zinc-900 font-semibold shadow-sm ring-1 ring-zinc-200' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 font-medium');
@endphp

<div x-show="nav" x-cloak @click="nav = false" class="fixed inset-0 z-40 bg-slate-900/50 sm:hidden print:hidden"></div>

<aside
    class="sp-sidebar fixed sm:sticky top-0 sm:top-16 z-50 sm:z-auto h-screen sm:h-[calc(100vh-4rem)] w-72 sm:w-64 shrink-0 overflow-y-auto bg-zinc-50 {{ $rtl ? 'right-0 border-l' : 'left-0 border-r' }} border-zinc-200 {{ $rtl ? 'translate-x-full' : '-translate-x-full' }} sm:!translate-x-0 transition-transform duration-200 print:hidden"
    :class="nav && '!translate-x-0'">

    <div class="flex items-center justify-between h-16 px-4 border-b border-slate-200 sm:hidden">
        <span class="flex items-center gap-2 ind-h text-xl"><span class="grid place-items-center w-8 h-8 bg-[#ffcc00] rounded-lg"><x-sp-icon name="cube" class="w-5 h-5" /></span> Stokito</span>
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
        $unsectioned = $visible($menu['unsectioned'])->values();
    @endphp
    <nav class="p-3 space-y-1">

        @foreach ($unsectioned as $item)
            <a href="{{ route($item['route']) }}" wire:navigate class="{{ $linkClass($isActive($item)) }}">{{ __($item['label']) }}</a>
        @endforeach

        @foreach ($menu['sections'] as $key => $section)
            @php
                $items = $visible($section['items'])->values();
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
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-[14px] font-semibold {{ $hasActive ? 'text-zinc-900' : 'text-zinc-700' }} hover:bg-zinc-100">
                        <x-sp-icon :name="$icon" class="w-[18px] h-[18px]" />
                        <span class="flex-1 text-start">{{ __($section['label']) }}</span>
                        <x-sp-icon name="chevron" class="w-4 h-4 text-slate-400 transition-transform {{ $rtl ? 'rotate-180' : '' }}" x-bind:class="open && '{{ $rtl ? '!-rotate-90' : 'rotate-90' }}'" />
                    </button>
                    <div x-show="open" x-collapse.duration.150ms class="mt-0.5 space-y-0.5 {{ $rtl ? 'pr-7' : 'pl-7' }}">
                        @foreach ($grouped($items) as $entry)
                            @if ($entry['group'] && count($entry['items']) > 1)
                                @php
                                    [$gLabel, $gIcon] = $docGroups[$entry['group']];
                                    $gActive = collect($entry['items'])->contains(fn ($i) => $isActive($i));
                                @endphp
                                <div x-data="{ sub: {{ $gActive ? 'true' : 'false' }} }">
                                    <button type="button" @click="sub = !sub" class="sp-subgroup w-full flex items-center gap-2.5 px-3 py-1.5 text-[14px] font-semibold rounded-lg {{ $gActive ? 'text-zinc-900' : 'text-zinc-700' }} hover:bg-zinc-100">
                                        <x-sp-icon :name="$gIcon" class="w-4 h-4 shrink-0 text-zinc-500" />
                                        <span class="flex-1 text-start">{{ __($gLabel) }}</span>
                                        <x-sp-icon name="chevron" class="w-3.5 h-3.5 text-zinc-400 transition-transform {{ $rtl ? 'rotate-180' : '' }}" x-bind:class="sub && '{{ $rtl ? '!-rotate-90' : 'rotate-90' }}'" />
                                    </button>
                                    <div x-show="sub" x-collapse.duration.150ms class="mt-0.5 space-y-0.5 {{ $rtl ? 'pr-4' : 'pl-4' }}">
                                        @foreach ($entry['items'] as $item)
                                            <a href="{{ route($item['route']) }}" wire:navigate class="{{ $linkClass($isActive($item)) }} !py-1.5 !text-[13.5px]"><span class="w-1.5 h-1.5 rounded-full ring-1 ring-current shrink-0 opacity-60"></span>{{ __($item['label']) }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                @foreach ($entry['items'] as $item)
                                    <a href="{{ route($item['route']) }}" wire:navigate class="{{ $linkClass($isActive($item)) }} !py-1.5 font-semibold"><x-sp-icon :name="str_ends_with($item['key'], '.create') ? 'plus' : ($itemIcons[$item['key']] ?? 'dot')" class="w-4 h-4 shrink-0 text-zinc-500" />{{ __($item['label']) }}</a>
                                @endforeach
                            @endif
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

        <div class="pt-3 mt-3 border-t border-zinc-200 space-y-0.5">
            <a href="/docs/guide-stokito-{{ in_array(app()->getLocale(), ['ar', 'en'], true) ? app()->getLocale() : 'fr' }}.pdf" target="_blank" rel="noopener" class="{{ $linkClass(false) }}">
                <x-sp-icon name="doc" class="w-[18px] h-[18px]" /> {{ __("Guide d'utilisation") }}
            </a>
            <a href="{{ route('profile') }}" wire:navigate class="{{ $linkClass(request()->routeIs('profile')) }}">
                <x-sp-icon name="user" class="w-[18px] h-[18px]" /> <span class="truncate">{{ $user->name }}</span>
            </a>
        </div>
    </nav>
@endif
</aside>
