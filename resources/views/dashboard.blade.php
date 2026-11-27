<x-app-layout>
    @php
        $u = auth()->user();
        $max = max(1, $months->max('total'));
        $money = fn ($v) => \App\Support\Fmt::money($v);
        $card = 'bg-white border border-zinc-200 rounded-xl shadow-sm';
    @endphp

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl">

        {{-- greeting + quick actions --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm text-zinc-500 first-letter:uppercase">{{ now()->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY') }}</p>
                <h1 class="ind-h text-3xl leading-tight mt-1">{{ __('Bonjour') }}, {{ $u->first_name ?: $u->name }}</h1>
            </div>
            @php
                $creates = array_values(array_filter([
                    $u->hasPermission('devis_creer') ? [route('devis.create'), __('Devis'), 'doc', null] : null,
                    $u->hasPermission('factures_consulter') ? [route('factures.create'), __('Facture'), 'doc', null] : null,
                    $u->hasPermission('bl_creer') ? [route('bon-livraisons.create'), __('Bon de livraison'), 'truck', null] : null,
                    $u->hasPermission('gestion_commandes_fournisseurs') ? [route('commande-fournisseurs.create'), __('Bon de commande'), 'cart', null] : null,
                    $u->hasPermission('gestion_commandes_fournisseurs') ? [route('commande-fournisseurs.create'), __('Scanner une facture fournisseur'), 'plus', 'IA'] : null,
                ]));
            @endphp
            @if ($creates)
                {{-- one "+ Nouveau" button instead of four (2026-11-24): opens a menu on click --}}
                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                    <button type="button" @click="open = ! open" :aria-expanded="open" aria-haspopup="true"
                        class="inline-flex items-center gap-2 bg-[#16a34a] text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm hover:bg-[#15803d]">
                        <x-sp-icon name="plus" class="w-4 h-4" /> {{ __('Ajouter') }}
                        <svg class="w-4 h-4 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="open" x-cloak x-transition.origin.top
                        class="absolute start-0 sm:start-auto sm:end-0 z-30 mt-2 w-64 bg-white border border-zinc-200 shadow-lg rounded-xl overflow-hidden" role="menu">
                        @foreach ($creates as [$href, $label, $icon, $badge])
                            <a href="{{ $href }}" wire:navigate role="menuitem"
                                class="flex items-center gap-3 px-4 py-3 text-[15px] font-semibold hover:bg-zinc-100 {{ $loop->last ? '' : 'border-b border-zinc-200' }}">
                                <x-sp-icon :name="$icon" class="w-4 h-4 shrink-0" />
                                <span class="flex-1">{{ $label }}</span>
                                @if ($badge)<span class="ind-mono text-[10px] bg-zinc-900 text-white px-1.5 py-0.5">{{ $badge }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- getting started (new self-service accounts, until the first product exists) --}}
        @if (! $u->is_demo && \App\Models\Product::count() === 0)
            <div class="bg-white border border-zinc-200 shadow-lg shadow-zinc-900/5 rounded-xl">
                <div class="p-5">
                    <p class="text-xs text-zinc-400">{{ __('Démarrage') }} · 3 {{ __('étapes') }}</p>
                    <h2 class="ind-h text-2xl leading-tight mt-1">{{ __('Bienvenue ! Votre espace est prêt.') }}</h2>
                    <div class="mt-4 grid md:grid-cols-3 gap-3">
                        @foreach ([[route('societe.edit'), '01', __('Complétez Ma Société'), __('Logo, adresse, identifiants : ils apparaissent sur vos PDF.')],
                                   [route('products.index'), '02', __('Ajoutez vos produits'), __('Un par un, ou importez votre fichier Excel en une fois.')],
                                   [route('devis.create'), '03', __('Faites votre premier devis'), __('Choisissez un client, ajoutez les lignes, imprimez le PDF.')]] as [$href, $n, $t, $d])
                            <a href="{{ $href }}" wire:navigate class="block border border-zinc-200 p-4 hover:bg-zinc-100 rounded-xl">
                                <div class="ind-h text-3xl leading-none text-zinc-300">{{ $n }}</div>
                                <div class="mt-2 text-base font-semibold">{{ $t }}</div>
                                <div class="text-sm text-zinc-600">{{ $d }}</div>
                            </a>
                        @endforeach
                    </div>
                    <p class="mt-4 text-sm text-zinc-600">{{ __('Besoin d’aide pour démarrer ?') }} <a href="https://wa.me/212676035777" target="_blank" rel="noopener" class="font-semibold underline decoration-amber-400 decoration-2">{{ __('Écrivez-nous sur WhatsApp') }}</a>.</p>
                </div>
            </div>
        @endif

        {{-- user guide --}}
        <div class="flex flex-wrap items-center gap-3 border border-zinc-200 bg-white shadow-sm px-4 py-3 rounded-xl">
            <span class="grid place-items-center w-9 h-9 rounded-lg bg-[#ffcc00] text-zinc-900 shrink-0"><x-sp-icon name="doc" class="w-5 h-5" /></span>
            <div class="flex-1 min-w-[200px]">
                <div class="text-sm font-semibold text-slate-900">{{ __("Guide d'utilisation") }}</div>
                <div class="text-xs text-slate-500">{{ __('Tout Stokito expliqué pas à pas, à garder ou à partager avec votre équipe.') }}</div>
            </div>
            <a href="/docs/guide-stokito-fr.pdf" target="_blank" rel="noopener" class="inline-flex items-center bg-white border border-zinc-200 px-3 py-1.5 text-sm font-bold hover:bg-zinc-50 rounded-lg">Français (PDF)</a>
            <a href="/docs/guide-stokito-ar.pdf" target="_blank" rel="noopener" class="inline-flex items-center bg-white border border-zinc-200 px-3 py-1.5 text-sm font-bold hover:bg-zinc-50 rounded-lg" dir="rtl">العربية (PDF)</a>
            <a href="/docs/guide-stokito-en.pdf" target="_blank" rel="noopener" class="inline-flex items-center bg-white border border-zinc-200 px-3 py-1.5 text-sm font-bold hover:bg-zinc-50 rounded-lg">English (PDF)</a>
        </div>

        {{-- KPIs (2026-11-25): big filled cards like the user's reference, only Stokito colours (ink / yellow / white) --}}
        @php
            $kpi = 'relative overflow-hidden rounded-2xl p-4 sm:p-5 min-h-[128px] flex flex-col transition';
            $dark = $kpi.' bg-gradient-to-br from-zinc-900 to-zinc-800 text-white shadow-lg shadow-zinc-900/10 hover:shadow-xl';
            $yellow = $kpi.' on-yellow bg-gradient-to-br from-[#ffd84d] to-[#ffc400] text-zinc-900 shadow-lg shadow-amber-500/20 hover:shadow-xl';
            $light = $kpi.' bg-white border border-zinc-200 text-zinc-900 shadow-sm hover:border-zinc-300 hover:shadow';
            $lab = 'text-[11px] font-semibold uppercase tracking-wider';
            $val = 'mt-3 ind-h text-2xl sm:text-3xl leading-none tabular-nums';
            $cur = 'text-sm font-medium opacity-60';
            $icoDark = 'grid place-items-center w-9 h-9 shrink-0 rounded-xl bg-[#ffcc00] text-zinc-900';
            $icoYellow = 'grid place-items-center w-9 h-9 shrink-0 rounded-xl bg-zinc-900 text-[#ffcc00]';
            $icoLight = 'grid place-items-center w-9 h-9 shrink-0 rounded-xl bg-[#ffcc00]/25 text-zinc-900';
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="{{ $dark }}">
                <span aria-hidden="true" class="pointer-events-none absolute -end-10 -bottom-12 w-36 h-36 rounded-full bg-white/5"></span>
                <div class="flex items-start justify-between gap-3"><span class="{{ $lab }} text-white/70">{{ __('CA du mois') }}</span><span class="{{ $icoDark }}"><x-sp-icon name="chart" class="w-5 h-5" /></span></div>
                <div class="{{ $val }}">{{ $money($caMois) }} <span class="{{ $cur }}">{{ $currency }}</span></div>
                @if ($caEvolution !== null)
                    <div class="mt-auto pt-2 text-xs font-semibold {{ $caEvolution >= 0 ? 'text-[#ffcc00]' : 'text-white/70' }}">{{ $caEvolution >= 0 ? '▲' : '▼' }} {{ abs($caEvolution) }}% {{ __('vs mois dernier') }}</div>
                @else
                    <div class="mt-auto pt-2 text-xs text-white/60">{{ __('Factures TTC du mois') }}</div>
                @endif
            </div>
            <div class="{{ $yellow }}">
                <span aria-hidden="true" class="pointer-events-none absolute -end-10 -bottom-12 w-36 h-36 rounded-full bg-white/25"></span>
                <div class="flex items-start justify-between gap-3"><span class="{{ $lab }} text-zinc-900/70">{{ __('Encaissé ce mois') }}</span><span class="{{ $icoYellow }}"><x-sp-icon name="coins" class="w-5 h-5" /></span></div>
                <div class="{{ $val }}">{{ $money($encaisseMois) }} <span class="{{ $cur }}">{{ $currency }}</span></div>
                <div class="mt-auto pt-2 text-xs text-zinc-900/70">{{ __('Règlements clients') }}</div>
            </div>
            <a href="{{ route('factures.index') }}" wire:navigate class="{{ $dark }}">
                <span aria-hidden="true" class="pointer-events-none absolute -end-10 -bottom-12 w-36 h-36 rounded-full bg-white/5"></span>
                <div class="flex items-start justify-between gap-3"><span class="{{ $lab }} text-white/70">{{ __('Impayés') }}</span><span class="{{ $icoDark }}"><x-sp-icon name="clock" class="w-5 h-5" /></span></div>
                <div class="{{ $val }}">{{ $money($totalImpayes) }} <span class="{{ $cur }}">{{ $currency }}</span></div>
                <div class="mt-auto pt-2 text-xs text-white/60">{{ trans_choice('{0} Aucune facture|{1} :count facture|[2,*] :count factures', $impayes->count(), ['count' => $impayes->count()]) }}</div>
            </a>
            <a href="{{ route('products.index') }}" wire:navigate class="{{ $yellow }}">
                <span aria-hidden="true" class="pointer-events-none absolute -end-10 -bottom-12 w-36 h-36 rounded-full bg-white/25"></span>
                <div class="flex items-start justify-between gap-3"><span class="{{ $lab }} text-zinc-900/70">{{ __('Stock bas') }}</span><span class="{{ $icoYellow }}"><x-sp-icon name="alert" class="w-5 h-5" /></span></div>
                <div class="{{ $val }}">{{ $lowStock->count() }} <span class="{{ $cur }}">{{ __('produits') }}</span></div>
                <div class="mt-auto pt-2 text-xs text-zinc-900/70">{{ $devisEnCours }} {{ __('devis en cours') }}</div>
            </a>
        </div>

        {{-- KPIs, second row (2026-11-24): same layout, white --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @if ($u->hasPermission('marges'))
            <div class="{{ $light }}">
                <div class="flex items-start justify-between gap-3"><span class="{{ $lab }} text-zinc-500">{{ __('Marge brute du mois') }}</span><span class="{{ $icoLight }}"><x-sp-icon name="chart" class="w-5 h-5" /></span></div>
                <div class="{{ $val }} {{ $margeMois < 0 ? 'text-red-600' : '' }}">{{ $money($margeMois) }} <span class="{{ $cur }}">{{ $currency }}</span></div>
                <div class="mt-auto pt-2 text-xs text-zinc-500">{{ $margePct !== null ? $margePct.' % '.__('du CA HT') : __('Aucune facture ce mois') }}</div>
            </div>
            @endif
            @if ($u->hasPermission('depenses'))
            <a href="{{ route('depenses.index') }}" wire:navigate class="{{ $light }}">
                <div class="flex items-start justify-between gap-3"><span class="{{ $lab }} text-zinc-500">{{ __('Dépenses du mois') }}</span><span class="{{ $icoLight }}"><x-sp-icon name="wallet" class="w-5 h-5" /></span></div>
                <div class="{{ $val }}">{{ $money($depensesMoisTtc) }} <span class="{{ $cur }}">{{ $currency }}</span></div>
                @if ($u->hasPermission('marges'))
                    @php $resultat = $margeMois - $depensesMoisHt; @endphp
                    <div class="mt-auto pt-2 text-xs {{ $resultat < 0 ? 'text-red-600 font-semibold' : 'text-zinc-500' }}">{{ __('Marge moins dépenses (HT)') }} : {{ $money($resultat) }}</div>
                @else
                    <div class="mt-auto pt-2 text-xs text-zinc-500">{{ __('Loyer, énergie, carburant…') }}</div>
                @endif
            </a>
            @endif
            @if ($u->hasPermission('produits_prix_achat'))
            <a href="{{ route('products.index') }}" wire:navigate class="{{ $light }}">
                <div class="flex items-start justify-between gap-3"><span class="{{ $lab }} text-zinc-500">{{ __('Valeur du stock') }}</span><span class="{{ $icoLight }}"><x-sp-icon name="cube" class="w-5 h-5" /></span></div>
                <div class="{{ $val }}">{{ $money($valeurStock) }} <span class="{{ $cur }}">{{ $currency }}</span></div>
                <div class="mt-auto pt-2 text-xs {{ $dormants ? 'text-zinc-900 font-semibold' : 'text-zinc-500' }}">{{ $dormants ? $dormants.' '.__('produits sans vente depuis 90 j') : __('Au prix d’achat HT') }}</div>
            </a>
            @endif
            <a href="{{ route('devis.index') }}" wire:navigate class="{{ $light }}">
                <div class="flex items-start justify-between gap-3"><span class="{{ $lab }} text-zinc-500">{{ __('Devis en attente') }}</span><span class="{{ $icoLight }}"><x-sp-icon name="doc" class="w-5 h-5" /></span></div>
                <div class="{{ $val }}">{{ $money($devisAttenteMontant) }} <span class="{{ $cur }}">{{ $currency }}</span></div>
                <div class="mt-auto pt-2 text-xs text-zinc-500">{{ $devisAttenteCount }} {{ __('devis') }}@if ($tauxAcceptation !== null) · {{ $tauxAcceptation }} % {{ __('acceptés (90 j)') }}@endif</div>
            </a>
            <div class="{{ $light }}">
                <div class="flex items-start justify-between gap-3"><span class="{{ $lab }} text-zinc-500">{{ __('TVA facturée ce mois') }}</span><span class="{{ $icoLight }}"><x-sp-icon name="coins" class="w-5 h-5" /></span></div>
                <div class="{{ $val }}">{{ $money($tvaMois) }} <span class="{{ $cur }}">{{ $currency }}</span></div>
                <div class="mt-auto pt-2 text-xs text-zinc-500">{{ __('Sur les factures du mois') }}</div>
            </div>
        </div>

        <div class="grid lg:grid-cols-5 gap-4 sm:gap-6">
            {{-- 6-month sales chart --}}
            <div class="{{ $card }} p-5 lg:col-span-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold">{{ __('Ventes des 6 derniers mois') }}</h2>
                    <span class="text-xs text-slate-400">{{ __('Factures TTC') }}, {{ $currency }}</span>
                </div>
                <div class="mt-6 flex items-end gap-2 sm:gap-4 h-48">
                    @foreach ($months as $m)
                        <div class="flex-1 flex flex-col items-center justify-end h-full group">
                            <div class="text-[11px] font-semibold text-slate-500 mb-1 opacity-0 group-hover:opacity-100 sm:opacity-100 whitespace-nowrap">{{ $m['total'] > 0 ? number_format($m['total'] / 1000, 1, ',', ' ').'k' : '' }}</div>
                            <div class="w-full max-w-[56px] {{ $loop->last ? 'bg-[#ffcc00]' : 'bg-zinc-800 group-hover:bg-zinc-600' }} rounded-md"
                                style="height: {{ $m['total'] > 0 ? max(4, round($m['total'] / $max * 100)) : 2 }}%" title="{{ $money($m['total']) }} {{ $currency }}"></div>
                            <div class="mt-2 text-xs {{ $loop->last ? 'font-semibold text-slate-800' : 'text-slate-500' }}">{{ $m['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- unpaid invoices --}}
            <div class="{{ $card }} lg:col-span-2 overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold">{{ __('À encaisser') }}</h2>
                        @if ($aEncaisser30 > 0)<div class="text-xs text-slate-500">{{ __('Sous 30 jours') }} : <strong class="text-slate-900">{{ $money($aEncaisser30) }} {{ $currency }}</strong></div>@endif
                    </div>
                    <a href="{{ route('factures.index') }}" wire:navigate class="text-sm font-medium text-zinc-500 hover:text-zinc-900">{{ __('Toutes les factures') }}</a>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($impayes->take(5) as $f)
                        @php $late = $f->due_date && $f->due_date->isPast(); @endphp
                        <li class="flex items-center">
                            <a href="{{ route('factures.edit', $f->id) }}" wire:navigate class="flex-1 min-w-0 flex items-center justify-between gap-3 ps-5 pe-3 py-3 hover:bg-slate-50">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900">{{ $f->numero }}</div>
                                    <div class="text-xs text-slate-500 truncate">{{ $f->client->name ?? '-' }}</div>
                                </div>
                                <div class="text-end shrink-0">
                                    <div class="text-sm font-semibold text-slate-900">{{ $money($f->total_ttc - $f->montant_paye) }}</div>
                                    <div class="text-[11px] {{ $late ? 'text-rose-600 font-semibold' : 'text-slate-400' }}">
                                        {{ $f->due_date ? ($late ? __('En retard depuis le').' '.$f->due_date->format('d/m') : __('Échéance').' '.$f->due_date->format('d/m')) : '' }}
                                    </div>
                                </div>
                            </a>
                            @if ($wa = \App\Support\Relance::whatsappUrl($f))
                                <a href="{{ $wa }}" target="_blank" rel="noopener" class="me-3 grid place-items-center w-8 h-8 rounded-lg text-emerald-600 hover:bg-emerald-50" title="{{ __('Relancer sur WhatsApp') }}">
                                    <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3z"/></svg>
                                </a>
                            @endif
                        </li>
                    @empty
                        <li class="px-5 py-10 text-center text-sm text-slate-400">{{ __('Aucune facture impayée') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- top products / clients, last 90 days (2026-11-24) --}}
        <div class="grid lg:grid-cols-2 gap-4 sm:gap-6">
            @foreach ([[__('Top produits'), $topProduits, __('CA HT')], [__('Top clients'), $topClients, __('CA TTC')]] as [$titre, $rows, $unite])
                @php $topMax = max(1, $rows->max('ca')); @endphp
                <div class="{{ $card }} overflow-hidden">
                    <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                        <h2 class="text-base font-semibold">{{ $titre }}</h2>
                        <span class="text-xs text-zinc-400">{{ __('90 derniers jours') }} · {{ $unite }}</span>
                    </div>
                    @forelse ($rows as $i => $r)
                        <div class="px-5 py-3 {{ $loop->last ? '' : 'border-b border-slate-100' }}">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm text-slate-800 truncate"><span class="ind-mono text-[11px] text-zinc-500 me-2">{{ sprintf('%02d', $i + 1) }}</span>{{ $r['name'] }}</span>
                                <span class="text-sm font-semibold text-slate-900 shrink-0">{{ $money($r['ca']) }}</span>
                            </div>
                            <div class="mt-2 h-1.5 bg-zinc-100 overflow-hidden rounded-full"><div class="h-full rounded-full {{ $i === 0 ? 'bg-[#ffcc00]' : 'bg-zinc-900' }}" style="width: {{ max(3, round($r['ca'] / $topMax * 100)) }}%"></div></div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate-400">{{ __('Aucune vente sur la période') }}</div>
                    @endforelse
                </div>
            @endforeach
        </div>

        {{-- low stock --}}
        <div class="{{ $card }} overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                <h2 class="text-base font-semibold">{{ __('Produits en alerte de stock') }}</h2>
                <a href="{{ route('products.index') }}" wire:navigate class="text-sm font-medium text-zinc-500 hover:text-zinc-900">{{ __('Voir les produits') }}</a>
            </div>
            @if ($lowStock->isEmpty())
                <div class="px-5 py-10 text-center text-sm text-slate-400">{{ __('Aucun produit en alerte') }}</div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 divide-y sm:divide-y-0 divide-slate-100">
                    @foreach ($lowStock->take(9) as $product)
                        @php $pct = $product->stock_alert_threshold ? min(100, round(max(0, $product->stock_qty) / $product->stock_alert_threshold * 100)) : 0; @endphp
                        <div class="px-5 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm text-slate-800 truncate">{{ $product->name }}</span>
                                <span class="text-sm font-semibold {{ $product->stock_qty <= 0 ? 'text-rose-600' : 'text-amber-600' }} shrink-0">{{ \App\Support\Fmt::qty($product->stock_qty) }} / {{ $product->stock_alert_threshold !== null ? \App\Support\Fmt::qty($product->stock_alert_threshold) : '-' }}</span>
                            </div>
                            <div class="mt-2 h-1.5 bg-zinc-100 overflow-hidden rounded-full"><div class="h-full rounded-full {{ $product->stock_qty <= 0 ? 'bg-[#c62828]' : 'bg-[#ffcc00]' }}" style="width: {{ max(3, $pct) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
