<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    /* Stokito industrial style (2026-11-23): ink #111, safety yellow #ffcc00, paper #f4f2ec. Print-friendly: few dark fills. */
    @page { margin: 26px 34px 40px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11.5px; color: #111; }
    .mono { font-family: Courier, monospace; }
    .topbar { height: 6px; background: #ffcc00; border-bottom: 2px solid #111; margin-bottom: 16px; }
    .header { width: 100%; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 2px solid #111; }
    .header .logobox { text-align: left; margin-bottom: 10px; }
    .header .logobox img { max-height: 70px; max-width: 240px; }
    .header table.headrow { width: 100%; border-collapse: collapse; }
    .header table.headrow td { vertical-align: top; padding: 0; }
    .header td.societe { width: 58%; text-align: left; }
    .header td.docmeta { width: 42%; text-align: right; }
    .societe h1 { margin: 0 0 5px; font-size: 17px; text-transform: uppercase; letter-spacing: .5px; }
    .societe p { margin: 1px 0; font-size: 10px; color: #444; }
    .doctitle { display: inline-block; background: #111; color: #fff; font-size: 17px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; padding: 5px 12px; margin-bottom: 8px; }
    .docmeta p { margin: 2px 0; font-size: 10.5px; }
    .docmeta .k { font-family: Courier, monospace; font-size: 9.5px; color: #555; text-transform: uppercase; }
    .clientbox { width: 100%; margin-bottom: 18px; }
    .clientbox .inner { border: 2px solid #111; padding: 9px 13px; width: 46%; }
    .clientbox .label { font-family: Courier, monospace; font-size: 9px; color: #555; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
    .clientbox .name { font-size: 13px; font-weight: bold; margin-bottom: 2px; }
    .clientbox p { margin: 1px 0; font-size: 10px; color: #444; }
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; border: 2px solid #111; }
    table.items th { background: #111; color: #fff; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; padding: 7px 8px; text-align: left; }
    table.items th.num { text-align: right; }
    table.items td { padding: 7px 8px; font-size: 10.5px; border-bottom: 1px solid #d6d3cc; }
    table.items td.num { text-align: right; font-family: Courier, monospace; font-size: 10.5px; }
    table.items tr:nth-child(even) td { background: #f4f2ec; }
    .totalsbox td { padding: 5px 9px; font-size: 10.5px; border-bottom: 1px solid #d6d3cc; }
    .totalsbox td.lbl { color: #444; }
    .totalsbox td.val { text-align: right; font-weight: bold; white-space: nowrap; font-family: Courier, monospace; }
    .totalsbox tr.grand td { background: #ffcc00; border-top: 2px solid #111; border-bottom: 2px solid #111; font-size: 13px; color: #111; padding: 8px 9px; font-weight: bold; }
    .notes { margin-top: 12px; font-size: 10px; color: #444; }
    .words { margin-top: 4px; font-size: 10.5px; border-left: 4px solid #ffcc00; padding: 4px 0 4px 9px; }
    .conditions { margin-top: 10px; font-size: 9.5px; color: #444; }
    .stamp { width: 220px; height: 100px; border: 2px dashed #111; text-align: center; vertical-align: top; font-family: Courier, monospace; font-size: 9px; color: #555; text-transform: uppercase; letter-spacing: 1px; padding-top: 6px; }
    .legal { font-size: 8.5px; color: #555; margin-bottom: 2px; }
    .footer { position: fixed; bottom: -22px; left: 0; right: 0; text-align: center; font-family: Courier, monospace; font-size: 8.5px; color: #777; border-top: 1px solid #d6d3cc; padding-top: 4px; }
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
    @if ($withHeader)<div class="topbar"></div>@endif
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
                        @if ($societe->phone) Tél. {{ $societe->phone }} @endif
                        @if ($societe->email) &middot; {{ $societe->email }} @endif
                    </p>
                    <p>{{ implode(' · ', \App\Support\Countries::legalParts($societe)) }}</p>
                    @endif
                </td>
                <td class="docmeta">
                    <div class="doctitle">{{ $title }}</div>
                    <p><span class="k">N&deg;</span> <strong class="mono" style="font-size:12px;">{{ $numero }}</strong></p>
                    <p><span class="k">Date</span> {{ $date }}</p>
                    @if (!empty($dueDate))<p><span class="k">Échéance</span> {{ $dueDate }}</p>@endif
                    @if (!empty($status))<p><span class="k">Statut</span> {{ \App\Support\Fmt::status(str_replace(' ', '_', $status)) }}</p>@endif
                </td>
            </tr>
        </table>
    </div>

    <div class="clientbox">
        <div class="inner">
            <div class="label">{{ $partyLabel }}</div>
            <div class="name">{{ $party->name }}</div>
            @if ($party->address ?? false)<p>{{ $party->address }}</p>@endif
            @if ($party->phone ?? false)<p>Tél. {{ $party->phone }}</p>@endif
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
            <td class="stamp">Cachet et signature</td>
        </tr>
    </table>

    <div class="footer">
        @if ($withHeader && $legal)<div class="legal">{{ $societe->name }} &middot; {{ $legal }}</div>@endif
        Document généré par Stokito le {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
