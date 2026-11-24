@php
    $money = fn ($v) => \App\Support\Fmt::money($v);
    $rate = fn ($r) => rtrim(rtrim(number_format((float) $r, 2, ',', ''), '0'), ',');
    $cur = $s->currency ?: 'EUR';
    $label = ['jour' => 'JOURNALIÈRE', 'mois' => 'MENSUELLE', 'annee' => 'ANNUELLE'][$c->type] ?? strtoupper($c->type);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Z {{ $c->type }} n° {{ $c->sequence }} — {{ $c->periode }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e6e3dc; font: 12px/1.35 'DejaVu Sans Mono', 'Courier New', monospace; color: #000; }
        .tk { width: 80mm; margin: 12px auto; background: #fff; padding: 4mm 4mm 6mm; }
        .c { text-align: center; } .r { text-align: right; } .b { font-weight: bold; } .big { font-size: 15px; }
        .sep { border-top: 1px dashed #000; margin: 6px 0; } table { width: 100%; border-collapse: collapse; } td { padding: 1px 0; }
        .bar { text-align: center; margin: 10px auto 0; } .bar button { font: bold 14px sans-serif; padding: 8px 14px; border: 2px solid #111; background: #ffcc00; cursor: pointer; }
        @media print { body { background: #fff; } .tk { margin: 0; width: auto; padding: 0 1mm; } .bar { display: none; } @page { size: 80mm auto; margin: 2mm; } }
    </style>
</head>
<body>
<div class="tk">
    <div class="c b big">{{ $s->name }}</div>
    @foreach (\App\Support\Countries::legalParts($s) as $part)<div class="c">{{ $part }}</div>@endforeach
    <div class="sep"></div>
    <div class="c b big">CLÔTURE {{ $label }} (Z)</div>
    <table>
        <tr><td>Z n° {{ $c->sequence }}</td><td class="r">{{ $c->periode }}</td></tr>
        <tr><td>Établie le</td><td class="r">{{ $c->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Par</td><td class="r">{{ $c->user_name ?: 'clôture automatique' }}</td></tr>
        <tr><td>Tickets</td><td class="r">T-{{ str_pad($c->first_seq, 6, '0', STR_PAD_LEFT) }} → T-{{ str_pad($c->last_seq, 6, '0', STR_PAD_LEFT) }}</td></tr>
        <tr><td>Ventes / annulations</td><td class="r">{{ $c->nb_tickets }} / {{ $c->nb_annulations }}</td></tr>
    </table>
    <div class="sep"></div>
    <table>
        <tr><td>Taux</td><td class="r">HT</td><td class="r">TVA</td><td class="r">TTC</td></tr>
        @foreach ($c->tva_detail as $d)
            <tr><td>{{ $rate($d['taux']) }} %</td><td class="r">{{ $money($d['ht']) }}</td><td class="r">{{ $money($d['tva']) }}</td><td class="r">{{ $money($d['ttc']) }}</td></tr>
        @endforeach
        <tr class="b"><td>Total</td><td class="r">{{ $money($c->total_ht) }}</td><td class="r">{{ $money($c->total_tva) }}</td><td class="r">{{ $money($c->total_ttc) }}</td></tr>
    </table>
    <div class="sep"></div>
    <table>
        @foreach ($c->paiements as $mode => $v)
            <tr><td>{{ \App\Support\Caisse::MODES[$mode] ?? $mode }}</td><td class="r">{{ $money($v) }}</td></tr>
        @endforeach
    </table>
    <div class="sep"></div>
    <table>
        <tr class="b"><td>TOTAL PÉRIODE TTC</td><td class="r">{{ $money($c->total_ttc) }} {{ $cur }}</td></tr>
        <tr><td>Grand total perpétuel</td><td class="r">{{ $money($c->cumul_ttc) }}</td></tr>
        <tr><td>Grand total perpétuel (val. abs.)</td><td class="r">{{ $money($c->cumul_abs_ttc) }}</td></tr>
    </table>
    <div class="sep"></div>
    <div class="c" style="font-size:10px">Stokito Caisse v{{ \App\Support\Caisse::VERSION }} · Signature {{ strtoupper(substr($c->hash, 0, 8)) }}</div>
</div>
<div class="bar"><button type="button" onclick="window.print()">Imprimer</button></div>
</body>
</html>
