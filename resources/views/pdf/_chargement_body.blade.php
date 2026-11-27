{{-- one loading slip: same look as the other documents (2026-11-27) --}}
@php
    $bl = $bc->bonLivraison;
    $bls = $bl ? collect([$bl]) : $bc->bonLivraisons;      // manual slip: several delivery notes
    $client = $bls->count() === 1 ? $bls->first()->client : null;
    $clients = $bls->map(fn ($b) => $b->client ? trim(($b->client->name ?? '').' '.($b->client->prenom ?? '')) : null)->filter()->unique()->implode(', ');
    $phones = implode(' / ', array_filter([$societe->phone, $societe->phone2]));
    $statuts = \App\Models\BonChargementLigne::STATUTS;
    $qty = fn ($v) => $v === null ? '' : \App\Support\Fmt::qty($v);
@endphp
<div class="topbar"></div>
<table class="head">
    <tr>
        <td style="width:38%;" class="co">
            <p class="co-name">{{ $societe->name }}</p>
            @if ($societe->activite)<p class="co-act">{{ $societe->activite }}</p>@endif
            @if ($societe->address)<p>{{ $societe->address }}</p>@endif
            @if ($phones)<p>Tél. : {{ $phones }}</p>@endif
        </td>
        <td style="width:24%;" class="logo">
            @if ($societe->logoDataUri())<img src="{{ $societe->logoDataUri() }}">@endif
        </td>
        <td style="width:38%;">
            <div class="place">{{ $societe->city ? $societe->city.', le' : 'Le' }} <strong>{{ $bc->date?->format('d/m/Y') }}</strong></div>
            <table class="meta">
                <tr><td class="k">Chargement N°</td><td class="v">{{ $bc->numero }}</td></tr>
                @if ($bls->isNotEmpty())<tr><td class="k">{{ $bls->count() > 1 ? 'Bons de livraison' : 'Bon de livraison' }}</td><td class="v">{{ $bls->pluck('numero')->implode(', ') }}</td></tr>@endif
                @if ($clients)<tr><td class="k">{{ $bls->count() > 1 ? 'Clients' : 'Client' }}</td><td class="v">{{ $clients }}</td></tr>@endif
                @if ($client?->address)<tr><td class="k">Adresse</td><td>{{ $client->address }}</td></tr>@endif
                @if ($bc->chauffeur)<tr><td class="k">Chauffeur</td><td class="v">{{ $bc->chauffeur }}</td></tr>@endif
                @if ($bc->vehicule)<tr><td class="k">Immatriculation</td><td class="v">{{ $bc->vehicule }}</td></tr>@endif
                @if ($bc->commercial)<tr><td class="k">Commercial</td><td>{{ $bc->commercial }}</td></tr>@endif
            </table>
        </td>
    </tr>
</table>

<div class="title"><span>Bon de chargement</span></div>

<table class="items">
    <thead>
        <tr>
            <th style="width:5%;">N°</th>
            <th>Produit</th>
            <th style="width:9%;">Unité</th>
            <th style="width:14%;">Qté commandée</th>
            <th style="width:14%;">Qté chargée</th>
            <th style="width:13%;">Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($bc->lignes as $l)
            <tr>
                <td class="c">{{ $loop->iteration }}</td>
                <td>{{ $l->designation }}</td>
                <td class="c">{{ $l->unite ?: '-' }}</td>
                <td class="c">{{ $qty($l->quantite_commandee) }}</td>
                <td class="c">{{ $qty($l->quantite_chargee) }}</td>
                <td class="c">{{ $statuts[$l->statut()] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@if ($bc->notes)<div class="small"><strong>Notes :</strong> {{ $bc->notes }}</div>@endif

<table style="width:100%; margin-top:26px; page-break-inside:avoid;">
    <tr>
        <td></td>
        <td class="sign">Magasinier<br>nom et signature</td>
        <td style="width:18px;"></td>
        <td class="sign">Chauffeur<br>nom et signature</td>
    </tr>
</table>
