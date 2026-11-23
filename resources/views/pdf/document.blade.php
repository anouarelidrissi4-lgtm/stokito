<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 30px 35px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1f2937; }
    .header { width: 100%; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 2px solid #4f46e5; }
    .header .logobox { text-align: center; margin-bottom: 14px; }
    .header .logobox img { max-height: 70px; max-width: 240px; }
    .header table.headrow { width: 100%; border-collapse: collapse; }
    .header table.headrow td { vertical-align: top; padding: 0; }
    .header td.societe { width: 60%; text-align: left; }
    .header td.docmeta { width: 40%; text-align: right; }
    .societe h1 { margin: 0 0 4px; font-size: 18px; color: #111827; }
    .societe p { margin: 1px 0; font-size: 10.5px; color: #4b5563; }
    .docmeta h2 { margin: 0 0 6px; font-size: 16px; color: #4f46e5; text-transform: uppercase; letter-spacing: 1px; }
    .docmeta p { margin: 1px 0; font-size: 11px; }
    .clientbox { width: 100%; overflow: hidden; margin-bottom: 18px; }
    .clientbox .inner { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 14px; width: 45%; }
    .clientbox .label { font-size: 9.5px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .clientbox .name { font-size: 13px; font-weight: bold; color: #111827; margin-bottom: 2px; }
    .clientbox p { margin: 1px 0; font-size: 10.5px; color: #4b5563; }
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    table.items th { background: #4f46e5; color: #fff; font-size: 10px; text-transform: uppercase; padding: 7px 8px; text-align: left; }
    table.items th.num { text-align: right; }
    table.items td { padding: 7px 8px; font-size: 11px; border-bottom: 1px solid #e5e7eb; }
    table.items td.num { text-align: right; }
    table.items tr:nth-child(even) td { background: #f9fafb; }
    .totals { width: 100%; overflow: hidden; }
    .totals table { float: right; width: 260px; border-collapse: collapse; }
    .totalsbox td { padding: 5px 8px; font-size: 11px; }
    .totalsbox td.lbl { color: #6b7280; }
    .totalsbox td.val { text-align: right; font-weight: bold; white-space: nowrap; }
    .totalsbox tr.grand td { border-top: 2px solid #4f46e5; font-size: 13px; color: #4f46e5; padding-top: 8px; }
    .notes { margin-top: 20px; font-size: 10.5px; color: #6b7280; }
    .words { margin-top: 14px; font-size: 11px; color: #111827; }
    .conditions { margin-top: 10px; font-size: 10px; color: #4b5563; }
    .legal { font-size: 8.5px; color: #6b7280; margin-bottom: 2px; }
    .footer { position: fixed; bottom: -15px; left: 0; right: 0; text-align: center; font-size: 9px; color: #9ca3af; }
</style>
</head>
<body>

    @php
        // "sans entête": the paper already carries the letterhead -> no logo / company block
        $withHeader = $showHeader ?? true;
        $legal = implode(' · ', \App\Support\Countries::legalParts($societe));
        $cty = \App\Support\Countries::for($societe);
        $words = null;
        if (! empty($totals['ttc']) && in_array($title, ['Facture', 'Devis'], true) && class_exists(\NumberFormatter::class)) {
            $nf = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
            $ttc = round((float) $totals['ttc'], 2);
            $dh = (int) floor($ttc);
            $cts = (int) round(($ttc - $dh) * 100);
            [$one, $many, $sub] = \App\Support\Countries::words($societe->currency ?: 'MAD');
            $subs = strtoupper($societe->currency) === 'TND' ? (int) round(($ttc - $dh) * 1000) : $cts;
            $words = ucfirst($nf->format($dh)).' '.($dh > 1 ? $many : $one).($sub && $subs ? ' et '.$nf->format($subs).' '.$sub : '').' TTC';
        }
    @endphp
    <div class="header" @if (! $withHeader) style="border-bottom:0; padding-top:90px;" @endif>
        @if ($withHeader && $societe->logoDataUri())
            <div class="logobox">
                <img src="{{ $societe->logoDataUri() }}">
            </div>
        @endif
        <table class="headrow">
            <tr>
                <td class="societe">
                    @if ($withHeader)
                    <h1>{{ $societe->name }}</h1>
                    @if ($societe->address)<p>{{ $societe->address }}</p>@endif
                    <p>
                        @if ($societe->phone) Tel: {{ $societe->phone }} @endif
                        @if ($societe->email) &middot; {{ $societe->email }} @endif
                    </p>
                    <p>{{ implode(' · ', \App\Support\Countries::legalParts($societe)) }}</p>
                    @endif
                </td>
                <td class="docmeta">
                    <h2>{{ $title }}</h2>
                    <p><strong>N&deg;</strong> {{ $numero }}</p>
                    <p><strong>Date:</strong> {{ $date }}</p>
                    @if (!empty($dueDate))<p><strong>Échéance:</strong> {{ $dueDate }}</p>@endif
                    @if (!empty($status))<p><strong>Statut :</strong> {{ \App\Support\Fmt::status(str_replace(' ', '_', $status)) }}</p>@endif
                </td>
            </tr>
        </table>
    </div>

    <div class="clientbox">
        <div class="inner">
            <div class="label">{{ $partyLabel }}</div>
            <div class="name">{{ $party->name }}</div>
            @if ($party->address ?? false)<p>{{ $party->address }}</p>@endif
            @if ($party->phone ?? false)<p>Tel: {{ $party->phone }}</p>@endif
            @if ($party->ice ?? false)<p>{{ $cty['client'] }} : {{ $party->ice }}</p>@endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Produit</th>
                <th class="num">Quantité</th>
                @if ($showPrices)
                    <th class="num">Prix unitaire{{ isset($totals['ht']) ? ' HT' : '' }}</th>
                    <th class="num">Total{{ isset($totals['ht']) ? ' HT' : '' }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="num">{{ \App\Support\Fmt::qty($item['quantity']) }} {{ $item['unit'] ?? '' }}</td>
                    @if ($showPrices)
                        <td class="num">{{ \App\Support\Fmt::money($item['unit_price']) }}</td>
                        <td class="num">{{ \App\Support\Fmt::money($item['total']) }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- totals + amount in words + stamp, laid out with tables (dompdf drops floats inside overflow:hidden) --}}
    <table style="width:100%; border-collapse:collapse; page-break-inside:avoid;">
        <tr>
            <td style="vertical-align:top; padding:0 18px 0 0;">
                @if ($words)
                    <div class="words" style="margin-top:4px;">Arrêté{{ $title === 'Facture' ? 'e la présente facture' : ' le présent devis' }} à la somme de :<br><strong>{{ $words }}</strong>.</div>
                @endif
                @if ($title === 'Facture' && $societe->conditions_paiement)
                    <div class="conditions"><strong>Conditions de paiement :</strong> {{ $societe->conditions_paiement }}</div>
                @endif
                @if (!empty($notes))
                    <div class="notes"><strong>Notes :</strong> {{ $notes }}</div>
                @endif
            </td>
            @if ($showPrices && $totals)
            <td style="vertical-align:top; width:270px; padding:0;">
                <table class="totalsbox" style="width:100%; border-collapse:collapse;">
                    @if (isset($totals['ht']))
                        @if (! empty($totals['remise']))
                            <tr><td class="lbl">Total brut HT</td><td class="val">{{ \App\Support\Fmt::money($totals['brut']) }} {{ $societe->currency }}</td></tr>
                            <tr><td class="lbl">Remise</td><td class="val">- {{ \App\Support\Fmt::money($totals['remise']) }} {{ $societe->currency }}</td></tr>
                        @endif
                        <tr><td class="lbl">Total HT</td><td class="val">{{ \App\Support\Fmt::money($totals['ht']) }} {{ $societe->currency }}</td></tr>
                        <tr><td class="lbl">TVA ({{ rtrim(rtrim(number_format($totals['tva_rate'], 2), '0'), '.') }} %)</td><td class="val">{{ \App\Support\Fmt::money($totals['ttc'] - $totals['ht']) }} {{ $societe->currency }}</td></tr>
                        <tr class="grand"><td class="lbl">Total TTC</td><td class="val">{{ \App\Support\Fmt::money($totals['ttc']) }} {{ $societe->currency }}</td></tr>
                    @else
                        <tr class="grand"><td class="lbl">Total</td><td class="val">{{ \App\Support\Fmt::money($totals['ttc']) }} {{ $societe->currency }}</td></tr>
                    @endif
                </table>
            </td>
            @endif
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; margin-top:40px; page-break-inside:avoid;">
        <tr>
            <td></td>
            <td style="width:220px; height:100px; border:1px solid #d1d5db; text-align:center; vertical-align:top; font-size:9.5px; color:#9ca3af; padding-top:6px;">Cachet et signature</td>
        </tr>
    </table>

    <div class="footer">
        @if ($withHeader && $legal)<div class="legal">{{ $societe->name }} &middot; {{ $legal }}</div>@endif
        Document généré par Stokito le {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
