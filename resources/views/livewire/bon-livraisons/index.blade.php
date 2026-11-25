<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Bons de Livraison</h1>
        <a href="{{ route('bon-livraisons.create') }}" wire:navigate class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">+ Ajouter un bon de livraison</a>
    </div>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <x-list-totals :totals="$totals" />

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <x-list-toolbar :print-url="$printUrl" placeholder="N° de BL, client, code client…" />
        <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($bonLivraisons as $bl)
                    <tr wire:key="bl-{{ $bl->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $bl->numero }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ \App\Support\SalesLists::clientLabel($bl->client) }}@if ($bl->client?->code)<span class="block text-xs text-gray-400">{{ $bl->client->code }}</span>@endif</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $bl->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900 whitespace-nowrap">{{ \App\Support\Fmt::money($bl->total) }}</td>
                        <td class="px-6 py-4 text-sm"><span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-gray-100 text-gray-600' => $bl->status === 'draft',
                                'bg-green-100 text-green-700' => $bl->status === 'delivered',
                            ])>{{ \App\Support\Fmt::status($bl->status) }}</span></td>
                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap"><div class="inline-flex items-center gap-2"><a href="{{ route('bon-livraisons.pdf', $bl->id) }}" target="_blank" title="Ouvrir le PDF" class="inline-flex items-center gap-1 bg-[#d92d20] text-white px-2 py-1 rounded-md text-xs font-bold hover:bg-[#b42318]"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h1.5a1.5 1.5 0 0 1 0 3H8v-3zm0 3v2M13 13v5h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2h-1z" stroke-width="1.4"/></svg>PDF</a>@include('livewire.bon-livraisons._actions')</div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">Aucun bon de livraison.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- phone: one card per document (2026-11-26) --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse ($bonLivraisons as $bl)
                <div class="p-4" wire:key="bl-m-{{ $bl->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0"><div class="font-semibold text-gray-900">{{ $bl->numero }}</div></div>
                        <div class="text-end shrink-0"><div class="font-bold text-gray-900 tabular-nums">{{ \App\Support\Fmt::money($bl->total) }}</div><div class="mt-1"><span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-gray-100 text-gray-600' => $bl->status === 'draft',
                                'bg-green-100 text-green-700' => $bl->status === 'delivered',
                            ])>{{ \App\Support\Fmt::status($bl->status) }}</span></div></div>
                    </div>
                    <div class="mt-1 text-sm text-gray-600">{{ \App\Support\SalesLists::clientLabel($bl->client) }}@if ($bl->client?->code)<span class="block text-xs text-gray-400">{{ $bl->client->code }}</span>@endif<span class="text-xs text-gray-400">{{ $bl->date->format('d/m/Y') }}</span></div>
                    <div class="mt-3 flex items-center justify-end gap-2"><a href="{{ route('bon-livraisons.pdf', $bl->id) }}" target="_blank" title="Ouvrir le PDF" class="inline-flex items-center gap-1 bg-[#d92d20] text-white px-2 py-1 rounded-md text-xs font-bold hover:bg-[#b42318]"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h1.5a1.5 1.5 0 0 1 0 3H8v-3zm0 3v2M13 13v5h1a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2h-1z" stroke-width="1.4"/></svg>PDF</a>@include('livewire.bon-livraisons._actions')</div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-400">Aucun résultat.</div>
            @endforelse
        </div>

        <div class="p-4">{{ $bonLivraisons->links() }}</div>
    </div>
</div>
