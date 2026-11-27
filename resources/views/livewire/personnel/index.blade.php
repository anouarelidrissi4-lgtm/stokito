{{-- Module Personnel (2026-11-28): workers, what they earned on their current period, advances, loans --}}
@php
    $money = fn ($v) => \App\Support\Fmt::money($v);
    $field = 'w-full rounded-md border-gray-300 text-sm';
    $label = 'block text-sm font-medium text-gray-700 mb-1';
@endphp
<div class="p-4 sm:p-6 lg:p-8 max-w-6xl">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Personnel</h1>
            <p class="text-sm text-slate-500">Vos employés, leurs jours de travail, leurs avances et leur paie.</p>
        </div>
        @unless ($locked)
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('personnel.pointage') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"><x-sp-icon name="clock" class="w-4 h-4" /> Pointage</a>
                <button type="button" wire:click="create" class="inline-flex items-center gap-1.5 rounded-lg bg-[#16a34a] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#15803d]"><x-sp-icon name="plus" class="w-4 h-4" /> Ajouter un employé</button>
            </div>
        @endunless
    </div>

    @if ($locked)
        @include('livewire.personnel._locked')
    @else
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <div class="text-xs font-semibold text-slate-500">À payer (période en cours)</div>
                <div class="mt-1 text-2xl font-bold text-slate-900 tabular-nums">{{ $money($totaux['apayer']) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <div class="text-xs font-semibold text-slate-500">Avances à retirer de la paie</div>
                <div class="mt-1 text-2xl font-bold text-amber-700 tabular-nums">{{ $money($totaux['avances']) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <div class="text-xs font-semibold text-slate-500">Crédits restant dus</div>
                <div class="mt-1 text-2xl font-bold text-red-700 tabular-nums">{{ $money($totaux['credits']) }} <span class="text-sm font-medium text-slate-400">{{ $currency }}</span></div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center gap-3">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Nom, poste ou téléphone…" class="flex-1 min-w-[12rem] rounded-md border-gray-300 text-sm">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" wire:model.live="inactifs" class="rounded border-gray-300"> Afficher les anciens employés</label>
            </div>
            @if ($employes->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-slate-500">{{ trim($search) !== '' ? 'Aucun employé ne correspond.' : 'Aucun employé pour l’instant.' }}</p>
                    @if (trim($search) === '')
                        <button type="button" wire:click="create" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-[#16a34a] px-4 py-2 text-sm font-semibold text-white hover:bg-[#15803d]"><x-sp-icon name="plus" class="w-4 h-4" /> Ajouter votre premier employé</button>
                    @endif
                </div>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($employes as $e)
                        <li wire:key="emp-{{ $e->id }}" class="p-4 flex flex-col lg:flex-row lg:items-center gap-3 {{ $e->actif ? '' : 'opacity-60' }}">
                            <a href="{{ route('personnel.show', $e->id) }}" wire:navigate class="flex items-center gap-3 min-w-0 lg:w-72">
                                <span class="grid place-items-center w-10 h-10 shrink-0 rounded-full bg-slate-100 text-slate-700 font-bold">{{ mb_strtoupper(mb_substr($e->nom, 0, 1)) }}</span>
                                <span class="min-w-0">
                                    <span class="block font-semibold text-slate-900 truncate hover:underline">{{ $e->nom }}</span>
                                    <span class="block text-xs text-slate-500 truncate">{{ $e->poste ?: '—' }} · {{ $e->tauxLabel() }}{{ $e->actif ? '' : ' · ancien' }}</span>
                                </span>
                            </a>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-1 flex-1 text-sm">
                                <div><span class="block text-xs text-slate-500">Jours travaillés</span><span class="font-semibold tabular-nums">{{ rtrim(rtrim(number_format($e->_jours, 1, ',', ' '), '0'), ',') }}</span> <span class="text-xs text-slate-400 whitespace-nowrap">{{ $e->_periode }}</span></div>
                                <div><span class="block text-xs text-slate-500">Avances</span><span class="font-semibold tabular-nums {{ $e->_avances > 0 ? 'text-amber-700' : '' }}">{{ $money($e->_avances) }}</span></div>
                                <div><span class="block text-xs text-slate-500">Crédit restant</span><span class="font-semibold tabular-nums {{ $e->_credit > 0 ? 'text-red-700' : '' }}">{{ $money($e->_credit) }}</span></div>
                                <div><span class="block text-xs text-slate-500">À payer</span><span class="font-bold tabular-nums {{ $e->_apayer < 0 ? 'text-red-700' : 'text-slate-900' }}">{{ $money($e->_apayer) }}</span></div>
                            </div>
                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                <button type="button" wire:click="openAvance({{ $e->id }})" class="h-9 px-3 rounded-lg border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">Avance</button>
                                <a href="{{ route('personnel.show', $e->id) }}" wire:navigate class="inline-flex items-center h-9 px-3 rounded-lg bg-[#16a34a] text-sm font-semibold text-white hover:bg-[#15803d]">Paie et fiche</a>
                                <button type="button" wire:click="edit({{ $e->id }})" class="h-9 px-3 rounded-lg bg-[#ffcc00] text-sm font-semibold text-zinc-900 hover:bg-[#ffd633]">Modifier</button>
                                <button type="button" wire:click="toggleActif({{ $e->id }})" wire:confirm="{{ $e->actif ? 'Marquer '.$e->nom.' comme ancien employé ? Son historique est gardé.' : 'Réactiver '.$e->nom.' ?' }}" class="h-9 px-3 rounded-lg text-sm font-semibold text-slate-500 hover:text-slate-900">{{ $e->actif ? 'Ne travaille plus ici' : 'Réactiver' }}</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <p class="mt-3 text-xs text-slate-500">« À payer » : salaire de la période en cours (d’après le pointage) moins les avances et la retenue des crédits. Ce n’est pas un bulletin de paie officiel (CNSS, IR).</p>
    @endif

    {{-- employee form --}}
    @if ($showForm)
        <div class="fixed inset-0 bg-black/40 flex items-start sm:items-center justify-center z-50 overflow-y-auto p-4" wire:click.self="$set('showForm', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $editId ? 'Modifier l’employé' : 'Nouvel employé' }}</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2"><label class="{{ $label }}">Nom et prénom *</label><input wire:model="nom" type="text" class="{{ $field }}" autofocus>@error('nom')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                    <div><label class="{{ $label }}">Poste</label><input wire:model="poste" type="text" placeholder="Ex. soudeur, chauffeur" class="{{ $field }}"></div>
                    <div><label class="{{ $label }}">Téléphone</label><input wire:model="telephone" type="tel" class="{{ $field }}"></div>
                    <div><label class="{{ $label }}">CIN</label><input wire:model="cin" type="text" class="{{ $field }}"></div>
                    <div><label class="{{ $label }}">Date d’entrée</label><input wire:model="date_entree" type="date" class="{{ $field }}"></div>
                    <div><label class="{{ $label }}">Payé *</label>
                        <select wire:model.live="type_paie" class="{{ $field }}">@foreach (\App\Models\Employe::TYPES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                    <div><label class="{{ $label }}">Salaire {{ \App\Models\Employe::UNITES[$type_paie] ?? '' }} *</label><input wire:model="taux" type="number" step="0.01" min="0" class="{{ $field }}">@error('taux')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                    <div class="sm:col-span-2"><label class="{{ $label }}">Notes</label><textarea wire:model="notes" rows="2" class="{{ $field }}"></textarea></div>
                </div>
                <p class="mt-3 text-xs text-gray-500">Par jour : jours travaillés × salaire. Par semaine ou par mois : le salaire, moins les absences (1/6 de la semaine ou 1/26 du mois par jour d’absence).</p>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button type="button" wire:click="save" class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif

    {{-- quick advance --}}
    @if ($avanceFor)
        @php $avEmp = $employes->firstWhere('id', $avanceFor); @endphp
        <div class="fixed inset-0 bg-black/40 flex items-start sm:items-center justify-center z-50 overflow-y-auto p-4" wire:click.self="$set('avanceFor', null)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-semibold text-gray-900">Avance sur salaire</h2>
                <p class="text-sm text-gray-500 mb-4">{{ $avEmp->nom ?? '' }} · retirée automatiquement de sa prochaine paie.</p>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="{{ $label }}">Montant *</label><input wire:model="avMontant" type="number" step="0.01" min="0" class="{{ $field }}" autofocus>@error('avMontant')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                    <div><label class="{{ $label }}">Date *</label><input wire:model="avDate" type="date" class="{{ $field }}"></div>
                    <div><label class="{{ $label }}">Payée en</label><select wire:model="avMode" class="{{ $field }}">@foreach (\App\Support\Paie::MODES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                    <div><label class="{{ $label }}">Sortie de</label><select wire:model="avCompte" class="{{ $field }}"><option value="">— Aucun compte —</option>@foreach ($comptes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>@error('avCompte')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                    <div class="col-span-2"><label class="{{ $label }}">Note</label><input wire:model="avNotes" type="text" class="{{ $field }}"></div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" wire:click="$set('avanceFor', null)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button type="button" wire:click="saveAvance" class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">Enregistrer l’avance</button>
                </div>
            </div>
        </div>
    @endif
</div>
