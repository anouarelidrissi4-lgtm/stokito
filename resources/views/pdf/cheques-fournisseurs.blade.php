{{-- Chèques / effets fournisseurs, printable (2026-11-27) — same layout as the user's model, A4 landscape --}}
@php
    $money = fn ($v) => \App\Support\Fmt::money($v);
    $pay = \App\Models\ChequeEffetFournisseur::PAIEMENT;
    $fmt = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d/m/Y') : null;
    $periode = $from || $to ? 'Échéance '.($from ? 'du '.$fmt($from).' ' : '').($to ? 'au '.$fmt($to) : '') : 'Toutes les échéances';
    $statutLabel = \App\Livewire\ChequesFournisseurs\Index::STATUTS[$statut] ?? 'Tous';
@endphp
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Chèques / Effets fournisseurs</title>
<style>
    @page { margin: 26px 30px 40px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1f2328; }
    table { border-collapse: collapse; width: 100%; }
    h1 { font-size: 18px; margin: 0 0 4px; }
    .meta { color: #6b7280; font-size: 9px; line-height: 1.5; }
    .co { text-align: right; font-size: 9.5px; line-height: 1.45; vertical-align: top; }
    .stats { margin: 12px 0 14px; border: 1px solid #d9dce1; }
    .stats td { padding: 6px 9px; width: 33.3%; border-right: 1px solid #d9dce1; }
    .stats b { display: block; font-size: 9.5px; }
    .items th { background: #f1f2f5; border: 1px solid #d9dce1; padding: 7px 5px; font-size: 9.5px; }
    .items td { border: 1px solid #d9dce1; padding: 5px; }
    .grp td { background: #d6ebf5; font-weight: bold; font-size: 11px; padding: 6px 5px; }
    .grp .sum { color: #0f7a4f; text-align: right; }
    .r { text-align: right; }
    .late { color: #b42318; font-weight: bold; }
    .foot { position: fixed; bottom: -26px; left: 0; right: 0; font-size: 8px; color: #6b7280; border-top: 1px solid #d1d5db; padding-top: 5px; }
</style>
</head>
<body>
<div class="foot"><table><tr><td>Imprimé le {{ now()->format('d/m/Y H:i') }} · Stokito</td><td class="r">{{ $societe->name }} - Chèques / effets fournisseurs</td></tr></table></div>

<table>
    <tr>
        <td>
            <h1>Chèques / Effets fournisseurs</h1>
            <div class="meta">Imprimé le {{ now()->format('d/m/Y H:i') }}<br>{{ $periode }} · Paiement : {{ $statutLabel }}@if ($search) · Recherche : « {{ $search }} »@endif</div>
        </td>
        <td class="co"><b>{{ $societe->name }}</b>@if ($societe->address)<br>{{ $societe->address }}@endif @if ($societe->phone)<br>{{ $societe->phone }}@endif</td>
    </tr>
</table>

<table class="stats">
    <tr>
        <td><b>Chèques / effets filtrés ({{ $stats['all'][0] }})</b>{{ $money($stats['all'][1]) }}</td>
        <td><b>À échéance payés ({{ $stats['paid'][0] }})</b>{{ $money($stats['paid'][1]) }}</td>
        <td style="border-right:0;"><b>Échéance passée non payés ({{ $stats['late'][0] }})</b>{{ $money($stats['late'][1]) }}</td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width:8%;">Dépôt</th>
            <th style="width:8%;">Échéance</th>
            <th style="width:12%;">Fournisseur</th>
            <th style="width:15%;">Bon de commande</th>
            <th style="width:15%;">Compte</th>
            <th style="width:6%;">Type</th>
            <th style="width:9%;">N° CH/EFF</th>
            <th class="r" style="width:10%;">Montant</th>
            <th style="width:17%;">Paiement</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($groups as $name => $list)
            <tr class="grp">
                <td colspan="7">{{ $name }}</td>
                <td class="sum">{{ $money($list->sum('montant')) }}</td>
                <td>{{ $list->count() }} chèque(s) / effet(s)</td>
            </tr>
            @foreach ($list as $c)
                @php $cf = $c->reglementFournisseur?->commandeFournisseur; @endphp
                <tr>
                    <td>{{ $c->date_emission?->format('d/m/Y') }}</td>
                    <td class="{{ $c->isEnRetard() ? 'late' : '' }}">{{ $c->date_echeance?->format('d/m/Y') }}</td>
                    <td>{{ $c->fournisseur->name ?? '—' }}</td>
                    <td>{{ $cf ? $cf->numero.' : '.$money($cf->total) : '—' }}</td>
                    <td>{{ $c->compteLabel() ?? '—' }}</td>
                    <td>{{ $c->typeLabel() }}</td>
                    <td>{{ $c->numero_cheque }}</td>
                    <td class="r">{{ $money($c->montant) }}</td>
                    <td>{{ $pay[$c->status] ?? $c->status }}</td>
                </tr>
            @endforeach
        @empty
            <tr><td colspan="9" style="text-align:center; color:#9ca3af; padding:14px;">Aucun chèque / effet.</td></tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
