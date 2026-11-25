{{-- one cell of the products list / print page (2026-11-25), $key = column key from App\Support\ProductColumns --}}
@switch($key)
    @case('ref'){{ $p->reference }}@break
    @case('name'){{ $p->name }}@break
    @case('barcode'){{ $p->barcode ?: '—' }}@break
    @case('categorie'){{ $p->category?->name ?? '—' }}@break
    @case('marque'){{ $p->brand?->name ?? '—' }}@break
    @case('fournisseur'){{ $p->fournisseur?->name ?? '—' }}@break
    @case('unit'){{ $p->unit ?: '—' }}@break
    @case('achat'){{ \App\Support\Fmt::money($p->purchase_price) }}@break
    @case('vente'){{ \App\Support\Fmt::money($p->sale_price) }}@break
    @case('gros'){{ $p->wholesale_price !== null ? \App\Support\Fmt::money($p->wholesale_price) : '—' }}@break
    @case('tva'){{ $p->tva_rate !== null ? rtrim(rtrim(number_format((float) $p->tva_rate, 2, ',', ''), '0'), ',').' %' : ($tvaDefault !== null ? rtrim(rtrim(number_format((float) $tvaDefault, 2, ',', ''), '0'), ',').' %' : '—') }}@break
    @case('stock')<span class="font-semibold {{ $p->stock_qty <= 0 ? 'text-red-600' : ($p->isLowStock() ? 'text-amber-600' : 'text-green-600') }}">{{ \App\Support\Fmt::qty($p->stock_qty) }}</span>@break
    @case('seuil'){{ $p->stock_alert_threshold !== null ? \App\Support\Fmt::qty($p->stock_alert_threshold) : '—' }}@break
@endswitch
