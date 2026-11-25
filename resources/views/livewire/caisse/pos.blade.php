@php
    $t = $this->totals;
    $money = fn ($v) => \App\Support\Fmt::money($v);
    $paid = collect($pay)->sum(fn ($v) => (float) $v);
    $diff = round($paid - $t['total_ttc'], 2);
    $input = 'w-full rounded-none border-2 border-[#111] bg-white px-2 py-1.5 text-sm focus:ring-[#ffcc00] focus:border-[#111]';
@endphp
<div class="p-3 sm:p-5 lg:p-6 max-w-7xl pb-28 lg:pb-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="ind-h text-3xl sm:text-4xl uppercase leading-none">Caisse</h1>
        <div class="flex gap-2 text-sm font-semibold">
            <a href="{{ route('caisse.tickets') }}" wire:navigate class="border-2 border-[#111] bg-white px-3 py-1.5 hover:bg-[#ffcc00]">Tickets</a>
            @if (auth()->user()->hasPermission('caisse_cloturer'))
                <a href="{{ route('caisse.clotures') }}" wire:navigate class="border-2 border-[#111] bg-white px-3 py-1.5 hover:bg-[#ffcc00]">Clôtures (Z)</a>
            @endif
        </div>
    </div>

    @if ($last)
        <div class="mb-4 border-2 border-[#111] bg-[#dff3e6] p-4 flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1">
                <div class="ind-h text-xl uppercase">Ticket {{ $last->numero }} enregistré</div>
                <div class="text-sm">Total {{ $money($last->total_ttc) }} {{ $currency }}@if ((float) $last->rendu > 0) · <b>Rendu : {{ $money($last->rendu) }} {{ $currency }}</b>@endif</div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('caisse.tickets.print', $last->id) }}" target="_blank" class="ind-h uppercase tracking-wider border-2 border-[#111] bg-[#111] text-[#ffcc00] px-4 py-2">Imprimer le ticket</a>
                <button type="button" wire:click="$set('lastTicketId', null)" class="ind-h uppercase tracking-wider border-2 border-[#111] bg-white px-4 py-2">Nouvelle vente</button>
            </div>
        </div>
    @endif

    <div class="grid lg:grid-cols-[1fr_420px] gap-4 items-start">
        {{-- products --}}
        <div>
            <div class="flex gap-2">
                <input type="text" wire:model.live.debounce.250ms="search" wire:keydown.enter.prevent="addByCode" autofocus autocomplete="off"
                    placeholder="Rechercher ou scanner : désignation, référence, code-barres…" class="{{ $input }} !py-2.5 !text-base">
                <x-barcode-scanner on-scan="addByCode" label="Scanner" />
            </div>
            <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2">
                @forelse ($products as $p)
                    <button type="button" wire:click="addProduct({{ $p->id }})" wire:key="p{{ $p->id }}"
                        class="text-start border-2 border-[#111] bg-white p-2.5 hover:bg-[#fff3c4] active:bg-[#ffcc00] min-h-[84px] flex flex-col">
                        @if ($p->image_path)
                            <img src="{{ $p->imageUrl() }}" alt="" loading="lazy" class="-mx-2.5 -mt-2.5 mb-2 w-[calc(100%+1.25rem)] aspect-[4/3] object-cover border-b-2 border-[#111]">
                        @endif
                        <span class="text-sm font-semibold leading-snug line-clamp-2">{{ $p->name }}</span>
                        <span class="ind-mono text-[10px] text-[#777] mt-0.5">{{ $p->reference }}</span>
                        <span class="mt-auto pt-1 flex items-end justify-between gap-1">
                            <b class="ind-mono text-sm">{{ $money($p->sale_price) }}</b>
                            <span class="ind-mono text-[10px] px-1 {{ (float) $p->stock_qty <= 0 ? 'bg-[#b42318] text-white' : 'text-[#666]' }}">{{ \App\Support\Fmt::qty($p->stock_qty) }}</span>
                        </span>
                    </button>
                @empty
                    <p class="col-span-full text-sm text-[#666] py-6 text-center">Aucun produit pour « {{ $search }} ».</p>
                @endforelse
            </div>
        </div>

        {{-- ticket --}}
        <div id="ticket" class="scroll-mt-20 lg:sticky lg:top-4 border-2 border-[#111] bg-white shadow-[6px_6px_0_#111]">
            <div class="bg-[#111] text-[#ffcc00] px-4 py-2 flex items-center justify-between">
                <span class="ind-h text-lg uppercase tracking-wider">Ticket en cours</span>
                <span class="ind-mono text-[11px] text-[#bbb]">{{ count($cart) }} ligne(s)</span>
            </div>
            <div class="p-3 border-b-2 border-[#111]">
                <select wire:model="client_id" class="{{ $input }}">
                    <option value="">Client comptoir</option>
                    @foreach ($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="max-h-[46vh] overflow-y-auto divide-y divide-[#e3e0d8]">
                @forelse ($cart as $i => $l)
                    <div class="p-3" wire:key="l{{ $i }}-{{ $l['product_id'] ?? 'x' }}">
                        <div class="flex justify-between gap-2">
                            <div class="min-w-0"><div class="font-semibold text-sm leading-snug">{{ $l['designation'] }}</div><div class="ind-mono text-[10px] text-[#888]">{{ $l['reference'] }} · TVA {{ rtrim(rtrim(number_format($l['tva_rate'], 2, ',', ''), '0'), ',') }} %</div></div>
                            <button type="button" wire:click="remove({{ $i }})" class="shrink-0 w-7 h-7 border-2 border-[#111] text-[#b42318] font-bold leading-none" aria-label="Retirer">×</button>
                        </div>
                        <div class="mt-2 grid grid-cols-[auto_1fr_1fr] gap-2 items-end">
                            <div class="flex items-stretch">
                                <button type="button" wire:click="dec({{ $i }})" class="w-8 border-2 border-[#111] bg-white font-bold">−</button>
                                <input type="number" step="any" min="0" wire:model.live.debounce.400ms="cart.{{ $i }}.quantity" class="w-14 border-y-2 border-[#111] text-center text-sm ind-mono px-0">
                                <button type="button" wire:click="inc({{ $i }})" class="w-8 border-2 border-[#111] bg-white font-bold">+</button>
                            </div>
                            <label class="text-[10px] text-[#666]">Prix TTC<input type="number" step="0.01" min="0" wire:model.live.debounce.400ms="cart.{{ $i }}.prix_unitaire_ttc" class="{{ $input }} !py-1 ind-mono text-right"></label>
                            <label class="text-[10px] text-[#666]">Remise %<input type="number" step="0.01" min="0" max="100" wire:model.live.debounce.400ms="cart.{{ $i }}.remise_pct" class="{{ $input }} !py-1 ind-mono text-right"></label>
                        </div>
                        <div class="mt-1 text-right ind-mono text-sm font-bold">{{ $money($t['lignes'][$i]['total_ttc'] ?? 0) }}</div>
                    </div>
                @empty
                    <p class="p-6 text-center text-sm text-[#888]">Touchez un produit ou scannez un code-barres.</p>
                @endforelse
            </div>
            <div class="border-t-2 border-[#111] p-4 space-y-1">
                @foreach ($t['tva_detail'] as $d)
                    <div class="flex justify-between text-xs text-[#666] ind-mono"><span>TVA {{ rtrim(rtrim(number_format($d['taux'], 2, ',', ''), '0'), ',') }} % sur {{ $money($d['ht']) }}</span><span>{{ $money($d['tva']) }}</span></div>
                @endforeach
                <div class="flex justify-between items-end pt-1">
                    <span class="ind-h text-xl uppercase">Total TTC</span>
                    <span class="ind-h text-4xl leading-none">{{ $money($t['total_ttc']) }} <span class="text-base text-[#666]">{{ $currency }}</span></span>
                </div>
            </div>
            <div class="grid grid-cols-[auto_1fr] gap-2 p-3 pt-0">
                <button type="button" wire:click="clear" wire:confirm="Vider le ticket en cours ?" class="ind-h uppercase tracking-wider border-2 border-[#111] bg-white px-4 py-3" @disabled(! $cart)>Vider</button>
                <button type="button" wire:click="openPay" class="ind-h text-xl uppercase tracking-wider border-2 border-[#111] bg-[#ffcc00] px-4 py-3 shadow-[3px_3px_0_#111] disabled:opacity-40" @disabled(! $cart)>Encaisser</button>
            </div>
        </div>
    </div>

    {{-- phone: total + pay always at hand (the ticket sits below the product list) --}}
    @if ($cart && ! $payOpen)
        <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-[#111] border-t-4 border-[#ffcc00] px-3 py-2.5 flex items-center gap-3">
            <a href="#ticket" class="flex-1 min-w-0 text-white">
                <span class="block ind-mono text-[11px] text-[#bbb]">{{ count($cart) }} ligne(s) · voir le ticket</span>
                <span class="block ind-h text-2xl leading-none">{{ $money($t['total_ttc']) }} <span class="text-sm text-[#bbb]">{{ $currency }}</span></span>
            </a>
            <button type="button" wire:click="openPay" class="ind-h text-lg uppercase tracking-wider border-2 border-[#ffcc00] bg-[#ffcc00] px-5 py-2.5">Encaisser</button>
        </div>
    @endif

    {{-- payment --}}
    @if ($payOpen)
        <div class="fixed inset-0 z-50 bg-black/50 flex items-end sm:items-center justify-center p-0 sm:p-4" wire:click.self="$set('payOpen', false)">
            <div class="w-full sm:max-w-md bg-white border-2 border-[#111] shadow-[8px_8px_0_#111] max-h-[95vh] overflow-y-auto">
                <div class="bg-[#111] text-white px-4 py-3 flex justify-between items-end">
                    <span class="ind-h text-lg uppercase tracking-wider text-[#ffcc00]">Paiement</span>
                    <span class="ind-h text-3xl leading-none">{{ $money($t['total_ttc']) }} <span class="text-sm text-[#bbb]">{{ $currency }}</span></span>
                </div>
                <div class="p-4 space-y-3">
                    @foreach (\App\Support\Caisse::MODES as $mode => $label)
                        <div>
                            <div class="flex items-center justify-between"><label class="text-sm font-semibold" for="pay-{{ $mode }}">{{ $label }}</label>
                                <button type="button" wire:click="exact('{{ $mode }}')" class="text-xs font-semibold underline decoration-[#ffcc00] decoration-2">Montant exact</button></div>
                            <input id="pay-{{ $mode }}" type="number" step="0.01" min="0" inputmode="decimal" wire:model.live.debounce.300ms="pay.{{ $mode }}" class="{{ $input }} mt-1 !text-lg ind-mono text-right" placeholder="0,00">
                            @if ($mode === 'especes')
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    @foreach ([5, 10, 20, 50, 100, 200] as $b)
                                        @if ($b >= $t['total_ttc'] || $b >= 20)
                                            <button type="button" wire:click="cash({{ $b }})" class="border-2 border-[#111] px-2.5 py-1 text-sm ind-mono hover:bg-[#ffcc00]">{{ $b }}</button>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                    <div class="border-2 border-[#111] p-3 flex justify-between items-center {{ $diff >= 0 ? 'bg-[#dff3e6]' : 'bg-[#fff3c4]' }}">
                        <span class="font-semibold">{{ $diff >= 0 ? 'Rendu de monnaie' : 'Reste à payer' }}</span>
                        <span class="ind-h text-2xl">{{ $money(abs($diff)) }}</span>
                    </div>
                    @error('pay') <p class="text-sm font-semibold text-[#b42318]">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-[auto_1fr] gap-2 p-4 pt-0">
                    <button type="button" wire:click="$set('payOpen', false)" class="ind-h uppercase tracking-wider border-2 border-[#111] bg-white px-4 py-3">Retour</button>
                    <button type="button" wire:click="encaisser" wire:loading.attr="disabled" class="ind-h text-xl uppercase tracking-wider border-2 border-[#111] bg-[#ffcc00] px-4 py-3 shadow-[3px_3px_0_#111] disabled:opacity-40" @disabled($diff < 0)>Valider l’encaissement</button>
                </div>
            </div>
        </div>
    @endif
</div>
