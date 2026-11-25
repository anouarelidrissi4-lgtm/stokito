@php
    $rtl = app()->getLocale() === 'ar';
    $money = fn ($v) => \App\Support\Fmt::money($v);
    $has = fn ($c) => in_array($c, $cols, true);
    $valeurAchat = $products->sum(fn ($p) => max(0, (float) $p->stock_qty) * (float) $p->purchase_price);
    $valeurVente = $products->sum(fn ($p) => max(0, (float) $p->stock_qty) * (float) $p->sale_price);
    $currency = $societe->currency ?? '';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('Liste des produits') }} · {{ $societe->name ?? 'Stokito' }}</title>
    <style>
        @page { size: A4 {{ count($cols) >= 4 ? 'landscape' : 'portrait' }}; margin: 12mm 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; color: #18181b; font-size: 11.5px; background: #f4f4f5; }
        .sheet { max-width: 1100px; margin: 24px auto; background: #fff; padding: 28px 32px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .bar { max-width: 1100px; margin: 20px auto 0; display: flex; gap: 8px; justify-content: flex-end; }
        .bar button, .bar a { font: 600 13px/1 inherit; padding: 10px 16px; border-radius: 8px; border: 1px solid #d4d4d8; background: #fff; color: #18181b; cursor: pointer; text-decoration: none; }
        .bar button { background: #18181b; color: #fff; border-color: #18181b; }
        header { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; border-bottom: 2px solid #18181b; padding-bottom: 12px; }
        h1 { margin: 0; font-size: 20px; }
        .muted { color: #71717a; }
        .co { font-weight: 700; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        thead { display: table-header-group; }
        th { text-align: start; font-weight: 600; font-size: 10.5px; color: #52525b; background: #f4f4f5; padding: 7px 8px; border-bottom: 1px solid #d4d4d8; }
        td { padding: 6px 8px; border-bottom: 1px solid #e4e4e7; vertical-align: top; }
        tr { page-break-inside: avoid; }
        .num { text-align: end; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .ref { font-weight: 600; white-space: nowrap; }
        .low { color: #b45309; font-weight: 600; } .out { color: #dc2626; font-weight: 600; }
        .totals { margin-top: 14px; display: flex; gap: 24px; justify-content: flex-end; font-size: 12px; }
        .totals b { font-size: 13px; }
        footer { margin-top: 18px; font-size: 10px; color: #a1a1aa; display: flex; justify-content: space-between; }
        @media print { body { background: #fff; } .sheet { margin: 0; padding: 0; box-shadow: none; border-radius: 0; max-width: none; } .bar { display: none; } }
    </style>
</head>
<body>
    <div class="bar">
        <a href="{{ route('products.index') }}">← {{ __('Produits') }}</a>
        <button type="button" onclick="window.print()">{{ __('Imprimer') }}</button>
    </div>
    <div class="sheet">
        <header>
            <div>
                <div class="co">{{ $societe->name ?? '' }}</div>
                <h1>{{ __('Liste des produits') }}</h1>
                <div class="muted">
                    {{ $products->count() }} {{ __('produit(s)') }}
                    @if ($category) · {{ __('Catégorie') }} : {{ $category }} @endif
                    @if ($search !== '') · « {{ $search }} » @endif
                </div>
            </div>
            <div class="muted">{{ now()->format('d/m/Y H:i') }}</div>
        </header>

        <table>
            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>{{ __('Désignation') }}</th>
                    @if ($has('categorie'))<th>{{ __('Catégorie') }}</th>@endif
                    @if ($has('marque'))<th>{{ __('Marque') }}</th>@endif
                    @if ($has('fournisseur'))<th>{{ __('Fournisseur') }}</th>@endif
                    @if ($has('achat'))<th class="num">{{ __('Prix achat') }}</th>@endif
                    <th class="num">{{ __('Prix vente TTC') }}</th>
                    @if ($has('gros'))<th class="num">{{ __('Prix gros') }}</th>@endif
                    <th class="num">{{ __('Stock') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $p)
                    <tr>
                        <td class="ref">{{ $p->reference }}</td>
                        <td>{{ $p->name }}</td>
                        @if ($has('categorie'))<td>{{ $p->category?->name ?? '—' }}</td>@endif
                        @if ($has('marque'))<td>{{ $p->brand?->name ?? '—' }}</td>@endif
                        @if ($has('fournisseur'))<td>{{ $p->fournisseur?->name ?? '—' }}</td>@endif
                        @if ($has('achat'))<td class="num">{{ $money($p->purchase_price) }}</td>@endif
                        <td class="num">{{ $money($p->sale_price) }}</td>
                        @if ($has('gros'))<td class="num">{{ $p->wholesale_price !== null ? $money($p->wholesale_price) : '—' }}</td>@endif
                        <td class="num {{ $p->stock_qty <= 0 ? 'out' : ($p->isLowStock() ? 'low' : '') }}">{{ \App\Support\Fmt::qty($p->stock_qty) }} <span class="muted">{{ $p->unit }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="muted" style="text-align:center;padding:24px">{{ __('Aucun produit.') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals">
            @if ($has('achat'))<span>{{ __('Valeur du stock (achat)') }} : <b>{{ $money($valeurAchat) }} {{ $currency }}</b></span>@endif
            <span>{{ __('Valeur du stock (vente TTC)') }} : <b>{{ $money($valeurVente) }} {{ $currency }}</b></span>
        </div>
        <footer><span>Stokito</span><span>{{ now()->format('d/m/Y') }}</span></footer>
    </div>
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
