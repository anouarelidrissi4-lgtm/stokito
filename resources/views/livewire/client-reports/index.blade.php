<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher un client..." class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div class="overflow-x-auto">
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
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $client->name }}</td>
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
        <div class="p-4">{{ $clients->links() }}</div>
    </div>
</div>
