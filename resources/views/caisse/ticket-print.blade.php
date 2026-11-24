@php
    $money = fn ($v) => \App\Support\Fmt::money($v);
    $rate = fn ($r) => rtrim(rtrim(number_format((float) $r, 2, ',', ''), '0'), ',');
    $cur = $s->currency ?: 'EUR';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $t->numero }}{{ $dup ? ' — Duplicata' : '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e6e3dc; font: 12px/1.35 'DejaVu Sans Mono', 'Courier New', monospace; color: #000; }
        .tk { width: 80mm; margin: 12px auto; background: #fff; padding: 4mm 4mm 6mm; }
        .c { text-align: center; } .r { text-align: right; } .b { font-weight: bold; }
        .big { font-size: 16px; } .sep { border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; } td { vertical-align: top; padding: 1px 0; }
        .dup { border: 2px solid #000; padding: 3px; margin: 4px 0; font-weight: bold; font-size: 14px; text-align: center; }
        .bar { text-align: center; margin: 10px auto 0; display: flex; gap: 8px; justify-content: center; }
        .bar button { font: bold 14px sans-serif; padding: 8px 14px; border: 2px solid #111; background: #ffcc00; cursor: pointer; }
        @media print { body { background: #fff; } .tk { margin: 0; width: auto; padding: 0 1mm; } .bar { display: none; } @page { size: 80mm auto; margin: 2mm; } }
    </style>
</head>
<body>
<div class="tk">
    <div class="c b big">{{ $s->name }}</div>
    @if ($s->address)<div class="c">{{ $s->address }}@if ($s->city && ! str_contains(mb_strtolower($s->address), mb_strtolower($s->city))), {{ $s->city }}@endif</div>@endif
    @if ($s->phone)<div class="c">Tél. {{ $s->phone }}</div>@endif
    @foreach (\App\Support\Countries::legalParts($s) as $part)<div class="c">{{ $part }}</div>@endforeach
    <div class="sep"></div>
    @if ($dup)<div class="dup">DUPLICATA N° {{ $dup }}</div>@endif
    @if ($t->isAnnulation())<div class="dup">ANNULATION DU TICKET {{ $t->annule?->numero }}</div>@endif
    <table>
        <tr><td>Ticket {{ $t->numero }}</td><td class="r">{{ $t->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td colspan="2">Caissier : {{ $t->user_name }}</td></tr>
        @if ($t->client_name)<tr><td colspan="2">Client : {{ $t->client_name }}</td></tr>@endif
    </table>
    <div class="sep"></div>
    <table>
        @foreach ($t->lignes as $l)
            <tr><td colspan="2">{{ $l->designation }}</td></tr>
            <tr><td>&nbsp;&nbsp;{{ \App\Support\Fmt::qty($l->quantity) }} x {{ $money($l->prix_unitaire_ttc) }}@if ((float) $l->remise_pct > 0) -{{ $rate($l->remise_pct) }}%@endif</td><td class="r">{{ $money($l->total_ttc) }}</td></tr>
        @endforeach
    </table>
    <div class="sep"></div>
    <table>
        <tr class="b big"><td>TOTAL TTC</td><td class="r">{{ $money($t->total_ttc) }} {{ $cur }}</td></tr>
    </table>
    <table style="margin-top:4px">
        <tr><td>Taux</td><td class="r">HT</td><td class="r">TVA</td><td class="r">TTC</td></tr>
        @foreach ($t->tva_detail as $d)
            <tr><td>{{ $rate($d['taux']) }} %</td><td class="r">{{ $money($d['ht']) }}</td><td class="r">{{ $money($d['tva']) }}</td><td class="r">{{ $money($d['ttc']) }}</td></tr>
        @endforeach
        @if ($s->tva_franchise)<tr><td colspan="4">TVA non applicable, art. 293 B du CGI</td></tr>@endif
    </table>
    <div class="sep"></div>
    <table>
        @foreach ($t->paiements as $p)
            <tr><td>{{ \App\Support\Caisse::MODES[$p->mode] ?? $p->mode }}</td><td class="r">{{ $money($p->mode === 'especes' && (float) $t->montant_recu > 0 ? $t->montant_recu : $p->montant) }}</td></tr>
        @endforeach
        @if ((float) $t->rendu > 0)<tr class="b"><td>Rendu</td><td class="r">{{ $money($t->rendu) }}</td></tr>@endif
    </table>
    <div class="sep"></div>
    <div class="c">Merci de votre visite</div>
    <div class="c" style="margin-top:6px;font-size:10px">Stokito Caisse v{{ \App\Support\Caisse::VERSION }} · Signature {{ strtoupper(substr($t->hash, 0, 8)) }}</div>
</div>
<div class="bar"><button type="button" onclick="window.print()">Imprimer</button><button type="button" onclick="window.close()" style="background:#fff">Fermer</button></div>
<script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 250); });</script>
</body>
</html>
