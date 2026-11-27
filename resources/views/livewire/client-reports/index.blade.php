<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
    </div>

    @if ($totalSoldes !== null)
        <x-list-totals :totals="['Total dû par les clients' => \App\Support\Fmt::money($totalSoldes), 'Clients' => (string) $clients->total()]" />
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <x-list-toolbar :print-url="$printUrl" placeholder="Client, prénom, code client…" :dates="false" />
        <div class="hidden md:block overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    @if ($mode === 'plafonds')
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Plafond Crédit</th>
                    @endif
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Solde</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dossier</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($clients as $client)
                    <tr wire:key="cl-{{ $client->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ \App\Support\SalesLists::clientLabel($client) }}@if ($client->code)<span class="block text-xs font-normal text-gray-400">{{ $client->code }}</span>@endif</td>
                        @if ($mode === 'plafonds')
                            <td class="px-6 py-4 text-sm text-right text-gray-500 whitespace-nowrap">{{ \App\Support\Fmt::money($client->credit_limit) }}</td>
                        @endif
                        <td class="px-6 py-4 text-sm text-right font-semibold {{ $client->isOverCreditLimit() ? 'text-red-600' : 'text-gray-900' }}">
                            {{ \App\Support\Fmt::money($client->solde_calcule) }}
                            @if ($client->isOverCreditLimit())
                                <span class="ml-1 text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded">Depasse</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('dossier-clients.show', $client->id) }}" wire:navigate class="text-indigo-600 hover:underline">Voir dossier</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-400">Aucun client.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>

        {{-- phone (2026-11-27): one card per client --}}
        <div class="md:hidden divide-y divide-gray-100" data-sp="client-reports-phone">
            @forelse ($clients as $client)
                @php
                    $over = $client->isOverCreditLimit();
                    $limit = (float) $client->credit_limit;
                    $pct = $limit > 0 ? min(100, max(0, round($client->solde_calcule / $limit * 100))) : null;
                @endphp
                <a href="{{ route('dossier-clients.show', $client->id) }}" wire:navigate wire:key="cl-m-{{ $client->id }}" class="block p-4 active:bg-gray-50">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 truncate">{{ \App\Support\SalesLists::clientLabel($client) }}</p>
                            <p class="text-xs text-gray-400">{{ implode(' · ', array_filter([$client->code, $client->phone])) }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-base font-bold tabular-nums {{ $over ? 'text-red-600' : 'text-gray-900' }}">{{ \App\Support\Fmt::money($client->solde_calcule) }}</p>
                            @if ($over)<span class="text-[11px] font-semibold bg-red-100 text-red-700 px-1.5 py-0.5 rounded">Dépassé</span>@endif
                        </div>
                    </div>
                    @if ($pct !== null)
                        <div class="mt-3">
                            <div class="flex justify-between text-xs text-gray-500"><span>Plafond {{ \App\Support\Fmt::money($limit) }}</span><span>{{ $pct }} %</span></div>
                            <div class="mt-1 h-1.5 rounded-full bg-gray-200"><div class="h-1.5 rounded-full {{ $over ? 'bg-red-500' : 'bg-[#ffcc00]' }}" style="width: {{ $pct }}%"></div></div>
                        </div>
                    @endif
                    <p class="mt-2 text-xs font-semibold text-zinc-700">Voir dossier →</p>
                </a>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-400">Aucun client.</div>
            @endforelse
        </div>
        <div class="p-4">{{ $clients->links() }}</div>
    </div>
</div>
