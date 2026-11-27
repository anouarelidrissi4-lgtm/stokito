{{-- Grand livre client (2026-11-27), like the user's model: client, summary, ledger with running balance --}}
@php
    $money = fn ($v) => \App\Support\Fmt::money($v);
    $neg = fn ($v) => $v < -0.009 ? 'text-red-600' : 'text-gray-900';
    $idLabel = \App\Support\Countries::for(\App\Models\Societe::current())['client'];
    $phones = implode(' / ', array_filter([$client->phone, $client->phone2 ?? null]));
    $field = 'rounded-md border-gray-300 text-sm';
@endphp
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-6xl">
    <a href="{{ route('dossier-clients.index') }}" wire:navigate class="text-sm text-gray-500 hover:text-gray-900">← Dossier clients</a>
    <div class="mt-1 mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Grand livre client</h1>
            <p class="text-sm text-gray-500">État comptable de {{ \App\Support\SalesLists::clientLabel($client) }}</p>
        </div>
        <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 h-10 px-4 rounded-lg bg-[#d92d20] text-white text-sm font-semibold hover:bg-[#b42318]">
            <x-sp-icon name="printer" class="w-4 h-4" /> Imprimer (PDF)
        </a>
    </div>

    {{-- client --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-2.5 border-b border-gray-200 bg-gray-50 text-sm font-semibold text-gray-900">Client</div>
        <dl class="grid sm:grid-cols-2 text-sm">
            <div class="flex gap-3 px-4 py-2.5 border-b sm:border-r border-gray-100"><dt class="w-20 shrink-0 font-semibold text-gray-500">Nom</dt><dd class="text-gray-900">{{ trim($client->name.' '.($client->prenom ?? '')) }}</dd></div>
            <div class="flex gap-3 px-4 py-2.5 border-b border-gray-100"><dt class="w-20 shrink-0 font-semibold text-gray-500">GSM</dt><dd class="text-gray-900 break-all">{{ $phones ?: '—' }}</dd></div>
            <div class="flex gap-3 px-4 py-2.5 border-b sm:border-b-0 sm:border-r border-gray-100"><dt class="w-20 shrink-0 font-semibold text-gray-500">Adresse</dt><dd class="text-gray-900">{{ $client->address ?: '—' }}</dd></div>
            <div class="flex gap-3 px-4 py-2.5"><dt class="w-20 shrink-0 font-semibold text-gray-500">{{ $idLabel }}</dt><dd class="text-gray-900">{{ $client->ice ?: '—' }}</dd></div>
        </dl>
    </div>

    {{-- filters --}}
    <div class="mt-4 bg-white shadow rounded-lg p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Du</label>
            <input wire:model.live="from" type="date" class="{{ $field }}">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Au</label>
            <input wire:model.live="to" type="date" class="{{ $field }}">
        </div>
        <label class="inline-flex items-center gap-2 h-10 text-sm text-gray-700">
            <input wire:model.live="hidePaid" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-zinc-900 focus:ring-zinc-900"> Masquer les BL / factures payés
        </label>
        @if ($from || $to || $hidePaid)
            <button type="button" wire:click="resetFilters" class="h-10 px-3 text-sm text-gray-600 underline">Réinitialiser</button>
        @endif
    </div>

    {{-- summary --}}
    <div class="mt-4 grid grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach ([
            ['Documents (BL / factures)', (string) $ledger['documents'], 'text-gray-900'],
            ['Total ventes', $money($ledger['debits']), 'text-gray-900'],
            ['Crédits (règlements, avoirs)', $money($ledger['credits']), 'text-green-700'],
            ['Solde période', $money($ledger['soldePeriode']), $neg($ledger['soldePeriode'])],
            ['Solde comptable', $money($ledger['soldeComptable']), $neg($ledger['soldeComptable'])],
        ] as [$label, $value, $color])
            <div class="bg-white shadow rounded-lg px-4 py-3">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="mt-1 text-lg font-bold tabular-nums {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    {{-- ledger --}}
    <div class="mt-4 bg-white shadow rounded-lg overflow-hidden">
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">Date</th>
                        <th class="px-4 py-2.5 text-start font-semibold">Élément</th>
                        <th class="px-4 py-2.5 text-start font-semibold">Référence</th>
                        <th class="px-4 py-2.5 text-end font-semibold">Débit</th>
                        <th class="px-4 py-2.5 text-end font-semibold">Crédit</th>
                        <th class="px-4 py-2.5 text-end font-semibold">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @if ($ledger['hasReport'])
                        <tr class="bg-gray-50/60">
                            <td class="px-4 py-2.5 text-gray-500">{{ \Illuminate\Support\Carbon::parse($from)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2.5 font-medium text-gray-700" colspan="4">Report antérieur</td>
                            <td class="px-4 py-2.5 text-end font-bold tabular-nums {{ $neg($ledger['report']) }}">{{ $money($ledger['report']) }}</td>
                        </tr>
                    @endif
                    @forelse ($ledger['rows'] as $r)
                        <tr wire:key="gl-{{ $r['kind'] }}-{{ $r['id'] }}">
                            <td class="px-4 py-2.5 whitespace-nowrap text-gray-700">{{ $r['date']?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2.5 text-gray-900">{{ $r['element'] }}@if (! empty($r['detail']))<span class="block text-xs text-gray-400">{{ $r['detail'] }}</span>@endif</td>
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                @if ($r['kind'] === 'bl')<a href="{{ route('bon-livraisons.edit', $r['id']) }}" wire:navigate class="text-gray-900 hover:underline">{{ $r['ref'] }}</a>
                                @elseif ($r['kind'] === 'facture')<a href="{{ route('factures.edit', $r['id']) }}" wire:navigate class="text-gray-900 hover:underline">{{ $r['ref'] }}</a>
                                @else<span class="text-gray-700">{{ $r['ref'] }}</span>@endif
                            </td>
                            <td class="px-4 py-2.5 text-end tabular-nums font-semibold text-red-600">{{ $r['debit'] > 0 ? $money($r['debit']) : '-' }}</td>
                            <td class="px-4 py-2.5 text-end tabular-nums font-semibold text-green-700">{{ $r['credit'] > 0 ? $money($r['credit']) : '-' }}</td>
                            <td class="px-4 py-2.5 text-end tabular-nums font-bold text-gray-900">{{ $money($r['balance']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Aucune opération sur cette période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- phone --}}
        <div class="md:hidden divide-y divide-gray-100" data-sp="ledger-phone">
            @if ($ledger['hasReport'])
                <div class="px-4 py-3 flex justify-between text-sm bg-gray-50"><span class="font-medium text-gray-700">Report antérieur</span><span class="font-bold tabular-nums {{ $neg($ledger['report']) }}">{{ $money($ledger['report']) }}</span></div>
            @endif
            @forelse ($ledger['rows'] as $r)
                <div class="px-4 py-3" wire:key="glm-{{ $r['kind'] }}-{{ $r['id'] }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">{{ $r['element'] }}</p>
                            <p class="text-xs text-gray-500">{{ $r['date']?->format('d/m/Y') }} · {{ $r['ref'] }}</p>
                        </div>
                        <p class="text-sm font-bold tabular-nums {{ $r['debit'] > 0 ? 'text-red-600' : 'text-green-700' }}">{{ $r['debit'] > 0 ? '+ '.$money($r['debit']) : '− '.$money($r['credit']) }}</p>
                    </div>
                    <p class="mt-1 text-right text-xs text-gray-500">Balance <span class="font-semibold text-gray-900 tabular-nums">{{ $money($r['balance']) }}</span></p>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-gray-400">Aucune opération sur cette période.</div>
            @endforelse
        </div>
    </div>
</div>
