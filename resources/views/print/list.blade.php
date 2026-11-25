{{-- Generic printable list (2026-11-26): devis, BL, factures, règlements, soldes clients. --}}
@php
    $rtl = app()->getLocale() === 'ar';
    $period = trim((($filters['dateStart'] ?? '') ? 'du '.\Illuminate\Support\Carbon::parse($filters['dateStart'])->format('d/m/Y') : '').' '.(($filters['dateEnd'] ?? '') ? 'au '.\Illuminate\Support\Carbon::parse($filters['dateEnd'])->format('d/m/Y') : ''));
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} · {{ $societe->name ?? 'Stokito' }}</title>
    <style>
        @page { size: A4 {{ count($cols) >= 7 ? 'landscape' : 'portrait' }}; margin: 12mm 10mm; }
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
        .totals { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
        .totals div { border: 1px solid #e4e4e7; border-radius: 8px; padding: 6px 12px; }
        .totals b { display: block; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        thead { display: table-header-group; }
        th { text-align: start; font-weight: 600; font-size: 10.5px; color: #52525b; background: #f4f4f5; padding: 7px 8px; border-bottom: 1px solid #d4d4d8; }
        td { padding: 6px 8px; border-bottom: 1px solid #e4e4e7; vertical-align: top; }
        tr { page-break-inside: avoid; }
        .r { text-align: end; white-space: nowrap; font-variant-numeric: tabular-nums; }
        td:first-child { font-weight: 600; white-space: nowrap; }
        footer { margin-top: 18px; font-size: 10px; color: #a1a1aa; display: flex; justify-content: space-between; }
        @media print { body { background: #fff; } .sheet { margin: 0; padding: 0; box-shadow: none; border-radius: 0; max-width: none; } .bar { display: none; } }
    </style>
</head>
<body>
    <div class="bar">
        <a href="{{ $back }}">← Retour</a>
        <button type="button" onclick="window.print()">Imprimer</button>
    </div>
    <div class="sheet">
        <header>
            <div>
                <div class="co">{{ $societe->name ?? '' }}</div>
                <h1>{{ $title }}</h1>
                <div class="muted">
                    {{ count($rows) }} ligne(s)@if ($period !== '') · {{ $period }}@endif
                    @if (($filters['search'] ?? '') !== '') · « {{ $filters['search'] }} »@endif
                </div>
            </div>
            <div class="muted">{{ now()->format('d/m/Y H:i') }}</div>
        </header>

        @if ($totals)
            <div class="totals">
                @foreach ($totals as $label => $value)<div><span class="muted">{{ $label }}</span><b>{{ $value }}</b></div>@endforeach
            </div>
        @endif

        <table>
            <thead><tr>
                @foreach ($cols as $c)<th class="{{ is_array($c) && ($c[1] ?? '') === 'r' ? 'r' : '' }}">{{ is_array($c) ? $c[0] : $c }}</th>@endforeach
            </tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>@foreach ($row as $i => $cell)<td class="{{ is_array($cols[$i]) && ($cols[$i][1] ?? '') === 'r' ? 'r' : '' }}">{{ $cell }}</td>@endforeach</tr>
                @empty
                    <tr><td colspan="{{ count($cols) }}" class="muted" style="text-align:center;padding:24px">Aucune ligne.</td></tr>
                @endforelse
            </tbody>
        </table>
        <footer><span>Stokito</span><span>{{ now()->format('d/m/Y') }}</span></footer>
    </div>
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
