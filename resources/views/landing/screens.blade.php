{{-- Landing "Écrans" (2026-11-24): coded replicas of the app screens instead of screenshots — sharp on every screen,
     light, always on-brand. Fictional French demo data (same numbers as the hero mock). Mock text stays French, like the app. --}}
@php
    $menu = [
        ['home', 'Tableau de bord', 0, null],
        ['cube', 'CATALOGUE', null, [['Produits', 4], ['Inventaire', 3]]],
        ['cart', 'VENTES', null, [['Devis', 1], ['Bons de livraison', null], ['Factures', 2]]],
        ['truck', 'ACHATS', null, [['Bons de commande', null]]],
        ['wallet', 'TRÉSORERIE', null, null],
    ];
    $chip = fn ($c) => ['ok' => 'bg-[#dff3e6] text-[#0a6b37] border-[#0a6b37]', 'part' => 'bg-[#fff3c4] text-[#7a5600] border-[#b38600]',
        'late' => 'bg-[#fde3e3] text-[#b42318] border-[#b42318]', 'sent' => 'bg-[#eceae4] text-[#444] border-[#888]'][$c];
@endphp
<section id="ecrans" class="py-20 sm:py-24 scroll-mt-20 bg-[#f4f2ec]" x-data="{ tab: 0, n: {{ count($screens) }}, t: null }" x-init="t = setInterval(() => tab = (tab + 1) % n, 6000)">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-6 border-b-2 border-[#111] pb-6">
            <div>
                <p class="ind-mono text-xs text-[#666]">SECTION 04 · {{ __('Écrans') }}</p>
                <h2 class="mt-2 ind-h text-4xl sm:text-5xl uppercase leading-[0.95]">{{ __('L’application, écran par écran') }}</h2>
            </div>
            <p class="max-w-sm text-[#444]">{{ __('Les écrans de la démo, reproduits avec ses données fictives. Ouvrez la démo pour les essayer.') }}</p>
        </div>
        <div class="mt-8 flex flex-wrap" role="tablist">
            @foreach ($screens as $i => [$key, $label])
                <button type="button" role="tab" @click="tab = {{ $i }}; clearInterval(t)" :aria-selected="tab === {{ $i }}" class="ind-h tracking-wider uppercase px-3 sm:px-4 py-2 text-sm sm:text-base border-2 border-[#111] -me-0.5 -mb-0.5" :class="tab === {{ $i }} ? 'bg-[#111] text-[#ffcc00]' : 'bg-white text-[#111] hover:bg-[#ffcc00]'">{{ __($label) }}</button>
            @endforeach
        </div>

        {{-- app window --}}
        <div class="border-2 border-[#111] bg-white shadow-[10px_10px_0_#111] overflow-hidden text-[#111]" dir="ltr" aria-hidden="true">
            <div class="h-11 bg-[#111] border-b-4 border-[#ffcc00] flex items-center justify-between px-3">
                <div class="flex items-center gap-2"><span class="grid place-items-center w-6 h-6 bg-[#ffcc00] text-[#111]"><x-sp-icon name="cube" class="w-3.5 h-3.5" /></span><span class="ind-h text-white text-lg tracking-wide">STOKITO</span></div>
                <div class="flex items-center gap-2 text-[11px]">
                    <span class="hidden sm:inline-flex items-center gap-2 border border-[#3a3a3a] text-[#bdbdbd] px-2 py-1">Rechercher <span class="ind-mono text-[9px] border border-[#555] px-1">Ctrl K</span></span>
                    <span class="ind-mono text-white border border-[#3a3a3a] px-2 py-1">Compte Démo</span>
                </div>
            </div>
            <div class="flex">
                <aside class="hidden md:block w-48 shrink-0 bg-[#f4f2ec] border-e-2 border-[#111] py-3 text-[12.5px]">
                    @foreach ($menu as [$ic, $name, $tab, $children])
                        <div class="flex items-center gap-2 px-4 py-1.5 {{ $children !== null || $tab === null ? 'ind-h tracking-wide text-[13px]' : '' }}" @if ($tab !== null) :class="tab === {{ $tab }} && 'bg-[#ffcc00] font-semibold'" @endif>
                            <x-sp-icon :name="$ic" class="w-4 h-4 shrink-0" /> <span>{{ $name }}</span>
                        </div>
                        @foreach ($children ?? [] as [$c, $ct])
                            <div class="ms-6 me-2 px-3 py-1 text-[12px] text-[#444] border-2 border-transparent" @if ($ct !== null) :class="tab === {{ $ct }} && 'bg-[#ffcc00] !border-[#111] !text-[#111] font-semibold'" @endif>{{ $c }}</div>
                        @endforeach
                    @endforeach
                </aside>

                <div class="flex-1 min-w-0 p-3 sm:p-5 bg-[#faf9f6] min-h-[440px] text-[13px] grid">

                    {{-- 0 · TABLEAU DE BORD --}}
                    <div class="col-start-1 row-start-1 min-w-0" :style="{ visibility: tab === 0 ? 'visible' : 'hidden' }">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="ind-h text-2xl uppercase">Tableau de bord</div>
                            <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                                @foreach (['Devis', 'Facture', 'Bon de livraison', 'Bon de commande'] as $b)
                                    <span class="border-2 border-[#111] bg-white px-2 py-1 {{ $loop->index > 1 ? 'hidden sm:inline' : '' }}">+ {{ $b }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
                            @foreach ([['CA DU MOIS', '18 420,60 €', '+12 % vs octobre', ''], ['ENCAISSÉ', '12 905,00 €', '70 % du CA', ''], ['IMPAYÉS', '5 515,60 €', '3 factures', 'text-[#b42318]'], ['MARGE', '4 236,70 €', '23 % du CA', 'text-[#0a6b37]']] as [$k, $v, $s, $cl])
                                <div class="border-2 border-[#111] bg-white p-2.5 sm:p-3">
                                    <div class="ind-mono text-[10px] text-[#777]">{{ $k }}</div>
                                    <div class="mt-1 ind-h text-xl sm:text-2xl leading-none {{ $cl }}">{{ $v }}</div>
                                    <div class="mt-1 text-[11px] text-[#666]">{{ $s }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 grid lg:grid-cols-5 gap-3">
                            <div class="lg:col-span-3 border-2 border-[#111] bg-white p-3">
                                <div class="flex justify-between"><span class="font-semibold">Ventes des 6 derniers mois</span><span class="ind-mono text-[10px] text-[#777]">€ HT</span></div>
                                <div class="mt-3 h-32 flex items-end gap-2 sm:gap-3 border-b-2 border-[#111]">
                                    @foreach ([['JUIN', 46], ['JUIL.', 62], ['AOÛT', 41], ['SEPT.', 74], ['OCT.', 88], ['NOV.', 96]] as [$m, $h])
                                        <div class="flex-1 {{ $loop->last ? 'bg-[#ffcc00] border-2 border-b-0 border-[#111]' : 'bg-[#111]' }}" style="height: {{ $h }}%"></div>
                                    @endforeach
                                </div>
                                <div class="mt-1 flex gap-2 sm:gap-3 ind-mono text-[9px] text-[#777]">@foreach (['JUIN', 'JUIL.', 'AOÛT', 'SEPT.', 'OCT.', 'NOV.'] as $m)<span class="flex-1 text-center">{{ $m }}</span>@endforeach</div>
                            </div>
                            <div class="lg:col-span-2 border-2 border-[#111] bg-white">
                                <div class="px-3 py-2 border-b-2 border-[#111] flex justify-between"><span class="font-semibold">Stock bas</span><span class="ind-mono text-[10px] text-[#b42318]">3 ALERTES</span></div>
                                @foreach ([['VANNE-1/2', 'Vanne laiton 1/2"', '0 / 10', 'bg-[#b42318]', 3], ['TUB-CAR-60', 'Tube carré 60×60', '12 / 20', 'bg-[#ffcc00]', 60], ['DISQ-115', 'Disque à tronçonner 115', '18 / 25', 'bg-[#ffcc00]', 72]] as [$r, $n, $q, $c, $w])
                                    <div class="px-3 py-2 {{ $loop->last ? '' : 'border-b border-[#e3e0d8]' }}">
                                        <div class="flex justify-between gap-2"><span class="truncate"><span class="ind-mono text-[10px] text-[#888] me-1.5">{{ $r }}</span>{{ $n }}</span><b class="ind-mono text-[12px] shrink-0">{{ $q }}</b></div>
                                        <div class="mt-1 h-1.5 bg-[#eceae4]"><div class="h-full {{ $c }}" style="width: {{ $w }}%"></div></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- 1 · DEVIS --}}
                    <div class="col-start-1 row-start-1 min-w-0" style="visibility:hidden" :style="{ visibility: tab === 1 ? 'visible' : 'hidden' }">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div><span class="ind-mono text-[10px] text-[#777]">DEVIS</span><div class="ind-h text-2xl uppercase leading-none">DEV-00042 <span class="align-middle ms-1 text-[11px] font-sans normal-case border px-1.5 py-0.5 {{ $chip('ok') }}">Accepté</span></div></div>
                            <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                                <span class="border-2 border-[#111] bg-white px-2 py-1">PDF</span>
                                <span class="hidden sm:inline border-2 border-[#111] bg-white px-2 py-1">Envoyer</span>
                                <span class="border-2 border-[#0a6b37] text-[#0a6b37] bg-white px-2 py-1">WhatsApp</span>
                                <span class="border-2 border-[#111] bg-[#ffcc00] px-2 py-1">Convertir en BL</span>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                            @foreach ([['Client', 'Bâti Plus SARL', 'col-span-2 sm:col-span-1'], ['Date', '18/11/2026', ''], ['Valable jusqu’au', '18/12/2026', '']] as [$l, $v, $cl])
                                <div class="{{ $cl }}"><div class="text-[11px] text-[#666]">{{ $l }}</div><div class="mt-0.5 border border-[#bbb] bg-white px-2 py-1.5">{{ $v }}</div></div>
                            @endforeach
                        </div>
                        <div class="mt-4 border-2 border-[#111] bg-white">
                            <div class="grid grid-cols-[1fr_44px_64px_76px] sm:grid-cols-[1fr_56px_80px_56px_90px] gap-2 bg-[#111] text-[#ffcc00] ind-mono text-[10px] px-3 py-2">
                                <span>PRODUIT</span><span class="text-right">QTÉ</span><span class="text-right">P.U. HT</span><span class="hidden sm:block text-right">TVA</span><span class="text-right">TOTAL HT</span>
                            </div>
                            @foreach ([['Fer rond à béton Ø10 (barre 6 m)', '50', '7,20', '360,00'], ['Tube carré 40×40×2 (6 m)', '12', '18,50', '222,00'], ['Peinture antirouille 4 L', '6', '32,90', '197,40']] as [$n, $q, $pu, $t])
                                <div class="grid grid-cols-[1fr_44px_64px_76px] sm:grid-cols-[1fr_56px_80px_56px_90px] gap-2 px-3 py-2.5 border-b border-[#e3e0d8] items-center">
                                    <span class="leading-snug sm:truncate">{{ $n }}</span><span class="text-right ind-mono">{{ $q }}</span><span class="text-right ind-mono">{{ $pu }}</span><span class="hidden sm:block text-right ind-mono">20 %</span><span class="text-right ind-mono font-semibold">{{ $t }}</span>
                                </div>
                            @endforeach
                            <div class="flex justify-end px-3 py-3">
                                <div class="w-52 space-y-1 ind-mono text-[12px]">
                                    <div class="flex justify-between"><span class="text-[#666]">Total HT</span><span>779,40 €</span></div>
                                    <div class="flex justify-between"><span class="text-[#666]">TVA 20 %</span><span>155,88 €</span></div>
                                    <div class="flex justify-between border-t-2 border-[#111] pt-1 font-bold text-[13px]"><span>Total TTC</span><span class="bg-[#ffcc00] px-1">935,28 €</span></div>
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-[11px] text-[#666]">Accepté par le client : « Convertir en BL » reprend toutes les lignes, puis « Facturer ».</p>
                    </div>

                    {{-- 2 · FACTURES --}}
                    <div class="col-start-1 row-start-1 min-w-0" style="visibility:hidden" :style="{ visibility: tab === 2 ? 'visible' : 'hidden' }">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="ind-h text-2xl uppercase">Factures</div>
                            <span class="border-2 border-[#111] bg-[#ffcc00] px-2 py-1 text-[11px] font-semibold">+ Nouvelle facture</span>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-1.5 text-[11px]">
                            @foreach (['Toutes' => true, 'Payées' => false, 'Partielles' => false, 'En retard' => false] as $f => $on)
                                <span class="border-2 border-[#111] px-2 py-0.5 {{ $on ? 'bg-[#111] text-[#ffcc00]' : 'bg-white' }}">{{ $f }}</span>
                            @endforeach
                        </div>
                        <div class="mt-3 border-2 border-[#111] bg-white">
                            <div class="grid grid-cols-[1fr_80px_86px] sm:grid-cols-[96px_1fr_90px_90px_90px_96px] gap-2 bg-[#111] text-[#ffcc00] ind-mono text-[10px] px-3 py-2">
                                <span class="hidden sm:block">NUMÉRO</span><span>CLIENT</span><span class="hidden sm:block">ÉCHÉANCE</span><span class="text-right">TTC</span><span class="hidden sm:block text-right">RESTE</span><span class="text-right">STATUT</span>
                            </div>
                            @foreach ([
                                ['FAC-00061', 'Bâti Plus SARL', '18/12/2026', '935,28', '0,00', 'ok', 'Payée', null],
                                ['FAC-00060', 'Menuiserie Laurent', '10/12/2026', '2 412,00', '1 000,00', 'part', 'Partielle', 'Encaisser'],
                                ['FAC-00058', 'Garage du Parc', '05/11/2026', '1 380,00', '1 380,00', 'late', 'En retard', 'Relancer'],
                                ['FAC-00057', 'SCI Les Tilleuls', '30/12/2026', '648,00', '648,00', 'sent', 'Envoyée', 'WhatsApp'],
                            ] as [$num, $cl, $due, $ttc, $rest, $st, $stl, $act])
                                <div class="grid grid-cols-[1fr_80px_86px] sm:grid-cols-[96px_1fr_90px_90px_90px_96px] gap-2 px-3 py-2.5 border-b border-[#e3e0d8] items-center">
                                    <span class="hidden sm:block ind-mono text-[12px]">{{ $num }}</span>
                                    <span class="min-w-0"><span class="block truncate">{{ $cl }}</span><span class="sm:hidden ind-mono text-[10px] text-[#888]">{{ $num }}</span>
                                        @if ($act)<span class="block text-[11px] font-semibold {{ $act === 'Encaisser' ? 'text-[#111] underline decoration-[#ffcc00] decoration-2' : 'text-[#0a6b37]' }}">{{ $act }}</span>@endif</span>
                                    <span class="hidden sm:block ind-mono text-[12px] {{ $st === 'late' ? 'text-[#b42318] font-semibold' : '' }}">{{ $due }}</span>
                                    <span class="text-right ind-mono text-[12px]">{{ $ttc }}</span>
                                    <span class="hidden sm:block text-right ind-mono text-[12px]">{{ $rest }}</span>
                                    <span class="text-right"><span class="inline-block border text-[10.5px] px-1.5 py-0.5 {{ $chip($st) }}">{{ $stl }}</span></span>
                                </div>
                            @endforeach
                            <div class="px-3 py-2 flex justify-between text-[11px] text-[#666]"><span>4 factures</span><span class="ind-mono">Reste à encaisser : <b class="text-[#111]">3 028,00 €</b></span></div>
                        </div>
                    </div>

                    {{-- 3 · INVENTAIRE --}}
                    <div class="col-start-1 row-start-1 min-w-0" style="visibility:hidden" :style="{ visibility: tab === 3 ? 'visible' : 'hidden' }">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div><span class="ind-mono text-[10px] text-[#777]">INVENTAIRE · INV-00007</span><div class="ind-h text-2xl uppercase leading-none">Dépôt principal <span class="align-middle ms-1 text-[11px] font-sans normal-case border px-1.5 py-0.5 {{ $chip('part') }}">En cours</span></div></div>
                            <span class="border-2 border-[#111] bg-[#ffcc00] px-2 py-1 text-[11px] font-semibold">Valider l’inventaire</span>
                        </div>
                        <div class="mt-3 border-2 border-[#111] bg-white p-3">
                            <div class="flex justify-between text-[12px]"><span>142 / 180 produits comptés</span><b class="ind-mono">79 %</b></div>
                            <div class="mt-1.5 h-2.5 border border-[#111]"><div class="h-full bg-[#111]" style="width: 79%"></div></div>
                        </div>
                        <div class="mt-3 border-2 border-[#111] bg-white">
                            <div class="grid grid-cols-[1fr_52px_60px_52px] sm:grid-cols-[96px_1fr_80px_90px_70px] gap-2 bg-[#111] text-[#ffcc00] ind-mono text-[10px] px-3 py-2">
                                <span class="hidden sm:block">RÉF.</span><span>PRODUIT</span><span class="text-right">THÉO.</span><span class="text-right">COMPTÉ</span><span class="text-right">ÉCART</span>
                            </div>
                            @foreach ([['DISQ-115', 'Disque à tronçonner 115 mm', 500, 480, -20], ['COR-40', 'Cornière 40×40 (6 m)', 140, 140, 0], ['FER-RD-10', 'Fer rond à béton Ø10', 260, 262, 2], ['VIS-440', 'Vis inox 4x40 (boîte 100)', 35, 35, 0], ['GAN-T9', 'Gants de manutention T9', 64, 58, -6]] as [$r, $n, $th, $co, $e])
                                <div class="grid grid-cols-[1fr_52px_60px_52px] sm:grid-cols-[96px_1fr_80px_90px_70px] gap-2 px-3 py-2 border-b border-[#e3e0d8] items-center">
                                    <span class="hidden sm:block ind-mono text-[11px] text-[#666]">{{ $r }}</span>
                                    <span class="leading-snug sm:truncate">{{ $n }}</span>
                                    <span class="text-right ind-mono text-[12px] text-[#666]">{{ $th }}</span>
                                    <span class="text-right"><span class="inline-block w-full sm:w-16 border border-[#bbb] bg-white px-1.5 py-0.5 ind-mono text-[12px] text-right">{{ $co }}</span></span>
                                    <span class="text-right ind-mono text-[12px] font-bold {{ $e < 0 ? 'text-[#b42318]' : ($e > 0 ? 'text-[#0a6b37]' : 'text-[#0a6b37]') }}">{{ $e === 0 ? 'OK' : ($e > 0 ? '+'.$e : '−'.abs($e)) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-[11px] text-[#666]">À la validation, le stock de chaque produit est corrigé de son écart.</p>
                    </div>

                    {{-- 4 · PRODUITS --}}
                    <div class="col-start-1 row-start-1 min-w-0" style="visibility:hidden" :style="{ visibility: tab === 4 ? 'visible' : 'hidden' }">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="ind-h text-2xl uppercase">Produits</div>
                            <div class="flex gap-1.5 text-[11px] font-semibold"><span class="hidden sm:inline border-2 border-[#111] bg-white px-2 py-1">Importer Excel</span><span class="border-2 border-[#111] bg-[#ffcc00] px-2 py-1">+ Nouveau produit</span></div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2 items-center">
                            <span class="flex-1 min-w-[160px] border border-[#bbb] bg-white px-2 py-1.5 text-[#999]">Rechercher référence, désignation, marque…</span>
                            <span class="border-2 border-[#111] bg-white px-2 py-1 text-[11px]">Stock bas (3)</span>
                        </div>
                        <div class="mt-3 border-2 border-[#111] bg-white">
                            <div class="grid grid-cols-[1fr_64px_70px] sm:grid-cols-[100px_1fr_110px_70px_80px_80px] gap-2 bg-[#111] text-[#ffcc00] ind-mono text-[10px] px-3 py-2">
                                <span class="hidden sm:block">RÉF.</span><span>DÉSIGNATION</span><span class="hidden sm:block">CATÉGORIE</span><span class="text-right">STOCK</span><span class="hidden sm:block text-right">ACHAT HT</span><span class="text-right">VENTE TTC</span>
                            </div>
                            @foreach ([
                                ['FER-RD-10', 'Fer rond à béton Ø10', 'Acier', '262', '5,90', '8,64', null],
                                ['TUB-CAR-60', 'Tube carré 60×60 (6 m)', 'Acier', '12', '24,50', '35,90', 'low'],
                                ['VANNE-1/2', 'Vanne laiton 1/2"', 'Plomberie', '0', '6,80', '12,90', 'out'],
                                ['PEINT-AR4', 'Peinture antirouille 4 L', 'Peinture', '38', '22,10', '39,48', null],
                                ['VIS-440', 'Vis inox 4x40 (boîte 100)', 'Quincaillerie', '35', '6,20', '11,90', null],
                            ] as [$r, $n, $cat, $s, $a, $v, $flag])
                                <div class="grid grid-cols-[1fr_64px_70px] sm:grid-cols-[100px_1fr_110px_70px_80px_80px] gap-2 px-3 py-2.5 border-b border-[#e3e0d8] items-center">
                                    <span class="hidden sm:block ind-mono text-[11px] text-[#666]">{{ $r }}</span>
                                    <span class="min-w-0"><span class="block truncate">{{ $n }}</span><span class="sm:hidden ind-mono text-[10px] text-[#888]">{{ $r }}</span></span>
                                    <span class="hidden sm:block text-[12px] text-[#555]">{{ $cat }}</span>
                                    <span class="text-right"><span class="inline-block ind-mono text-[12px] font-semibold px-1.5 {{ $flag === 'out' ? 'bg-[#b42318] text-white' : ($flag === 'low' ? 'bg-[#ffcc00]' : '') }}">{{ $s }}</span></span>
                                    <span class="hidden sm:block text-right ind-mono text-[12px]">{{ $a }}</span>
                                    <span class="text-right ind-mono text-[12px]">{{ $v }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="mt-10"><a href="{{ route('demo.login') }}" class="ind-btn bg-[#ffcc00] text-[#111]">{{ __('Ouvrir la démo') }} <span aria-hidden="true">{{ $rtl ? '←' : '→' }}</span></a></div>
    </div>
</section>
