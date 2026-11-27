{{-- A worker's file (2026-11-28): current period, pay (compute + record), advances, loans, pay history with receipts --}}
@php
    $money = fn ($v) => \App\Support\Fmt::money($v);
    $num = fn ($v) => rtrim(rtrim(number_format((float) $v, 1, ',', ' '), '0'), ',');
    $field = 'w-full rounded-md border-gray-300 text-sm';
    $label = 'block text-sm font-medium text-gray-700 mb-1';
    $card = 'bg-white rounded-xl border border-slate-200 shadow-sm';
    $e = $employe;
    $modes = \App\Support\Paie::MODES;
@endphp
<div class="p-4 sm:p-6 lg:p-8 max-w-6xl">
    <a href="{{ route('personnel.index') }}" wire:navigate class="text-sm text-slate-500 hover:text-slate-900">← Personnel</a>
    <div class="mt-2 flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3 min-w-0">
            <span class="grid place-items-center w-12 h-12 shrink-0 rounded-full bg-[#ffcc00] text-zinc-900 text-lg font-bold">{{ mb_strtoupper(mb_substr($e->nom, 0, 1)) }}</span>
            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-slate-900 truncate">{{ $e->nom }}</h1>
                <p class="text-sm text-slate-500">{{ $e->poste ?: 'Employé' }} · {{ $e->tauxLabel() }}{{ $e->telephone ? ' · '.$e->telephone : '' }}{{ $e->date_entree ? ' · depuis le '.$e->date_entree->format('d/m/Y') : '' }}</p>
            </div>
        </div>
        @unless ($locked)
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="open('avance')" class="h-10 px-4 rounded-lg border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">+ Avance</button>
                <button type="button" wire:click="open('credit')" class="h-10 px-4 rounded-lg border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">+ Crédit</button>
                <button type="button" wire:click="open('paie')" class="h-10 px-4 rounded-lg bg-[#16a34a] text-sm font-semibold text-white hover:bg-[#15803d]">Calculer et payer</button>
            </div>
        @endunless
    </div>

    @if ($locked)
        @include('livewire.personnel._locked')
    @else
        @php [$pd, $pf] = $periode; $estime = $calc['base'] - $calc['avances'] - $calc['retenue_credit']; @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <div class="{{ $card }} p-4"><div class="text-xs font-semibold text-slate-500">Jours travaillés ({{ $pd->format('d/m') }} → {{ $pf->format('d/m') }})</div><div class="mt-1 text-2xl font-bold tabular-nums">{{ $num($calc['jours']) }}</div>
                <div class="text-xs text-slate-500">{{ $calc['compteur']['A'] }} absence{{ $calc['compteur']['A'] > 1 ? 's' : '' }} · {{ $calc['compteur']['C'] }} congé · {{ $calc['compteur']['F'] }} férié</div></div>
            <div class="{{ $card }} p-4"><div class="text-xs font-semibold text-slate-500">Salaire de la période</div><div class="mt-1 text-2xl font-bold tabular-nums">{{ $money($calc['base']) }}</div><div class="text-xs text-slate-500">{{ $currency }}</div></div>
            <div class="{{ $card }} p-4"><div class="text-xs font-semibold text-slate-500">Avances + crédit à retirer</div><div class="mt-1 text-2xl font-bold tabular-nums text-amber-700">{{ $money($calc['avances'] + $calc['retenue_credit']) }}</div><div class="text-xs text-slate-500">Crédit restant : {{ $money($e->creditRestant()) }}</div></div>
            <div class="{{ $card }} p-4 {{ $estime < 0 ? 'ring-1 ring-red-200' : '' }}"><div class="text-xs font-semibold text-slate-500">{{ $estime < 0 ? 'Il vous doit' : 'Vous lui devez' }}</div><div class="mt-1 text-2xl font-bold tabular-nums {{ $estime < 0 ? 'text-red-700' : 'text-[#16a34a]' }}">{{ $money(abs($estime)) }}</div><div class="text-xs text-slate-500">si vous payez aujourd’hui</div></div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            {{-- pays --}}
            <div class="{{ $card }} lg:col-span-2">
                <div class="px-4 py-3 border-b border-slate-100 font-semibold text-slate-900">Paies</div>
                @forelse ($paies as $p)
                    <div wire:key="paie-{{ $p->id }}" class="px-4 py-3 border-b border-slate-50 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                        <span class="font-medium text-slate-900 min-w-[11rem]">{{ $p->periodeLabel() }}</span>
                        <span class="text-slate-500">{{ $num($p->jours) }} j · base {{ $money($p->base) }}@if ((float) $p->prime > 0) · prime {{ $money($p->prime) }}@endif @if ((float) $p->avances > 0) · avances −{{ $money($p->avances) }}@endif @if ((float) $p->retenue_credit > 0) · crédit −{{ $money($p->retenue_credit) }}@endif</span>
                        <span class="ms-auto font-bold tabular-nums">{{ $money($p->net) }} {{ $currency }}</span>
                        <span class="text-xs text-slate-500">payé le {{ $p->date_paiement->format('d/m/Y') }} · {{ $modes[$p->mode_paiement] ?? $p->mode_paiement }}</span>
                        <a href="{{ route('personnel.recu', $p->id) }}" target="_blank" class="inline-flex items-center gap-1 h-8 px-3 rounded-lg bg-[#d92d20] text-white text-xs font-semibold hover:bg-[#b42318]"><x-sp-icon name="printer" class="w-3.5 h-3.5" /> Reçu PDF</a>
                        <button type="button" wire:click="deletePaie({{ $p->id }})" wire:confirm="Supprimer cette paie ? Les avances et la retenue du crédit seront rendues, et la dépense supprimée." class="text-red-600 hover:text-red-800" title="Supprimer"><x-sp-icon name="trash" class="w-4 h-4" /></button>
                    </div>
                @empty
                    <p class="px-4 py-6 text-sm text-slate-500">Aucune paie pour l’instant. Cliquez sur <b>Calculer et payer</b>.</p>
                @endforelse
            </div>

            {{-- advances --}}
            <div class="{{ $card }}">
                <div class="px-4 py-3 border-b border-slate-100 font-semibold text-slate-900">Avances</div>
                @forelse ($avances as $a)
                    <div wire:key="av-{{ $a->id }}" class="px-4 py-2.5 border-b border-slate-50 flex items-center gap-3 text-sm">
                        <span class="text-slate-500 w-20">{{ $a->date->format('d/m/Y') }}</span>
                        <span class="flex-1 min-w-0 truncate">{{ $a->notes ?: ($modes[$a->mode_paiement] ?? '') }}</span>
                        <span class="text-xs {{ $a->paie_id ? 'text-slate-400' : 'text-amber-700 font-semibold' }}">{{ $a->paie_id ? 'retirée de la paie' : 'à retirer' }}</span>
                        <span class="font-semibold tabular-nums">{{ $money($a->montant) }}</span>
                        @unless ($a->paie_id)<button type="button" wire:click="deleteAvance({{ $a->id }})" wire:confirm="Supprimer cette avance ?" class="text-red-600 hover:text-red-800" title="Supprimer"><x-sp-icon name="trash" class="w-4 h-4" /></button>@endunless
                    </div>
                @empty
                    <p class="px-4 py-6 text-sm text-slate-500">Aucune avance.</p>
                @endforelse
            </div>

            {{-- loans --}}
            <div class="{{ $card }}">
                <div class="px-4 py-3 border-b border-slate-100 font-semibold text-slate-900">Crédits</div>
                @forelse ($credits as $c)
                    @php $pct = (float) $c->montant > 0 ? min(100, round((float) $c->rembourse / (float) $c->montant * 100)) : 0; @endphp
                    <div wire:key="cr-{{ $c->id }}" class="px-4 py-3 border-b border-slate-50 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="text-slate-500 w-20">{{ $c->date->format('d/m/Y') }}</span>
                            <span class="flex-1">{{ $money($c->montant) }} · {{ $money($c->mensualite) }} par paie</span>
                            <span class="font-semibold tabular-nums {{ $c->restant() > 0 ? 'text-red-700' : 'text-[#16a34a]' }}">{{ $c->restant() > 0 ? 'reste '.$money($c->restant()) : 'remboursé' }}</span>
                            @if ((float) $c->rembourse == 0)<button type="button" wire:click="deleteCredit({{ $c->id }})" wire:confirm="Supprimer ce crédit ?" class="text-red-600 hover:text-red-800" title="Supprimer"><x-sp-icon name="trash" class="w-4 h-4" /></button>@endif
                        </div>
                        <div class="mt-2 h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full bg-[#16a34a]" style="width: {{ $pct }}%"></div></div>
                    </div>
                @empty
                    <p class="px-4 py-6 text-sm text-slate-500">Aucun crédit.</p>
                @endforelse
            </div>
        </div>
        <p class="mt-4 text-xs text-slate-500">La paie enregistre le net payé dans vos <a href="{{ route('depenses.index') }}" wire:navigate class="underline">dépenses</a> (catégorie Salaires), comme les avances. Un crédit n’est pas une dépense : c’est de l’argent prêté, repris sur les paies. Ce reçu n’est pas un bulletin de paie officiel (CNSS, IR).</p>
    @endif

    {{-- panels --}}
    @if ($panel)
        <div class="fixed inset-0 bg-black/40 flex items-start sm:items-center justify-center z-50 overflow-y-auto p-4" wire:click.self="$set('panel', '')">
            <div class="bg-white rounded-xl shadow-xl w-full {{ $panel === 'paie' ? 'max-w-xl' : 'max-w-md' }} p-6">
                @if ($panel === 'paie')
                    <h2 class="text-lg font-semibold text-gray-900">Paie de {{ $e->nom }}</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ \App\Models\Employe::TYPES[$e->type_paie] }} · {{ $e->tauxLabel() }}</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="{{ $label }}">Du</label><input wire:model.live="debut" type="date" class="{{ $field }}">@error('debut')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                        <div><label class="{{ $label }}">Au</label><input wire:model.live="fin" type="date" class="{{ $field }}">@error('fin')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                    </div>
                    @if ($compteur)
                        <p class="mt-2 text-xs text-slate-500">Pointage : {{ $compteur['P'] }} présent · {{ $compteur['D'] }} demi · {{ $compteur['A'] }} absent · {{ $compteur['C'] }} congé · {{ $compteur['F'] }} férié</p>
                    @endif
                    <div class="mt-4 rounded-xl border border-slate-200 divide-y divide-slate-100 text-sm">
                        <div class="flex items-center gap-3 px-3 py-2"><span class="flex-1">Jours travaillés</span><input wire:model="jours" type="number" step="0.5" min="0" class="w-28 rounded-md border-gray-300 text-sm text-right"></div>
                        <div class="flex items-center gap-3 px-3 py-2"><span class="flex-1">Salaire de base <span class="text-xs text-slate-400">(modifiable)</span></span><input wire:model.live.debounce.400ms="base" type="number" step="0.01" min="0" class="w-28 rounded-md border-gray-300 text-sm text-right"></div>
                        <div class="flex items-center gap-3 px-3 py-2"><span class="flex-1">+ Prime</span><input wire:model.live.debounce.400ms="prime" type="number" step="0.01" min="0" class="w-28 rounded-md border-gray-300 text-sm text-right"></div>
                        <div class="flex items-center gap-3 px-3 py-2 text-amber-800"><span class="flex-1">− Avances non retirées</span><span class="w-28 text-right font-semibold tabular-nums">{{ $money($avancesPeriode) }}</span></div>
                        <div class="flex items-center gap-3 px-3 py-2 text-red-800"><span class="flex-1">− Retenue crédit</span><input wire:model.live.debounce.400ms="retenue" type="number" step="0.01" min="0" class="w-28 rounded-md border-gray-300 text-sm text-right"></div>
                        <div class="flex items-center gap-3 px-3 py-3 bg-slate-50"><span class="flex-1 font-bold">Net à payer</span><span class="w-28 text-right text-lg font-bold tabular-nums {{ $this->net < 0 ? 'text-red-700' : 'text-[#16a34a]' }}">{{ $money($this->net) }}</span></div>
                    </div>
                    @error('base')<p class="text-red-600 text-xs mt-2">{{ $message }}</p>@enderror
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <div><label class="{{ $label }}">Payé le</label><input wire:model="date" type="date" class="{{ $field }}"></div>
                        <div><label class="{{ $label }}">En</label><select wire:model="mode" class="{{ $field }}">@foreach ($modes as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                        <div><label class="{{ $label }}">Sortie de</label><select wire:model="compte" class="{{ $field }}"><option value="">— Aucun —</option>@foreach ($comptes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                        <div class="col-span-3"><label class="{{ $label }}">Note</label><input wire:model="notes" type="text" class="{{ $field }}"></div>
                    </div>
                    <div class="flex justify-end gap-2 mt-5">
                        <button type="button" wire:click="$set('panel', '')" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                        <button type="button" wire:click="payer" class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">Enregistrer la paie</button>
                    </div>
                @else
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ $panel === 'avance' ? 'Avance sur salaire' : 'Crédit à l’employé' }}</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ $panel === 'avance' ? 'Retirée automatiquement de sa prochaine paie.' : 'Remboursé par une retenue sur chaque paie.' }}</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="{{ $label }}">Montant *</label><input wire:model="montant" type="number" step="0.01" min="0" class="{{ $field }}" autofocus>@error('montant')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                        @if ($panel === 'credit')
                            <div><label class="{{ $label }}">Retenue par paie *</label><input wire:model="mensualite" type="number" step="0.01" min="0" class="{{ $field }}">@error('mensualite')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                        @endif
                        <div><label class="{{ $label }}">Date *</label><input wire:model="date" type="date" class="{{ $field }}"></div>
                        <div><label class="{{ $label }}">Payé en</label><select wire:model="mode" class="{{ $field }}">@foreach ($modes as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                        <div class="{{ $panel === 'credit' ? '' : 'col-span-2' }}"><label class="{{ $label }}">Sortie de</label><select wire:model="compte" class="{{ $field }}"><option value="">— Aucun compte —</option>@foreach ($comptes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>@error('compte')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                        <div class="col-span-2"><label class="{{ $label }}">Note</label><input wire:model="notes" type="text" class="{{ $field }}"></div>
                    </div>
                    <div class="flex justify-end gap-2 mt-5">
                        <button type="button" wire:click="$set('panel', '')" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                        <button type="button" wire:click="{{ $panel === 'avance' ? 'saveAvance' : 'saveCredit' }}" class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">Enregistrer</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
