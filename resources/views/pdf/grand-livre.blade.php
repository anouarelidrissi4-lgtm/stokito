{{-- Grand livre client, printable (2026-11-27) — same layout as the user's model --}}
@php
    $money = fn ($v) => \App\Support\Fmt::money($v);
    $phones = implode(' / ', array_filter([$client->phone, $client->phone2 ?? null]));
    $idLabel = \App\Support\Countries::for($societe)['client'];
    $clientName = trim($client->name.' '.($client->prenom ?? ''));
    $filtres = array_filter([
        $from ? 'du '.\Illuminate\Support\Carbon::parse($from)->format('d/m/Y') : null,
        $to ? 'au '.\Illuminate\Support\Carbon::parse($to)->format('d/m/Y') : null,
        $hidePaid ? 'BL / factures payés masqués' : null,
    ]);
@endphp
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Grand livre client {{ $clientName }}</title>
<style>
    @page { margin: 28px 30px 46px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2328; }
    table { border-collapse: collapse; width: 100%; }
    .top td { vertical-align: top; }
    h1 { font-size: 19px; margin: 0; }
    .sub { color: #6b7280; font-size: 10px; margin-top: 3px; }
    .co { text-align: right; font-size: 10px; line-height: 1.45; }
    .co b { font-size: 11px; }
    .rule { border-bottom: 1px solid #6b7280; margin: 14px 0 22px; }
    h2 { font-size: 12px; margin: 0 0 6px; padding-bottom: 5px; border-bottom: 1px solid #e5e7eb; }
    .info td { border: 1px solid #d9dce1; padding: 6px 8px; }
    .info .k { background: #f1f2f5; font-weight: bold; text-align: center; width: 13%; }
    .sum { border: 1px solid #d9dce1; margin-top: 10px; }
    .sum td { padding: 5px 10px; }
    .sum .k { color: #6b7280; }
    .sum .v { text-align: right; font-weight: bold; }
    .items { margin-top: 0; }
    .items th { background: #f1f2f5; border: 1px solid #d9dce1; padding: 7px 6px; font-size: 10px; }
    .items td { border: 1px solid #d9dce1; padding: 6px; }
    .r { text-align: right; }
    .deb { color: #b42318; font-weight: bold; text-align: right; }
    .cre { color: #15803d; font-weight: bold; text-align: right; }
    .bal { font-weight: bold; text-align: right; }
    .neg { color: #b42318; }
    .report td { background: #f8f9fb; font-style: italic; }
    .foot { position: fixed; bottom: -30px; left: 0; right: 0; border-top: 1px solid #d1d5db; padding-top: 6px; font-size: 8.5px; color: #6b7280; }
</style>
</head>
<body>
<div class="foot">
    <table><tr>
        <td>Imprimé le {{ now()->format('d/m/Y H:i') }} · Stokito</td>
        <td class="r">{{ $societe->name }} - Grand livre client {{ $clientName }}</td>
    </tr></table>
</div>

<table class="top">
    <tr>
        <td><h1>Grand livre client</h1><div class="sub">État comptable client</div></td>
        <td class="co">
            <b>{{ $societe->name }}</b>
            @if ($societe->address)<br>{{ $societe->address }}@endif
            @if ($societe->phone || $societe->phone2)<br>{{ implode(' / ', array_filter([$societe->phone, $societe->phone2])) }}@endif
        </td>
    </tr>
</table>
<div class="rule"></div>

<h2>Client</h2>
<table class="info">
    <tr><td class="k">Nom</td><td style="width:37%;">{{ $clientName }}</td><td class="k">GSM</td><td>{{ $phones }}</td></tr>
    <tr><td class="k">Adresse</td><td>{{ $client->address }}</td><td class="k">{{ $idLabel }}</td><td>{{ $client->ice }}</td></tr>
</table>

<table class="sum">
    <tr><td class="k">Filtre</td><td></td><td></td><td class="v">{{ $filtres ? implode(' · ', $filtres) : 'Toutes les opérations' }}</td></tr>
    <tr><td class="k">Nombre de documents</td><td class="v">{{ $ledger['documents'] }}</td><td class="k">Total ventes</td><td class="v">{{ $money($ledger['debits']) }}</td></tr>
    <tr><td class="k">Crédits</td><td class="v">{{ $money($ledger['credits']) }}</td><td class="k">Solde période</td><td class="v {{ $ledger['soldePeriode'] < -0.009 ? 'neg' : '' }}">{{ $money($ledger['soldePeriode']) }}</td></tr>
    <tr><td class="k">Solde comptable</td><td></td><td></td><td class="v {{ $ledger['soldeComptable'] < -0.009 ? 'neg' : '' }}">{{ $money($ledger['soldeComptable']) }}</td></tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width:12%;">Date</th>
            <th style="width:22%;">Élément</th>
            <th style="width:16%;">Référence</th>
            <th class="r" style="width:15%;">Débit</th>
            <th class="r" style="width:15%;">Crédit</th>
            <th class="r" style="width:20%;">Balance</th>
        </tr>
    </thead>
    <tbody>
        @if ($ledger['hasReport'])
            <tr class="report"><td>{{ \Illuminate\Support\Carbon::parse($from)->format('d/m/Y') }}</td><td colspan="4">Report antérieur</td><td class="bal">{{ $money($ledger['report']) }}</td></tr>
        @endif
        @forelse ($ledger['rows'] as $r)
            <tr>
                <td>{{ $r['date']?->format('d/m/Y') }}</td>
                <td>{{ $r['element'] }}</td>
                <td>{{ $r['ref'] }}</td>
                <td class="deb">{{ $r['debit'] > 0 ? $money($r['debit']) : '-' }}</td>
                <td class="cre">{{ $r['credit'] > 0 ? $money($r['credit']) : '-' }}</td>
                <td class="bal">{{ $money($r['balance']) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center; color:#9ca3af; padding:14px;">Aucune opération sur cette période.</td></tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
