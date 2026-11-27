{{-- Dossier clients (2026-11-27): choose a customer to open the grand livre --}}
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dossier clients</h1>
        <p class="mt-1 text-sm text-gray-500">Choisissez un client pour voir son grand livre : bons de livraison, factures, règlements et solde.</p>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Nom, prénom, code client ou téléphone…" class="w-full md:max-w-md rounded-md border-gray-300 text-sm">
        </div>
        <div class="divide-y divide-gray-100">
            @forelse ($clients as $client)
                <a href="{{ route('dossier-clients.show', $client->id) }}" wire:navigate wire:key="dc-{{ $client->id }}" class="flex items-center justify-between gap-4 px-4 sm:px-6 py-3.5 hover:bg-gray-50">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate">{{ \App\Support\SalesLists::clientLabel($client) }}</p>
                        <p class="text-xs text-gray-400">{{ implode(' · ', array_filter([$client->code, $client->phone])) }}</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="text-right">
                            <p class="text-[11px] text-gray-400">Solde</p>
                            <p class="text-sm font-bold tabular-nums {{ $client->solde_calcule > 0.009 ? 'text-red-600' : 'text-gray-900' }}">{{ \App\Support\Fmt::money($client->solde_calcule) }}</p>
                        </div>
                        <span class="hidden sm:inline-flex items-center h-8 px-3 rounded-md bg-zinc-900 text-white text-xs font-semibold">Ouvrir le dossier</span>
                        <x-sp-icon name="chevron" class="w-4 h-4 text-gray-400 -rotate-90 sm:hidden" />
                    </div>
                </a>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-400">Aucun client.</div>
            @endforelse
        </div>
        <div class="p-4">{{ $clients->links() }}</div>
    </div>
</div>
