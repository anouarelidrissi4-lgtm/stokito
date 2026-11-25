<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    /* Classic Moroccan invoice layout in Stokito colours (2026-11-26): company left, logo centre, client right,
       yellow #ffcc00 table header, totals attached under the table, amount in words, legal footer. dompdf: tables only. */
    @page { margin: 26px 32px 96px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #111; }
    table { border-collapse: collapse; }
    .topbar { height: 5px; background: #ffcc00; margin-bottom: 14px; }
    table.head { width: 100%; }
    table.head td { vertical-align: top; padding: 0; }
    .co-name { margin: 0 0 3px; font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: .3px; }
    .co-act { margin: 0 0 4px; font-size: 10.5px; font-weight: bold; }
    .co p { margin: 1px 0; font-size: 10px; }
    .logo { text-align: center; }
    .logo img { max-height: 105px; max-width: 175px; }
    .place { text-align: right; font-size: 10.5px; margin-bottom: 12px; }
    table.meta { width: 100%; }
    table.meta td { padding: 2px 0; font-size: 10.5px; vertical-align: top; }
    table.meta td.k { width: 72px; padding-right: 6px; color: #555; white-space: nowrap; }
    table.meta td.v { font-weight: bold; }
    .title { text-align: center; margin: 20px 0 14px; }
    .title span { font-size: 19px; font-weight: bold; letter-spacing: 3px; text-transform: uppercase; border-bottom: 4px solid #ffcc00; padding: 0 8px 3px; }
    .objet { margin-bottom: 8px; font-size: 10.5px; }
    .objet p { margin: 1px 0; }
    table.items { width: 100%; }
    table.items th { background: #ffcc00; color: #111; font-size: 10px; font-weight: bold; padding: 6px 5px; border: 1px solid #111; text-align: center; }
    table.items td { border: 1px solid #111; padding: 5px 6px; font-size: 10.5px; }
    table.items td.c { text-align: center; }
    table.items td.r { text-align: right; white-space: nowrap; }
    table.items td.blank { border: 0; }
    table.items tr.t td.l { background: #fff4c2; font-size: 10px; font-weight: bold; text-align: center; text-transform: uppercase; }
    table.items tr.t td.r { font-weight: bold; }
    table.items tr.g td.l, table.items tr.g td.r { background: #ffcc00; font-size: 11.5px; }
    .words { margin-top: 14px; font-size: 11px; }
    .words b { font-weight: bold; }
    .small { margin-top: 8px; font-size: 9.5px; color: #333; }
    .sign { width: 230px; height: 105px; border: 1px dashed #a1a1aa; vertical-align: top; text-align: center; padding-top: 6px; font-size: 9px; color: #555; }
    .footer { position: fixed; bottom: -76px; left: 0; right: 0; text-align: center; font-size: 8.5px; color: #111; border-top: 2px solid #ffcc00; padding-top: 5px; }
    .footer p { margin: 1px 0; }
    .footer .l1 { font-weight: bold; font-size: 9px; }
    .footer .gen { margin-top: 3px; color: #a1a1aa; font-size: 7px; }
</style>
</head>
<body>
    @php
        // "sans entête": the paper already carries the letterhead -> no logo / company block / legal footer
        $withHeader = $showHeader ?? true;
        $cty = \App\Support\Countries::for($societe);
        $legalParts = \App\Support\Countries::legalParts($societe);
        $isFr = ($societe->country_code ?? '') === 'FR';
        $groups = $totals['groups'] ?? null;
        $multiVat = $showPrices && is_array($groups) && count($groups) > 1;
        $franchise = $isFr && $societe->tva_franchise && in_array($title, ['Facture', 'Devis'], true);
        // French invoice mentions (2026-11-24), printed only on invoices of French companies
        $mentions = [];
        if ($franchise) { $mentions[] = 'TVA non applicable, art. 293 B du CGI.'; }
        if ($isFr && $title === 'Facture') {
            $f = $facture ?? null;
            $nat = ['biens' => 'livraison de biens', 'services' => 'prestation de services', 'mixte' => 'livraison de biens et prestation de services'];
            if ($f && $f->nature_operation && isset($nat[$f->nature_operation])) { $mentions[] = 'Nature des opérations : '.$nat[$f->nature_operation].'.'; }
            if ($societe->tva_debits) { $mentions[] = 'Option pour le paiement de la taxe d’après les débits.'; }
            if ($societe->penalites_taux) { $mentions[] = 'Pénalités de retard : '.rtrim($societe->penalites_taux, '. ').', exigibles sans rappel.'; }
            if ($societe->indemnite_40) { $mentions[] = 'Indemnité forfaitaire pour frais de recouvrement en cas de retard de paiement : 40 € (clients professionnels).'; }
            if ($societe->escompte_neant) { $mentions[] = 'Escompte pour paiement anticipé : néant.'; }
        }
        $deliveryAddress = ($title === 'Facture' && isset($facture) && $facture->adresse_livraison) ? $facture->adresse_livraison : null;
        $words = null;
        if (! empty($totals['ttc']) && in_array($title, ['Facture', 'Devis'], true) && class_exists(\NumberFormatter::class)) {
            $nf = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
            $ttc = round((float) $totals['ttc'], 2);
            $dh = (int) floor($ttc);
            $cts = (int) round(($ttc - $dh) * 100);
            [$one, $many, $sub] = \App\Support\Countries::words($societe->currency ?: 'MAD');
            $subs = strtoupper((string) $societe->currency) === 'TND' ? (int) round(($ttc - $dh) * 1000) : $cts;
            $words = ucfirst($nf->format($dh)).' '.($dh > 1 ? $many : $one).($sub && $subs ? ' et '.$nf->format($subs).' '.$sub : '').' TTC';
        }
        $partyName = trim(($party->full_name ?? null) ?: ($party->name ?? ''));
        $phones = implode(' / ', array_filter([$societe->phone, $societe->phone2]));
        // city printed separately only when the address does not already contain it
        $cityExtra = ($societe->city && ! str_contains(mb_strtolower((string) $societe->address), mb_strtolower($societe->city))) ? $societe->city : null;
        $cur = $societe->currency;
        $hasHt = isset($totals['ht']);
        $cols = 4 + ($showPrices ? 2 + ($multiVat ? 1 : 0) : 0);   // N°, désignation, unité, quantité [, PU, TVA, total]
        $money = fn ($v) => \App\Support\Fmt::money($v);
    @endphp

    @if ($withHeader)<div class="topbar"></div>@endif
    <table class="head" @if (! $withHeader) style="margin-top:90px;" @endif>
        <tr>
            <td style="width:38%;" class="co">
                @if ($withHeader)
                    <p class="co-name">{{ $societe->name }}</p>
                    @if ($societe->activite)<p class="co-act">{{ $societe->activite }}</p>@endif
                    @if ($societe->address)<p>{{ $societe->address }}</p>@endif
                    @if ($cityExtra)<p>{{ $cityExtra }}</p>@endif
                    @if ($phones)<p>Tél. : {{ $phones }}</p>@endif
                    @if ($societe->email)<p>{{ $societe->email }}</p>@endif
                @endif
            </td>
            <td style="width:24%;" class="logo">
                @if ($withHeader && $societe->logoDataUri())<img src="{{ $societe->logoDataUri() }}">@endif
            </td>
            <td style="width:38%;">
                <div class="place">{{ $societe->city ? $societe->city.', le' : 'Le' }} <strong>{{ $date }}</strong></div>
                <table class="meta">
                    <tr><td class="k">{{ $title }} N°</td><td class="v">{{ $numero }}</td></tr>
                    <tr><td class="k">{{ $partyLabel }}</td><td class="v">{{ $partyName }}</td></tr>
                    @if ($party->address ?? false)<tr><td class="k">Adresse</td><td>{{ $party->address }}</td></tr>@endif
                    @if ($party->ice ?? false)<tr><td class="k">{{ $cty['client'] }}</td><td class="v">{{ $party->ice }}</td></tr>@endif
                    @if ($party->phone ?? false)<tr><td class="k">Tél.</td><td>{{ $party->phone }}</td></tr>@endif
                    @if (! empty($dueDate))<tr><td class="k">Échéance</td><td>{{ $dueDate }}</td></tr>@endif
                    @if (! empty($validUntil))<tr><td class="k">Valable au</td><td>{{ $validUntil }}</td></tr>@endif
                    @if (! empty($devisRef))<tr><td class="k">Devis</td><td>{{ $devisRef }}</td></tr>@endif
                </table>
            </td>
        </tr>
    </table>

    <div class="title"><span>{{ $title }}</span></div>

    @if (! empty($objet) || $deliveryAddress)
        <div class="objet">
            @if (! empty($objet))<p><strong>Objet :</strong> {{ $objet }}</p>@endif
            @if ($deliveryAddress)<p><strong>Livraison :</strong> {{ preg_replace('/\s*\R\s*/', ', ', trim($deliveryAddress)) }}</p>@endif
        </div>
    @endif

    <table class="items">
        <thead>
            <tr>
                <th style="width:5%;">N°</th>
                <th>Désignation</th>
                <th style="width:8%;">Unité</th>
                <th style="width:10%;">Quantité</th>
                @if ($showPrices)
                    <th style="width:13%;">Prix unitaire{{ $hasHt ? ' HT' : '' }}</th>
                    @if ($multiVat)<th style="width:7%;">TVA</th>@endif
                    <th style="width:15%;">Total{{ $hasHt ? ' HT' : '' }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $item)
                <tr>
                    <td class="c">{{ $loop->iteration }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td class="c">{{ $item['unit'] ?: '-' }}</td>
                    <td class="c">{{ \App\Support\Fmt::qty($item['quantity']) }}</td>
                    @if ($showPrices)
                        <td class="r">{{ $item['unit_price'] !== null ? $money($item['unit_price']) : '' }}</td>
                        @if ($multiVat)<td class="c">{{ \App\Support\DocTotals::label($item['tva_rate'] ?? 0) }} %</td>@endif
                        <td class="r">{{ $item['total'] !== null ? $money($item['total']) : '' }}</td>
                    @endif
                </tr>
            @endforeach

            @if ($showPrices && $totals)
                @php $lc = $cols - 3; @endphp
                @if ($hasHt)
                    @if (! empty($totals['remise']))
                        <tr class="t"><td class="blank" colspan="2"></td><td class="l" colspan="{{ $lc }}">Total brut HT</td><td class="r">{{ $money($totals['brut']) }}</td></tr>
                        <tr class="t"><td class="blank" colspan="2"></td><td class="l" colspan="{{ $lc }}">Remise</td><td class="r">- {{ $money($totals['remise']) }}</td></tr>
                    @endif
                    <tr class="t"><td class="blank" colspan="2"></td><td class="l" colspan="{{ $lc }}">Total HT</td><td class="r">{{ $money($totals['ht']) }}</td></tr>
                    @if ($franchise && (float) $totals['ttc'] === (float) $totals['ht'])
                        <tr class="t"><td class="blank" colspan="2"></td><td class="l" colspan="{{ $lc }}">TVA</td><td class="r">non applicable</td></tr>
                    @elseif (is_array($groups) && count($groups))
                        @foreach ($groups as $g)
                            <tr class="t"><td class="blank" colspan="2"></td><td class="l" colspan="{{ $lc }}">TVA {{ \App\Support\DocTotals::label($g['rate']) }} %@if ($multiVat) <span style="font-weight:normal; text-transform:none;">sur {{ $money($g['base']) }}</span>@endif</td><td class="r">{{ $money($g['tva']) }}</td></tr>
                        @endforeach
                    @else
                        <tr class="t"><td class="blank" colspan="2"></td><td class="l" colspan="{{ $lc }}">TVA {{ rtrim(rtrim(number_format($totals['tva_rate'], 2), '0'), '.') }} %</td><td class="r">{{ $money($totals['ttc'] - $totals['ht']) }}</td></tr>
                    @endif
                    <tr class="t g"><td class="blank" colspan="2"></td><td class="l" colspan="{{ $lc }}">Total TTC ({{ $cur }})</td><td class="r">{{ $money($totals['ttc']) }}</td></tr>
                @else
                    <tr class="t g"><td class="blank" colspan="2"></td><td class="l" colspan="{{ $lc }}">Total ({{ $cur }})</td><td class="r">{{ $money($totals['ttc']) }}</td></tr>
                @endif
            @endif
        </tbody>
    </table>

    @if ($words)
        <div class="words"><b>Arrêté{{ $title === 'Facture' ? 'e la présente facture' : ' le présent devis' }} à la somme de :</b> {{ $words }}.</div>
    @endif
    @if ($title === 'Facture' && $societe->conditions_paiement)
        <div class="small"><strong>Conditions de paiement :</strong> {{ $societe->conditions_paiement }}</div>
    @endif
    @if (! empty($notes))
        <div class="small"><strong>Notes :</strong> {{ $notes }}</div>
    @endif
    @if ($mentions)
        <div class="small">@foreach ($mentions as $m){{ $m }}@if (! $loop->last)<br>@endif @endforeach</div>
    @endif

    <table style="width:100%; margin-top:26px; page-break-inside:avoid;">
        <tr>
            @if ($title === 'Devis')
                <td></td>
                <td class="sign">Cachet de l’entreprise</td>
                <td style="width:18px;"></td>
                <td class="sign">Bon pour accord<br>date et signature du client</td>
            @elseif ($title === 'Bon de Livraison')
                <td></td>
                <td class="sign">Cachet de l’entreprise</td>
                <td style="width:18px;"></td>
                <td class="sign">Reçu par le client<br>date et signature</td>
            @else
                <td></td>
                <td class="sign">Cachet et signature</td>
            @endif
        </tr>
    </table>

    <div class="footer">
        @if ($withHeader)
            @php $l1 = array_filter([$phones ? 'Tél. : '.$phones : null, ...$legalParts]); @endphp
            @if ($l1)<p class="l1">{{ implode(' - ', $l1) }}</p>@endif
            @php $l2 = array_filter([trim(($societe->address ?? '').' '.($cityExtra ?? '')), $societe->rib]); @endphp
            @if ($l2)<p>{{ implode(' · ', $l2) }}</p>@endif
            @if ($societe->email)<p>{{ $societe->email }}</p>@endif
        @endif
        <p class="gen">Document généré par Stokito le {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
