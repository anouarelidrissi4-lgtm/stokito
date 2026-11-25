<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Règlements Clients</h1>
        <button wire:click="create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">+ Ajouter un règlement</button>
    </div>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <x-list-totals :totals="$totals" />

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <x-list-toolbar :print-url="$printUrl" placeholder="Client, code client, n° de facture…" />
        <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Facture</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mode</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($reglements as $reglement)
                    <tr wire:key="rc-{{ $reglement->id }}">
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $reglement->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ \App\Support\SalesLists::clientLabel($reglement->client) }}@if ($reglement->client?->code)<span class="block text-xs text-gray-400">{{ $reglement->client->code }}</span>@endif</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $reglement->facture->numero ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $reglement->modeLabel() }}@if ($reglement->numero_cheque)<span class="block text-xs text-gray-400">N° {{ $reglement->numero_cheque }}</span>@endif</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-green-700 whitespace-nowrap">{{ \App\Support\Fmt::money($reglement->montant) }}</td>
                        <td class="px-6 py-4 text-right text-sm">@include('livewire.reglements-clients._actions')</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">Aucun règlement.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- phone: one card per document (2026-11-26) --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse ($reglements as $reglement)
                <div class="p-4" wire:key="rc-m-{{ $reglement->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0"><div class="font-semibold text-gray-900">{{ \App\Support\SalesLists::clientLabel($reglement->client) }}@if ($reglement->client?->code)<span class="block text-xs text-gray-400">{{ $reglement->client->code }}</span>@endif</div></div>
                        <div class="text-end shrink-0"><div class="font-bold text-green-700 tabular-nums">{{ \App\Support\Fmt::money($reglement->montant) }}</div></div>
                    </div>
                    <div class="mt-1 text-sm text-gray-600"><span class="text-xs text-gray-500">{{ $reglement->date->format('d/m/Y') }} · {{ $reglement->modeLabel() }}@if ($reglement->facture) · {{ $reglement->facture->numero }}@endif</span></div>
                    <div class="mt-3 flex items-center justify-end gap-2">@include('livewire.reglements-clients._actions')</div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-400">Aucun résultat.</div>
            @endforelse
        </div>

        <div class="p-4">{{ $reglements->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">{{ $editId ? 'Modifier le règlement' : 'Nouveau règlement client' }}</h2>

                <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                <select wire:model="client_id" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    <option value="">-- Choisir --</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ \App\Support\SalesLists::clientLabel($client) }}{{ $client->code ? ' ('.$client->code.')' : '' }}</option>
                    @endforeach
                </select>
                @error('client_id') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Compte trésorerie</label>
                <select wire:model="tresorerie_compte_id" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    <option value="">-- Aucun --</option>
                    @foreach ($comptes as $compte)
                        <option value="{{ $compte->id }}">{{ $compte->name }}</option>
                    @endforeach
                </select>

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Montant</label>
                <input wire:model="montant" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @error('montant') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Date</label>
                <input wire:model="date" type="date" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @error('date') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Mode</label>
                <select wire:model="mode" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    <option value="especes">Espèces</option>
                    <option value="cheque">Chèque</option>
                    <option value="virement">Virement</option>
                    <option value="carte">Carte</option>
                    <option value="effet">Effet</option>
                </select>

                @if ($mode === 'cheque')
                    <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Numéro chèque</label>
                    <input wire:model="numero_cheque" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @endif

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif
</div>
