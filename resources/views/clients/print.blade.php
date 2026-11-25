@php
    $rtl = app()->getLocale() === 'ar';
    $idLabel = \App\Support\Countries::for($societe)['client'];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('Liste des clients') }} · {{ $societe->name ?? 'Stokito' }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm 10mm; }
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
        .code { font-weight: 600; white-space: nowrap; }
        footer { margin-top: 18px; font-size: 10px; color: #a1a1aa; display: flex; justify-content: space-between; }
        @media print { body { background: #fff; } .sheet { margin: 0; padding: 0; box-shadow: none; border-radius: 0; max-width: none; } .bar { display: none; } }
    </style>
</head>
<body>
    <div class="bar">
        <a href="{{ route('clients.index') }}">← {{ __('Clients') }}</a>
        <button type="button" onclick="window.print()">{{ __('Imprimer') }}</button>
    </div>
    <div class="sheet">
        <header>
            <div>
                <div class="co">{{ $societe->name ?? '' }}</div>
                <h1>{{ __('Liste des clients') }}</h1>
                <div class="muted">{{ $clients->count() }} {{ __('client(s)') }}@if ($search !== '') · « {{ $search }} »@endif</div>
            </div>
            <div class="muted">{{ now()->format('d/m/Y H:i') }}</div>
        </header>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Code client') }}</th>
                    <th>{{ __('Nom') }}</th>
                    <th>{{ __('Prénom') }}</th>
                    <th>{{ $idLabel }}</th>
                    <th>{{ __('Téléphone') }}</th>
                    <th>Email</th>
                    <th>{{ __('Adresse') }}</th>
                    <th class="num">{{ __('Solde') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $c)
                    <tr>
                        <td class="code">{{ $c->code ?? '—' }}</td>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->prenom ?? '—' }}</td>
                        <td>{{ $c->ice ?? '—' }}</td>
                        <td style="white-space:nowrap">{{ $c->phone ?? '—' }}</td>
                        <td>{{ $c->email ?? '—' }}</td>
                        <td>{{ $c->address ?? '—' }}</td>
                        <td class="num">{{ \App\Support\Fmt::money($c->solde()) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted" style="text-align:center;padding:24px">{{ __('Aucun client.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <footer><span>Stokito</span><span>{{ now()->format('d/m/Y') }}</span></footer>
    </div>
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
