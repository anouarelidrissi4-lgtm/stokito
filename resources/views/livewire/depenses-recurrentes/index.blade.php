<div class="p-4 sm:p-6 lg:p-8 max-w-6xl">
    @php $money = fn ($v) => \App\Support\Fmt::money($v); @endphp
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900">Dépenses récurrentes</h1>
            <p class="text-sm text-zinc-500">Loyer, Internet, salaires… : saisissez-les une fois, Stokito crée la dépense à chaque échéance.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('depenses.index') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">Toutes les dépenses</a>
            <button type="button" wire:click="create" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                <x-sp-icon name="plus" class="w-4 h-4" /> Ajouter une dépense récurrente
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
        <div class="bg-white rounded-xl border border-zinc-200 p-4">
            <div class="text-xs font-medium text-zinc-500">Actives</div>
            <div class="mt-1 text-2xl font-bold text-zinc-900">{{ $items->where('actif', true)->count() }}</div>
        </div>
        <div class="bg-white rounded-xl border border-zinc-200 p-4">
            <div class="text-xs font-medium text-zinc-500">Coût moyen par mois (TTC)</div>
            <div class="mt-1 text-2xl font-bold text-zinc-900 tabular-nums">{{ $money($parMois) }} <span class="text-sm font-medium text-zinc-400">{{ $currency }}</span></div>
        </div>
        <div class="bg-white rounded-xl border border-zinc-200 p-4">
            <div class="text-xs font-medium text-zinc-500">Prochaine échéance</div>
            @php $next = $items->where('actif', true)->sortBy('prochaine_date')->first(); @endphp
            <div class="mt-1 text-lg font-bold text-zinc-900">{{ $next ? $next->prochaine_date->format('d/m/Y').' · '.$next->libelle : '—' }}</div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Libellé</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Catégorie</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Fréquence</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Prochaine</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">Montant TTC</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Statut</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($items as $r)
                        <tr wire:key="rec-{{ $r->id }}" class="{{ $r->actif ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3 text-sm text-gray-900"><div class="font-medium">{{ $r->libelle }}</div>@if ($r->fournisseur)<div class="text-xs text-gray-500">{{ $r->fournisseur->name }}</div>@endif</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $r->categorieLabel() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $r->frequenceLabel() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $r->actif ? $r->prochaine_date->format('d/m/Y') : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 whitespace-nowrap">{{ $money($r->montant_ttc) }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($r->actif)<span class="inline-flex rounded-full bg-green-50 text-green-700 px-2 py-0.5 text-xs font-medium">Active</span>
                                @else<span class="inline-flex rounded-full bg-zinc-100 text-zinc-600 px-2 py-0.5 text-xs font-medium">En pause</span>@endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <x-row-actions>
                                    <button type="button" wire:click="edit({{ $r->id }})"><x-sp-icon name="pencil" class="w-4 h-4 shrink-0 text-zinc-500" /> Modifier</button>
                                    <button type="button" wire:click="toggle({{ $r->id }})"><x-sp-icon name="{{ $r->actif ? 'clock' : 'check' }}" class="w-4 h-4 shrink-0 text-zinc-500" /> {{ $r->actif ? 'Mettre en pause' : 'Reprendre' }}</button>
                                    <a href="{{ route('depenses.index', ['mois' => 'tout']) }}" wire:navigate><x-sp-icon name="wallet" class="w-4 h-4 shrink-0 text-zinc-500" /> Voir les dépenses ({{ $r->depenses()->count() }})</a>
                                    <button type="button" wire:click="delete({{ $r->id }})" wire:confirm="Supprimer cette dépense récurrente ? Les dépenses déjà créées sont gardées." class="!text-[#b42318] border-t border-zinc-200"><x-sp-icon name="trash" class="w-4 h-4 shrink-0" /> Supprimer</button>
                                </x-row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">Aucune dépense récurrente. Ajoutez par exemple votre loyer : il sera noté chaque mois tout seul.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-1 text-gray-900">{{ $editId ? 'Modifier la dépense récurrente' : 'Ajouter une dépense récurrente' }}</h2>
                <p class="text-sm text-gray-500 mb-4">Si la première date est déjà passée, les dépenses correspondantes sont créées tout de suite.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Libellé</label>
                        <input wire:model="libelle" type="text" placeholder="Ex. : Loyer du dépôt" class="w-full rounded-md border-gray-300 text-sm">
                        @error('libelle') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <select wire:model="categorie" class="w-full rounded-md border-gray-300 text-sm">
                            @foreach (\App\Models\Depense::CATEGORIES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur (facultatif)</label>
                        <select wire:model="fournisseur_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">—</option>
                            @foreach ($fournisseurs as $f)<option value="{{ $f->id }}">{{ $f->name }}</option>@endforeach
                        </select>
                        @error('fournisseur_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant TTC</label>
                        <input wire:model.live.debounce.400ms="montant_ttc" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm">
                        @error('montant_ttc') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Taux TVA %</label>
                            <input wire:model.live.debounce.400ms="taux_tva" type="number" step="0.01" min="0" max="100" class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">dont TVA</label>
                            <input wire:model="montant_tva" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                        @error('montant_tva') <p class="col-span-2 text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fréquence</label>
                        <select wire:model="frequence" class="w-full rounded-md border-gray-300 text-sm">
                            @foreach (\App\Models\DepenseRecurrente::FREQUENCES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jour du mois</label>
                        <input wire:model="jour" type="number" min="1" max="31" class="w-full rounded-md border-gray-300 text-sm">
                        <p class="text-xs text-gray-400 mt-0.5">29, 30 ou 31 : le dernier jour pour les mois plus courts.</p>
                        @error('jour') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $editId ? 'Prochaine date' : 'Première date' }}</label>
                        <input wire:model.live="date_debut" type="date" class="w-full rounded-md border-gray-300 text-sm">
                        @error('date_debut') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin (facultatif)</label>
                        <input wire:model="date_fin" type="date" class="w-full rounded-md border-gray-300 text-sm">
                        @error('date_fin') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement</label>
                        <select wire:model="mode_paiement" class="w-full rounded-md border-gray-300 text-sm">
                            @foreach (\App\Models\Depense::MODES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payé depuis (facultatif)</label>
                        <select wire:model="tresorerie_compte_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">—</option>
                            @foreach ($comptes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                        @error('tresorerie_compte_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button type="button" wire:click="save" wire:loading.attr="disabled" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif
</div>
