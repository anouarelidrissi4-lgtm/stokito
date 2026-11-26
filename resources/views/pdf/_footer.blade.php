{{-- legal footer, position: fixed -> printed first in <body> so dompdf repeats it on every page --}}
@php
    // own copy of what the footer needs, so it can be printed once for several documents (2026-11-27)
    $withHeader = $showHeader ?? true;
    $legalParts = \App\Support\Countries::legalParts($societe);
    $phones = implode(' / ', array_filter([$societe->phone, $societe->phone2]));
    $cityExtra = ($societe->city && ! str_contains(mb_strtolower((string) $societe->address), mb_strtolower($societe->city))) ? $societe->city : null;
@endphp
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
