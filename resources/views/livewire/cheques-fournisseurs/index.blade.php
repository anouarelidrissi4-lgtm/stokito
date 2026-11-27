{{-- Chèques / effets fournisseurs (2026-11-27), like the user's model: due-date filter, totals, grouped by supplier, PDF --}}
@php
    $money = fn ($v) => \App\Support\Fmt::money($v);
    $pay = \App\Models\ChequeEffetFournisseur::PAIEMENT;
    $badge = ['en_attente' => 'bg-amber-100 text-amber-800', 'encaisse' => 'bg-green-100 text-green-700', 'rejete' => 'bg-red-100 text-red-700'];
    $field = 'rounded-md border-gray-300 text-sm';
    $statuts = \App\Livewire\ChequesFournisseurs\Index::STATUTS;
    $bonLabel = fn ($c) => ($cf = $c->reglementFournisseur?->commandeFournisseur) ? $cf->numero.' : '.$money($cf->total) : '—';
@endphp
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Chèques / Effets fournisseurs</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 h-10 px-4 rounded-lg bg-[#d92d20] text-white text-sm font-semibold hover:bg-[#b42318]"><x-sp-icon name="printer" class="w-4 h-4" /> Imprimer (PDF)</a>
            <button wire:click="create" class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">+ Ajouter un chèque / effet</button>
        </div>
    </div>

    {{-- filters --}}
    <div class="bg-white shadow rounded-lg p-4 flex flex-wrap items-end gap-3">
        <div class="grow min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Recherche</label>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Fournisseur, n° de chèque, banque…" class="{{ $field }} w-full">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Échéance du</label>
            <input wire:model.live="from" type="date" class="{{ $field }}">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">au</label>
            <input wire:model.live="to" type="date" class="{{ $field }}">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Paiement</label>
            <select wire:model.live="statut" class="{{ $field }}">
                @foreach ($statuts as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
            </select>
        </div>
        @if ($search || $from || $to || $statut)
            <button type="button" wire:click="resetFilters" class="h-10 px-2 text-sm text-gray-600 underline">Réinitialiser</button>
        @endif
    </div>

    {{-- totals --}}
    <div class="mt-4 grid sm:grid-cols-3 gap-3">
        @foreach ([
            ['Chèques / effets filtrés', $stats['all'], 'text-gray-900'],
            ['À échéance payés', $stats['paid'], 'text-green-700'],
            ['Échéance passée non payés', $stats['late'], 'text-red-600'],
        ] as [$label, $st, $color])
            <div class="bg-white shadow rounded-lg px-4 py-3">
                <p class="text-xs font-semibold text-gray-600">{{ $label }} ({{ $st[0] }})</p>
                <p class="mt-1 text-xl font-bold tabular-nums {{ $color }}">{{ $money($st[1]) }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 bg-white shadow rounded-lg overflow-hidden">
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-3 py-2.5 text-start font-semibold">Dépôt</th>
                        <th class="px-3 py-2.5 text-start font-semibold">Échéance</th>
                        <th class="px-3 py-2.5 text-start font-semibold">Fournisseur</th>
                        <th class="px-3 py-2.5 text-start font-semibold">Bon de commande</th>
                        <th class="px-3 py-2.5 text-start font-semibold">Compte</th>
                        <th class="px-3 py-2.5 text-start font-semibold">Type</th>
                        <th class="px-3 py-2.5 text-start font-semibold">N° CH/EFF</th>
                        <th class="px-3 py-2.5 text-end font-semibold">Montant</th>
                        <th class="px-3 py-2.5 text-start font-semibold">Paiement</th>
                        <th class="px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($groups as $name => $list)
                        <tr class="bg-sky-50">
                            <td colspan="7" class="px-3 py-2 font-bold text-gray-900">{{ $name }}</td>
                            <td class="px-3 py-2 text-end font-bold tabular-nums text-green-700 whitespace-nowrap">{{ $money($list->sum('montant')) }}</td>
                            <td colspan="2" class="px-3 py-2 font-semibold text-gray-700 whitespace-nowrap">{{ $list->count() }} chèque(s) / effet(s)</td>
                        </tr>
                        @foreach ($list as $c)
                            <tr wire:key="ch-{{ $c->id }}">
                                <td class="px-3 py-2.5 whitespace-nowrap text-gray-700">{{ $c->date_emission?->format('d/m/Y') }}</td>
                                <td class="px-3 py-2.5 whitespace-nowrap {{ $c->isEnRetard() ? 'text-red-600 font-semibold' : 'text-gray-700' }}">{{ $c->date_echeance?->format('d/m/Y') }}</td>
                                <td class="px-3 py-2.5 text-gray-900">{{ $c->fournisseur->name ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-gray-700 whitespace-nowrap">{{ $bonLabel($c) }}</td>
                                <td class="px-3 py-2.5 text-gray-700">{{ $c->compteLabel() ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-gray-700">{{ $c->typeLabel() }}</td>
                                <td class="px-3 py-2.5 font-mono text-gray-900">{{ $c->numero_cheque }}</td>
                                <td class="px-3 py-2.5 text-end tabular-nums font-semibold whitespace-nowrap">{{ $money($c->montant) }}</td>
                                <td class="px-3 py-2.5"><span class="px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap {{ $badge[$c->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $pay[$c->status] ?? $c->status }}</span></td>
                                <td class="px-3 py-2.5 text-end whitespace-nowrap">
                                    @if ($c->status === 'en_attente')
                                        <button wire:click="markStatus({{ $c->id }}, 'encaisse')" class="h-7 px-2 rounded-md bg-[#16a34a] text-white text-xs font-semibold hover:bg-[#15803d]">Payé</button>
                                        <button wire:click="markStatus({{ $c->id }}, 'rejete')" class="h-7 px-2 rounded-md border border-gray-300 text-xs font-semibold text-gray-700 hover:border-zinc-900">Rejeté</button>
                                    @else
                                        <button wire:click="markStatus({{ $c->id }}, 'en_attente')" class="h-7 px-2 rounded-md border border-gray-300 text-xs font-semibold text-gray-700 hover:border-zinc-900">Annuler</button>
                                    @endif
                                    <button wire:click="delete({{ $c->id }})" wire:confirm="Supprimer ce chèque / effet ?" class="p-1 rounded text-red-600 hover:text-red-700 align-middle" title="Supprimer"><x-sp-icon name="trash" class="w-4 h-4" /></button>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="10" class="px-4 py-8 text-center text-sm text-gray-400">Aucun chèque / effet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- phone / tablet --}}
        <div class="lg:hidden" data-sp="cheques-phone">
            @forelse ($groups as $name => $list)
                <div class="px-4 py-2 bg-sky-50 flex justify-between text-sm"><span class="font-bold text-gray-900">{{ $name }}</span><span class="font-bold tabular-nums text-green-700">{{ $money($list->sum('montant')) }} <span class="font-normal text-gray-500">· {{ $list->count() }}</span></span></div>
                @foreach ($list as $c)
                    <div class="px-4 py-3 border-b border-gray-100" wire:key="chm-{{ $c->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $c->typeLabel() }} n° {{ $c->numero_cheque }}</p>
                                <p class="text-xs text-gray-500">Dépôt {{ $c->date_emission?->format('d/m/Y') }} · <span class="{{ $c->isEnRetard() ? 'text-red-600 font-semibold' : '' }}">Échéance {{ $c->date_echeance?->format('d/m/Y') }}</span></p>
                                <p class="text-xs text-gray-500">{{ implode(' · ', array_filter([$c->compteLabel(), $c->reglementFournisseur?->commandeFournisseur?->numero])) }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold tabular-nums">{{ $money($c->montant) }}</p>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badge[$c->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $pay[$c->status] ?? $c->status }}</span>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-end gap-2">
                            @if ($c->status === 'en_attente')
                                <button wire:click="markStatus({{ $c->id }}, 'encaisse')" class="h-8 px-3 rounded-md bg-[#16a34a] text-white text-xs font-semibold">Payé</button>
                                <button wire:click="markStatus({{ $c->id }}, 'rejete')" class="h-8 px-3 rounded-md border border-gray-300 text-xs font-semibold text-gray-700">Rejeté</button>
                            @else
                                <button wire:click="markStatus({{ $c->id }}, 'en_attente')" class="h-8 px-3 rounded-md border border-gray-300 text-xs font-semibold text-gray-700">Annuler</button>
                            @endif
                            <button wire:click="delete({{ $c->id }})" wire:confirm="Supprimer ce chèque / effet ?" class="h-8 px-2 rounded-md text-red-600" title="Supprimer"><x-sp-icon name="trash" class="w-4 h-4" /></button>
                        </div>
                    </div>
                @endforeach
            @empty
                <div class="px-4 py-8 text-center text-sm text-gray-400">Aucun chèque / effet.</div>
            @endforelse
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-t-2xl sm:rounded-xl shadow-xl p-5 w-full max-w-md max-h-[92vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">Nouveau chèque / effet fournisseur</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Fournisseur</label>
                        <select wire:model="fournisseur_id" class="w-full {{ $field }}">
                            <option value="">— Choisir —</option>
                            @foreach ($fournisseurs as $f)<option value="{{ $f->id }}">{{ $f->name }}</option>@endforeach
                        </select>
                        @error('fournisseur_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                        <select wire:model="type" class="w-full {{ $field }}"><option value="cheque">Chèque</option><option value="effet">Effet</option></select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">N° CH / EFF</label>
                        <input wire:model="numero_cheque" type="text" class="w-full {{ $field }}">
                        @error('numero_cheque') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Compte / banque</label>
                        <input wire:model="banque" type="text" placeholder="Ex. BENDIB ABDELAZIZ, Attijariwafa bank…" class="w-full {{ $field }}">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Montant</label>
                        <input wire:model="montant" type="number" step="0.01" min="0" class="w-full {{ $field }}">
                        @error('montant') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Dépôt</label>
                        <input wire:model="date_emission" type="date" class="w-full {{ $field }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Échéance</label>
                        <input wire:model="date_echeance" type="date" class="w-full {{ $field }}">
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button type="button" wire:click="save" class="h-10 px-5 rounded-lg bg-[#16a34a] text-white text-sm font-semibold hover:bg-[#15803d]">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif
</div>
