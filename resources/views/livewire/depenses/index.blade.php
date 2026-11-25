<div class="p-4 sm:p-6 lg:p-8 max-w-6xl">
    @php $money = fn ($v) => \App\Support\Fmt::money($v); @endphp
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dépenses</h1>
            <p class="text-sm text-slate-500">Loyer, énergie, carburant, salaires… : notez vos frais et gardez la photo du reçu.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="export" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Exporter CSV</button>
            <button type="button" wire:click="create" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                <x-sp-icon name="plus" class="w-4 h-4" /> Nouvelle dépense
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <div class="text-xs font-semibold uppercase text-slate-500">Total TTC</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ $money($totTtc) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <div class="text-xs font-semibold uppercase text-slate-500">Dont TVA</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ $money($totTva) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <div class="text-xs font-semibold uppercase text-slate-500">Par catégorie</div>
            <div class="mt-1 space-y-0.5 text-sm">
                @forelse ($parCategorie->take(4) as $c)
                    <div class="flex justify-between gap-2"><span class="truncate text-slate-600">{{ \App\Models\Depense::CATEGORIES[$c->categorie] ?? $c->categorie }}</span><span class="font-semibold text-slate-900 whitespace-nowrap">{{ $money($c->total) }}</span></div>
                @empty
                    <div class="text-slate-400">—</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap gap-2">
            <select wire:model.live="mois" class="rounded-md border-gray-300 text-sm">
                @foreach ($moisOptions as $k => $label)
                    <option value="{{ $k }}">{{ $label }}</option>
                @endforeach
                <option value="tout">Toutes les dates</option>
            </select>
            <select wire:model.live="filtreCategorie" class="rounded-md border-gray-300 text-sm">
                <option value="">Toutes les catégories</option>
                @foreach (\App\Models\Depense::CATEGORIES as $k => $label)
                    <option value="{{ $k }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="Rechercher un libellé ou une référence" class="flex-1 min-w-[12rem] rounded-md border-gray-300 text-sm">
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Libellé</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Catégorie</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Paiement</th>
                        <th class="px-4 py-3 text-end text-xs font-semibold text-slate-500 uppercase">TVA</th>
                        <th class="px-4 py-3 text-end text-xs font-semibold text-slate-500 uppercase">TTC</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($depenses as $d)
                        <tr wire:key="dep-{{ $d->id }}" class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">{{ $d->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-semibold text-slate-900">{{ $d->libelle }}</div>
                                <div class="text-xs text-slate-400">{{ collect([$d->fournisseur?->name, $d->reference])->filter()->implode(' · ') }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $d->categorieLabel() }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">{{ \App\Models\Depense::MODES[$d->mode_paiement] ?? $d->mode_paiement }}@if ($d->tresorerieCompte)<div class="text-xs text-slate-400">{{ $d->tresorerieCompte->name }}</div>@endif</td>
                            <td class="px-4 py-3 text-sm text-end text-slate-600 whitespace-nowrap">{{ $money($d->montant_tva) }}</td>
                            <td class="px-4 py-3 text-sm text-end font-semibold text-slate-900 whitespace-nowrap">{{ $money($d->montant_ttc) }}</td>
                            <td class="px-4 py-3 text-end whitespace-nowrap text-sm">
                                @if ($d->justificatif_path)
                                    <a href="{{ route('depenses.justificatif', $d->id) }}" target="_blank" class="font-semibold text-indigo-600 hover:underline me-3">Reçu</a>
                                @endif
                                <button type="button" wire:click="edit({{ $d->id }})" class="font-semibold text-indigo-600 hover:underline me-3">Modifier</button>
                                <button type="button" wire:click="delete({{ $d->id }})" wire:confirm="Supprimer cette dépense ?" class="text-red-600 hover:underline">Supprimer</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-12 text-center">
                            <div class="text-sm text-slate-500">Aucune dépense sur cette période.</div>
                            <button type="button" wire:click="create" class="mt-3 text-sm font-semibold text-indigo-600 hover:underline">Ajouter une dépense</button>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $depenses->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-start sm:items-center justify-center z-50 overflow-y-auto p-4" wire:click.self="$set('showModal', false)">
            <form wire:submit="save" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">{{ $editId ? 'Modifier la dépense' : 'Nouvelle dépense' }}</h2>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input wire:model="date" type="date" class="w-full rounded-md border-gray-300 text-sm">
                        @error('date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <select wire:model="categorie" class="w-full rounded-md border-gray-300 text-sm">
                            @foreach (\App\Models\Depense::CATEGORIES as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Libellé</label>
                        <input wire:model="libelle" type="text" placeholder="Ex. : Loyer du dépôt, gasoil camion…" class="w-full rounded-md border-gray-300 text-sm">
                        @error('libelle') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant TTC</label>
                        <input wire:model.live.debounce.400ms="montant_ttc" type="number" step="0.01" min="0" inputmode="decimal" class="w-full rounded-md border-gray-300 text-sm">
                        @error('montant_ttc') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Taux %</label>
                            <input wire:model.live.debounce.400ms="taux_tva" type="number" step="0.1" min="0" class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dont TVA</label>
                            <input wire:model="montant_tva" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                        @error('montant_tva') <p class="col-span-2 text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Paiement</label>
                        <select wire:model="mode_paiement" class="w-full rounded-md border-gray-300 text-sm">
                            @foreach (\App\Models\Depense::MODES as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payé depuis</label>
                        <select wire:model="tresorerie_compte_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">— Aucun compte —</option>
                            @foreach ($comptes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('tresorerie_compte_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur <span class="text-gray-400">(facultatif)</span></label>
                        <select wire:model="fournisseur_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">—</option>
                            @foreach ($fournisseurs as $f)
                                <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                        @error('fournisseur_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">N° de facture / reçu</label>
                        <input wire:model="reference" type="text" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Justificatif <span class="text-gray-400">(photo ou PDF, 10 Mo max)</span></label>
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="cursor-pointer inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-sm font-semibold hover:bg-gray-50">
                                {{ ($justificatifPath && ! $removeJustificatif) || $justificatif ? 'Changer le justificatif' : 'Ajouter un justificatif' }}
                                <input type="file" wire:model="justificatif" accept="image/*,application/pdf" class="sr-only">
                            </label>
                            @if ($justificatif)
                                <span class="text-xs text-gray-600">{{ $justificatif->getClientOriginalName() }}</span>
                            @elseif ($justificatifPath && ! $removeJustificatif && $editId)
                                <a href="{{ route('depenses.justificatif', $editId) }}" target="_blank" class="text-xs underline">Voir le justificatif</a>
                                <button type="button" wire:click="$set('removeJustificatif', true)" class="text-xs text-[#b42318] underline">Retirer</button>
                            @endif
                            <span wire:loading wire:target="justificatif" class="text-xs text-gray-500">Envoi…</span>
                        </div>
                        @error('justificatif') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button type="submit" wire:loading.attr="disabled" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
                </div>
            </form>
        </div>
    @endif
</div>
