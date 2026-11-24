<x-app-layout>
    @php
        $u = auth()->user();
        $max = max(1, $months->max('total'));
        $money = fn ($v) => \App\Support\Fmt::money($v);
        $card = 'bg-white border-2 border-[#111]';
    @endphp

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl">

        {{-- greeting + quick actions --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="ind-mono text-xs text-[#666] uppercase">{{ now()->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY') }}</p>
                <h1 class="ind-h text-4xl uppercase leading-none mt-1">{{ __('Bonjour') }}, {{ $u->first_name ?: $u->name }}</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($u->hasPermission('devis_creer'))
                    <a href="{{ route('devis.create') }}" wire:navigate class="inline-flex items-center gap-1.5 bg-[#111] text-[#ffcc00] border-2 border-[#111] px-3.5 py-2 ind-h text-lg uppercase tracking-wider shadow-[3px_3px_0_#ffcc00]"><x-sp-icon name="plus" class="w-4 h-4" /> {{ __('Devis') }}</a>
                @endif
                @if ($u->hasPermission('factures_consulter'))
                    <a href="{{ route('factures.create') }}" wire:navigate class="inline-flex items-center gap-1.5 bg-[#ffcc00] text-[#111] border-2 border-[#111] px-3.5 py-2 ind-h text-lg uppercase tracking-wider hover:bg-white"><x-sp-icon name="plus" class="w-4 h-4" /> {{ __('Facture') }}</a>
                @endif
                @if ($u->hasPermission('bl_creer'))
                    <a href="{{ route('bon-livraisons.create') }}" wire:navigate class="inline-flex items-center gap-1.5 bg-white border-2 border-[#111] px-3.5 py-2 ind-h text-lg uppercase tracking-wider hover:bg-[#ffcc00]"><x-sp-icon name="plus" class="w-4 h-4" /> {{ __('Bon de livraison') }}</a>
                @endif
            </div>
        </div>

        {{-- getting started (new self-service accounts, until the first product exists) --}}
        @if (! $u->is_demo && \App\Models\Product::count() === 0)
            <div class="bg-white border-2 border-[#111] shadow-[6px_6px_0_#111]">
                <div class="ind-hazard h-2"></div>
                <div class="p-5">
                    <p class="ind-mono text-[11px] uppercase text-[#666]">{{ __('Démarrage') }} · 3 {{ __('étapes') }}</p>
                    <h2 class="ind-h text-3xl uppercase leading-none mt-1">{{ __('Bienvenue ! Votre espace est prêt.') }}</h2>
                    <div class="mt-4 grid md:grid-cols-3 gap-3">
                        @foreach ([[route('societe.edit'), '01', __('Complétez Ma Société'), __('Logo, adresse, identifiants : ils apparaissent sur vos PDF.')],
                                   [route('products.index'), '02', __('Ajoutez vos produits'), __('Un par un, ou importez votre fichier Excel en une fois.')],
                                   [route('devis.create'), '03', __('Faites votre premier devis'), __('Choisissez un client, ajoutez les lignes, imprimez le PDF.')]] as [$href, $n, $t, $d])
                            <a href="{{ $href }}" wire:navigate class="block border-2 border-[#111] p-4 hover:bg-[#ffcc00]">
                                <div class="ind-h text-4xl leading-none text-[#ffcc00] [-webkit-text-stroke:1.5px_#111]">{{ $n }}</div>
                                <div class="mt-2 ind-h text-xl uppercase">{{ $t }}</div>
                                <div class="text-sm text-[#444]">{{ $d }}</div>
                            </a>
                        @endforeach
                    </div>
                    <p class="mt-4 text-sm text-[#444]">{{ __('Besoin d’aide pour démarrer ?') }} <a href="https://wa.me/212676035777" target="_blank" rel="noopener" class="font-semibold underline decoration-[#ffcc00] decoration-2">{{ __('Écrivez-nous sur WhatsApp') }}</a>.</p>
                </div>
            </div>
        @endif

        {{-- user guide --}}
        <div class="flex flex-wrap items-center gap-3 border-2 border-[#111] bg-[#ffcc00] px-4 py-3">
            <span class="grid place-items-center w-9 h-9 bg-[#111] text-[#ffcc00] shrink-0"><x-sp-icon name="doc" class="w-5 h-5" /></span>
            <div class="flex-1 min-w-[200px]">
                <div class="text-sm font-semibold text-slate-900">{{ __("Guide d'utilisation") }}</div>
                <div class="text-xs text-slate-500">{{ __('Tout Stokito expliqué pas à pas, à garder ou à partager avec votre équipe.') }}</div>
            </div>
            <a href="/docs/guide-stokito-fr.pdf" target="_blank" rel="noopener" class="inline-flex items-center bg-white border-2 border-[#111] px-3 py-1.5 text-sm font-bold hover:bg-[#111] hover:text-[#ffcc00]">Français (PDF)</a>
            <a href="/docs/guide-stokito-ar.pdf" target="_blank" rel="noopener" class="inline-flex items-center bg-white border-2 border-[#111] px-3 py-1.5 text-sm font-bold hover:bg-[#111] hover:text-[#ffcc00]" dir="rtl">العربية (PDF)</a>
            <a href="/docs/guide-stokito-en.pdf" target="_blank" rel="noopener" class="inline-flex items-center bg-white border-2 border-[#111] px-3 py-1.5 text-sm font-bold hover:bg-[#111] hover:text-[#ffcc00]">English (PDF)</a>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="{{ $card }} p-4 sm:p-5">
                <div class="flex items-center gap-2 ind-mono text-[11px] uppercase text-[#666]"><x-sp-icon name="chart" class="w-4 h-4 text-[#111]" /> {{ __('CA du mois') }}</div>
                <div class="mt-2 ind-h text-3xl sm:text-4xl leading-none text-slate-900">{{ $money($caMois) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
                @if ($caEvolution !== null)
                    <div class="mt-1 text-xs font-semibold {{ $caEvolution >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $caEvolution >= 0 ? '▲' : '▼' }} {{ abs($caEvolution) }}% {{ __('vs mois dernier') }}</div>
                @else
                    <div class="mt-1 text-xs text-slate-400">{{ __('Factures TTC du mois') }}</div>
                @endif
            </div>
            <div class="{{ $card }} p-4 sm:p-5">
                <div class="flex items-center gap-2 ind-mono text-[11px] uppercase text-[#666]"><x-sp-icon name="coins" class="w-4 h-4 text-[#111]" /> {{ __('Encaissé ce mois') }}</div>
                <div class="mt-2 ind-h text-3xl sm:text-4xl leading-none text-slate-900">{{ $money($encaisseMois) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
                <div class="mt-1 text-xs text-slate-400">{{ __('Règlements clients') }}</div>
            </div>
            <a href="{{ route('factures.index') }}" wire:navigate class="{{ $card }} p-4 sm:p-5 hover:bg-[#ffcc00]">
                <div class="flex items-center gap-2 ind-mono text-[11px] uppercase text-[#666]"><x-sp-icon name="clock" class="w-4 h-4 text-[#111]" /> {{ __('Impayés') }}</div>
                <div class="mt-2 ind-h text-3xl sm:text-4xl leading-none {{ $totalImpayes > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $money($totalImpayes) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
                <div class="mt-1 text-xs text-slate-400">{{ trans_choice('{0} Aucune facture|{1} :count facture|[2,*] :count factures', $impayes->count(), ['count' => $impayes->count()]) }}</div>
            </a>
            <a href="{{ route('products.index') }}" wire:navigate class="{{ $card }} p-4 sm:p-5 hover:bg-[#ffcc00]">
                <div class="flex items-center gap-2 ind-mono text-[11px] uppercase text-[#666]"><x-sp-icon name="alert" class="w-4 h-4 text-[#111]" /> {{ __('Stock bas') }}</div>
                <div class="mt-2 ind-h text-3xl sm:text-4xl leading-none {{ $lowStock->count() ? 'text-rose-600' : 'text-slate-900' }}">{{ $lowStock->count() }} <span class="text-sm font-medium text-slate-400">{{ __('produits') }}</span></div>
                <div class="mt-1 text-xs text-slate-400">{{ $devisEnCours }} {{ __('devis en cours') }}</div>
            </a>
        </div>

        {{-- KPIs, second row (2026-11-24) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @if ($u->hasPermission('marges'))
            <div class="{{ $card }} p-4 sm:p-5">
                <div class="flex items-center gap-2 ind-mono text-[11px] uppercase text-[#666]"><x-sp-icon name="chart" class="w-4 h-4 text-[#111]" /> {{ __('Marge brute du mois') }}</div>
                <div class="mt-2 ind-h text-3xl sm:text-4xl leading-none {{ $margeMois < 0 ? 'text-rose-600' : 'text-emerald-700' }}">{{ $money($margeMois) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
                <div class="mt-1 text-xs text-slate-400">{{ $margePct !== null ? $margePct.' % '.__('du CA HT') : __('Aucune facture ce mois') }}</div>
            </div>
            @endif
            @if ($u->hasPermission('produits_prix_achat'))
            <a href="{{ route('products.index') }}" wire:navigate class="{{ $card }} p-4 sm:p-5 hover:bg-[#ffcc00]">
                <div class="flex items-center gap-2 ind-mono text-[11px] uppercase text-[#666]"><x-sp-icon name="cube" class="w-4 h-4 text-[#111]" /> {{ __('Valeur du stock') }}</div>
                <div class="mt-2 ind-h text-3xl sm:text-4xl leading-none text-slate-900">{{ $money($valeurStock) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
                <div class="mt-1 text-xs {{ $dormants ? 'text-amber-700 font-semibold' : 'text-slate-400' }}">{{ $dormants ? $dormants.' '.__('produits sans vente depuis 90 j') : __('Au prix d’achat HT') }}</div>
            </a>
            @endif
            <a href="{{ route('devis.index') }}" wire:navigate class="{{ $card }} p-4 sm:p-5 hover:bg-[#ffcc00]">
                <div class="flex items-center gap-2 ind-mono text-[11px] uppercase text-[#666]"><x-sp-icon name="doc" class="w-4 h-4 text-[#111]" /> {{ __('Devis en attente') }}</div>
                <div class="mt-2 ind-h text-3xl sm:text-4xl leading-none text-slate-900">{{ $money($devisAttenteMontant) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
                <div class="mt-1 text-xs text-slate-400">{{ $devisAttenteCount }} {{ __('devis') }}@if ($tauxAcceptation !== null) · {{ $tauxAcceptation }} % {{ __('acceptés (90 j)') }}@endif</div>
            </a>
            <div class="{{ $card }} p-4 sm:p-5">
                <div class="flex items-center gap-2 ind-mono text-[11px] uppercase text-[#666]"><x-sp-icon name="coins" class="w-4 h-4 text-[#111]" /> {{ __('TVA facturée ce mois') }}</div>
                <div class="mt-2 ind-h text-3xl sm:text-4xl leading-none text-slate-900">{{ $money($tvaMois) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
                <div class="mt-1 text-xs text-slate-400">{{ __('Sur les factures du mois') }}</div>
            </div>
        </div>

        <div class="grid lg:grid-cols-5 gap-4 sm:gap-6">
            {{-- 6-month sales chart --}}
            <div class="{{ $card }} p-5 lg:col-span-3">
                <div class="flex items-center justify-between">
                    <h2 class="ind-h text-xl uppercase tracking-wide">{{ __('Ventes des 6 derniers mois') }}</h2>
                    <span class="text-xs text-slate-400">{{ __('Factures TTC') }}, {{ $currency }}</span>
                </div>
                <div class="mt-6 flex items-end gap-2 sm:gap-4 h-48">
                    @foreach ($months as $m)
                        <div class="flex-1 flex flex-col items-center justify-end h-full group">
                            <div class="text-[11px] font-semibold text-slate-500 mb-1 opacity-0 group-hover:opacity-100 sm:opacity-100 whitespace-nowrap">{{ $m['total'] > 0 ? number_format($m['total'] / 1000, 1, ',', ' ').'k' : '' }}</div>
                            <div class="w-full max-w-[56px] {{ $loop->last ? 'bg-[#ffcc00] border-2 border-b-0 border-[#111]' : 'bg-[#111] group-hover:bg-[#444]' }}"
                                style="height: {{ $m['total'] > 0 ? max(4, round($m['total'] / $max * 100)) : 2 }}%" title="{{ $money($m['total']) }} {{ $currency }}"></div>
                            <div class="mt-2 text-xs {{ $loop->last ? 'font-semibold text-slate-800' : 'text-slate-500' }}">{{ $m['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- unpaid invoices --}}
            <div class="{{ $card }} lg:col-span-2 overflow-hidden">
                <div class="px-5 py-4 border-b-2 border-[#111] flex items-center justify-between">
                    <div>
                        <h2 class="ind-h text-xl uppercase tracking-wide">{{ __('À encaisser') }}</h2>
                        @if ($aEncaisser30 > 0)<div class="text-xs text-slate-500">{{ __('Sous 30 jours') }} : <strong class="text-slate-900">{{ $money($aEncaisser30) }} {{ $currency }}</strong></div>@endif
                    </div>
                    <a href="{{ route('factures.index') }}" wire:navigate class="ind-mono text-[11px] uppercase underline decoration-[#ffcc00] decoration-2 underline-offset-4">{{ __('Toutes les factures') }}</a>
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
                    <div class="px-5 py-4 border-b-2 border-[#111] flex items-center justify-between">
                        <h2 class="ind-h text-xl uppercase tracking-wide">{{ $titre }}</h2>
                        <span class="ind-mono text-[11px] uppercase text-[#666]">{{ __('90 derniers jours') }} · {{ $unite }}</span>
                    </div>
                    @forelse ($rows as $i => $r)
                        <div class="px-5 py-3 {{ $loop->last ? '' : 'border-b border-slate-100' }}">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm text-slate-800 truncate"><span class="ind-mono text-[11px] text-[#666] me-2">{{ sprintf('%02d', $i + 1) }}</span>{{ $r['name'] }}</span>
                                <span class="text-sm font-semibold text-slate-900 shrink-0">{{ $money($r['ca']) }}</span>
                            </div>
                            <div class="mt-2 h-2 border border-[#111] bg-white overflow-hidden"><div class="h-full {{ $i === 0 ? 'bg-[#ffcc00]' : 'bg-[#111]' }}" style="width: {{ max(3, round($r['ca'] / $topMax * 100)) }}%"></div></div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate-400">{{ __('Aucune vente sur la période') }}</div>
                    @endforelse
                </div>
            @endforeach
        </div>

        {{-- low stock --}}
        <div class="{{ $card }} overflow-hidden">
            <div class="px-5 py-4 border-b-2 border-[#111] flex items-center justify-between">
                <h2 class="ind-h text-xl uppercase tracking-wide">{{ __('Produits en alerte de stock') }}</h2>
                <a href="{{ route('products.index') }}" wire:navigate class="ind-mono text-[11px] uppercase underline decoration-[#ffcc00] decoration-2 underline-offset-4">{{ __('Voir les produits') }}</a>
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
                            <div class="mt-2 h-2.5 border border-[#111] bg-white overflow-hidden"><div class="h-full {{ $product->stock_qty <= 0 ? 'bg-[#c62828]' : 'bg-[#ffcc00]' }}" style="width: {{ max(3, $pct) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
