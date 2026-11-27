{{-- Reçu de paiement de salaire (module Personnel, 2026-11-28). Not an official payslip (no CNSS / IR): a receipt the worker signs. --}}
@php
    $money = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $c = $societe->pdfColors();
    $num = fn ($v) => rtrim(rtrim(number_format((float) $v, 1, ',', ' '), '0'), ',');
    $modes = \App\Support\Paie::MODES;
    $e = $paie->employe;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Reçu de paie — {{ $e->nom }}</title>
<style>
    @page { margin: 26px 30px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    .head td { vertical-align: top; }
    .soc { font-size: 13px; font-weight: bold; }
    .muted { color: #666; }
    h1 { text-align: center; font-size: 17px; margin: 22px 0 4px; }
    .bar { height: 3px; width: 120px; margin: 0 auto 18px; background: {{ $c['accent'] }}; }
    table.info { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    table.info td { padding: 4px 6px; }
    table.lines { width: 100%; border-collapse: collapse; }
    table.lines th { background: {{ $c['accent'] }}; color: {{ $c['on'] }}; text-align: left; padding: 7px 8px; font-size: 11px; }
    table.lines td { padding: 7px 8px; border-bottom: 1px solid #e5e5e5; }
    table.lines td.r, table.lines th.r { text-align: right; }
    tr.net td { font-weight: bold; font-size: 13px; background: {{ $c['tint'] }}; border-bottom: 0; }
    .sign { width: 100%; margin-top: 46px; }
    .sign td { width: 50%; vertical-align: top; padding: 0 8px; }
    .box { height: 70px; border: 1px dashed #aaa; border-radius: 6px; margin-top: 6px; }
    .note { margin-top: 26px; font-size: 9px; color: #777; }
</style>
</head>
<body>
<table class="head" style="width:100%">
    <tr>
        <td style="width:60%">
            <div class="soc">{{ $societe->name }}</div>
            @if ($societe->address)<div class="muted">{{ $societe->address }}{{ $societe->city ? ', '.$societe->city : '' }}</div>@endif
            @if ($societe->phone)<div class="muted">Tél. {{ $societe->phone }}</div>@endif
            @if ($societe->ice)<div class="muted">ICE {{ $societe->ice }}</div>@endif
        </td>
        <td style="width:40%; text-align:right">@if ($societe->logoDataUri())<img src="{{ $societe->logoDataUri() }}" style="max-height:70px; max-width:150px">@endif</td>
    </tr>
</table>

<h1>Reçu de paiement de salaire</h1>
<div class="bar"></div>

<table class="info">
    <tr><td style="width:22%" class="muted">Employé</td><td style="width:38%"><b>{{ $e->nom }}</b></td><td style="width:18%" class="muted">Période</td><td>{{ $paie->periodeLabel() }}</td></tr>
    <tr><td class="muted">Poste</td><td>{{ $e->poste ?: '—' }}</td><td class="muted">Payé le</td><td>{{ $paie->date_paiement->format('d/m/Y') }}</td></tr>
    <tr><td class="muted">CIN</td><td>{{ $e->cin ?: '—' }}</td><td class="muted">Mode</td><td>{{ $modes[$paie->mode_paiement] ?? $paie->mode_paiement }}</td></tr>
</table>

<table class="lines">
    <tr><th>Détail</th><th class="r">Montant ({{ $societe->currency }})</th></tr>
    <tr><td>Salaire de base — {{ \App\Models\Employe::TYPES[$e->type_paie] ?? '' }}, {{ $money($e->taux) }} {{ \App\Models\Employe::UNITES[$e->type_paie] ?? '' }}@if ((float) $paie->jours > 0) · {{ $num($paie->jours) }} jour{{ (float) $paie->jours > 1 ? 's' : '' }} travaillé{{ (float) $paie->jours > 1 ? 's' : '' }}@endif</td><td class="r">{{ $money($paie->base) }}</td></tr>
    @if ((float) $paie->prime > 0)<tr><td>Prime</td><td class="r">+ {{ $money($paie->prime) }}</td></tr>@endif
    @if ((float) $paie->avances > 0)<tr><td>Avances déjà versées</td><td class="r">− {{ $money($paie->avances) }}</td></tr>@endif
    @if ((float) $paie->retenue_credit > 0)<tr><td>Remboursement de crédit</td><td class="r">− {{ $money($paie->retenue_credit) }}</td></tr>@endif
    <tr class="net"><td>Net payé</td><td class="r">{{ $money($paie->net) }}</td></tr>
</table>
@if ($paie->notes)<p style="margin-top:10px"><span class="muted">Note :</span> {{ $paie->notes }}</p>@endif

<table class="sign">
    <tr>
        <td>L’employeur<div class="box"></div></td>
        <td>L’employé (lu et approuvé, reçu la somme ci-dessus)<div class="box"></div></td>
    </tr>
</table>
<p class="note">Reçu de paiement établi avec Stokito. Ce document ne remplace pas le bulletin de paie réglementaire.</p>
</body>
</html>
