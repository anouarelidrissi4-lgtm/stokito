<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Règlements Fournisseurs</h1>
        <button wire:click="create" class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">+ Ajouter un règlement</button>
    </div>


    {{-- filtres : période + plusieurs fournisseurs (2026-11-27) --}}
    @php
        $field = 'rounded-md border-gray-300 text-sm';
        $choisis = $fournisseurs->whereIn('id', $fournisseurIds);
        $periodes = \App\Livewire\ReglementsFournisseurs\Index::PERIODES;
        $filtre = $search || $from || $to || $fournisseurIds;
    @endphp
    <div class="bg-white shadow rounded-lg p-4 mb-4">
        <div class="flex flex-wrap items-end gap-3">
            <div class="grow min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Recherche</label>
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Nom du fournisseur…" class="{{ $field }} w-full">
            </div>
            <div class="w-full sm:w-auto" x-data="{ open: false, q: '' }" @click.outside="open = false" @keydown.escape="open = false">
                <label class="block text-xs font-medium text-gray-500 mb-1">Fournisseurs</label>
                <div class="relative">
                    <button type="button" @click="open = !open; $nextTick(() => open && $refs.q.focus())" class="{{ $field }} w-full sm:w-64 h-10 px-3 border bg-white flex items-center justify-between gap-2 text-left rounded-lg">
                        <span class="truncate {{ $choisis->isEmpty() ? 'text-gray-500' : 'text-gray-900 font-medium' }}">
                            @if ($choisis->isEmpty()) Tous les fournisseurs
                            @elseif ($choisis->count() === 1) {{ $choisis->first()->name }}
                            @else {{ $choisis->count() }} fournisseurs choisis
                            @endif
                        </span>
                        <x-sp-icon name="chevron" class="w-4 h-4 shrink-0 text-gray-400 rotate-90" />
                    </button>
                    <div x-show="open" x-cloak x-transition.opacity class="absolute z-30 mt-1 w-full sm:w-80 rounded-lg border border-gray-200 bg-white shadow-lg">
                        <div class="p-2 border-b border-gray-100">
                            <input x-ref="q" x-model="q" type="search" placeholder="Chercher un fournisseur…" class="{{ $field }} w-full">
                        </div>
                        <ul class="max-h-64 overflow-y-auto py-1">
                            @forelse ($fournisseurs as $f)
                                <li wire:key="rf-f-{{ $f->id }}" x-show="!q || {{ \Illuminate\Support\Js::from(mb_strtolower($f->name)) }}.includes(q.toLowerCase())">
                                    <label class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-800 hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" wire:model.live="fournisseurIds" value="{{ $f->id }}" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                                        <span class="truncate">{{ $f->name }}</span>
                                    </label>
                                </li>
                            @empty
                                <li class="px-3 py-2 text-sm text-gray-400">Aucun fournisseur.</li>
                            @endforelse
                        </ul>
                        <div class="p-2 border-t border-gray-100 flex justify-between">
                            <button type="button" wire:click="$set('fournisseurIds', [])" class="text-sm text-gray-600 underline">Tout désélectionner</button>
                            <button type="button" @click="open = false" class="h-8 px-3 rounded-md bg-gray-900 text-white text-sm font-semibold">OK</button>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Période</label>
                <select wire:model.live="periode" class="{{ $field }}">
                    @foreach ($periodes as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Du</label>
                <input wire:model.live="from" type="date" class="{{ $field }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Au</label>
                <input wire:model.live="to" type="date" class="{{ $field }}">
            </div>
            @if ($filtre)
                <button type="button" wire:click="resetFilters" class="h-10 px-2 text-sm text-gray-600 underline">Réinitialiser</button>
            @endif
        </div>
        @if ($choisis->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($choisis as $f)
                    <span wire:key="rf-chip-{{ $f->id }}" class="inline-flex items-center gap-1 h-7 pl-3 pr-1 rounded-full bg-gray-100 text-sm text-gray-800">
                        {{ $f->name }}
                        <button type="button" wire:click="removeFournisseur({{ $f->id }})" class="grid place-items-center w-5 h-5 rounded-full text-gray-500 hover:bg-gray-200 hover:text-gray-900" aria-label="Retirer {{ $f->name }}">×</button>
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    {{-- totaux --}}
    <div class="mb-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div class="bg-white shadow rounded-lg px-4 py-3">
            <p class="text-xs font-semibold text-gray-600">
                Total réglé ({{ $count }} règlement{{ $count > 1 ? 's' : '' }})
                @if ($from || $to)
                    <span class="font-normal text-gray-500">· {{ $from ? \Carbon\Carbon::parse($from)->format('d/m/Y') : '…' }} → {{ $to ? \Carbon\Carbon::parse($to)->format('d/m/Y') : '…' }}</span>
                @endif
            </p>
            <p class="mt-1 text-xl font-bold tabular-nums text-red-700">{{ \App\Support\Fmt::money($total) }}</p>
        </div>
        @if ($parFournisseur->isNotEmpty())
            <div class="bg-white shadow rounded-lg px-4 py-3 lg:col-span-2">
                <p class="text-xs font-semibold text-gray-600">Par fournisseur</p>
                <ul class="mt-1 grid sm:grid-cols-2 gap-x-6 gap-y-0.5 text-sm">
                    @foreach ($choisis as $f)
                        @php $pf = $parFournisseur->get($f->id); @endphp
                        <li class="flex justify-between gap-3"><span class="truncate text-gray-700">{{ $f->name }} <span class="text-gray-400">({{ $pf->nb ?? 0 }})</span></span><span class="tabular-nums font-semibold text-gray-900 whitespace-nowrap">{{ \App\Support\Fmt::money($pf->total ?? 0) }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commande</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mode</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($reglements as $reglement)
                    <tr wire:key="rf-{{ $reglement->id }}">
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $reglement->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $reglement->fournisseur->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $reglement->commandeFournisseur->numero ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ ucfirst($reglement->mode) }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-red-700 whitespace-nowrap">{{ \App\Support\Fmt::money($reglement->montant) }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <button wire:click="delete({{ $reglement->id }})" wire:confirm="Supprimer ce reglement ?" class="text-red-600 hover:underline">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">{{ $filtre ? 'Aucun règlement pour ces filtres.' : 'Aucun règlement.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $reglements->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">Nouveau règlement fournisseur</h2>

                <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
                <select wire:model="fournisseur_id" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    <option value="">-- Choisir --</option>
                    @foreach ($fournisseurs as $fournisseur)
                        <option value="{{ $fournisseur->id }}">{{ $fournisseur->name }}</option>
                    @endforeach
                </select>
                @error('fournisseur_id') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

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

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Mode</label>
                <select wire:model="mode" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    <option value="especes">Espèces</option>
                    <option value="cheque">Chèque</option>
                    <option value="virement">Virement</option>
                    <option value="carte">Carte</option>
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
