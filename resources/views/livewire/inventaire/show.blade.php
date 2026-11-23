<div class="p-4 sm:p-6 lg:p-8 max-w-6xl">
    <a href="{{ route('inventaire.index') }}" wire:navigate class="text-sm text-slate-500 hover:text-slate-700">&larr; Inventaires</a>
    <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Inventaire {{ $inv->numero }}</h1>
            <p class="text-sm text-slate-500">{{ $inv->date->format('d/m/Y') }} · par {{ $inv->user->name ?? '-' }} · {{ $inv->nb_produits }} produit(s) compté(s), {{ $inv->nb_ecarts }} écart(s) corrigé(s)</p>
        </div>
        <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 print:hidden">Imprimer</button>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('success') }}</div>
    @endif
    @if ($inv->notes)
        <div class="mb-4 rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-700">{{ $inv->notes }}</div>
    @endif

    @php $value = $inv->items->sum(fn ($i) => $i->ecart * (float) $i->purchase_price); @endphp
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Produit</th>
                        <th class="px-4 py-3 text-end text-xs font-semibold text-slate-500 uppercase">Stock système</th>
                        <th class="px-4 py-3 text-end text-xs font-semibold text-slate-500 uppercase">Compté</th>
                        <th class="px-4 py-3 text-end text-xs font-semibold text-slate-500 uppercase">Écart</th>
                        @if ($seePrice)<th class="px-4 py-3 text-end text-xs font-semibold text-slate-500 uppercase">Valeur écart</th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($inv->items->sortBy(fn ($i) => $i->ecart === 0 ? 1 : 0) as $i)
                        <tr>
                            <td class="px-4 py-2.5">
                                <div class="text-sm font-medium text-slate-900">{{ $i->product->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $i->product->reference ?? '' }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-end text-sm text-slate-600">{{ $i->stock_systeme }}</td>
                            <td class="px-4 py-2.5 text-end text-sm text-slate-900 font-medium">{{ $i->stock_compte }}</td>
                            <td class="px-4 py-2.5 text-end text-sm font-semibold {{ $i->ecart === 0 ? 'text-emerald-600' : ($i->ecart > 0 ? 'text-indigo-600' : 'text-rose-600') }}">{{ $i->ecart === 0 ? 'OK' : ($i->ecart > 0 ? '+'.$i->ecart : $i->ecart) }}</td>
                            @if ($seePrice)<td class="px-4 py-2.5 text-end text-sm text-slate-600 whitespace-nowrap">{{ $i->ecart ? \App\Support\Fmt::money($i->ecart * (float) $i->purchase_price) : '' }}</td>@endif
                        </tr>
                    @endforeach
                </tbody>
                @if ($seePrice)
                    <tfoot class="bg-slate-50">
                        <tr><td colspan="4" class="px-4 py-3 text-end text-sm font-semibold text-slate-700">Valeur totale des écarts (prix d'achat)</td>
                            <td class="px-4 py-3 text-end text-sm font-bold {{ $value < 0 ? 'text-rose-600' : 'text-indigo-600' }} whitespace-nowrap">{{ \App\Support\Fmt::money($value) }}</td></tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
