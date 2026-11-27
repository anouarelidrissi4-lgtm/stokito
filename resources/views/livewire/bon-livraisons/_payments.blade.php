{{-- Règlements du bon de livraison (2026-11-26), reused by the bon de commande fournisseur (2026-11-27). Before the first save they are kept in $pendingPayments and saved with the document. --}}
@php
    $money = fn ($v) => \App\Support\Fmt::money($v);
    $detail = fn ($banque, $num, $ech, $tireur = null) => implode(' · ', array_filter([$banque, $num ? 'n° '.$num : null, $tireur, $ech ? 'échéance '.$ech : null]));
    $rows = $payments
        ? $payments->map(fn ($r) => ['key' => $r->id, 'date' => $r->date?->format('d/m/Y'), 'mode' => $r->modeLabel(), 'compte' => $r->tresorerieCompte?->name,
            'detail' => $detail($r->banque, $r->numero_cheque, $r->date_echeance?->format('d/m/Y'), $r->tireur), 'obs' => method_exists($r, 'observation') ? $r->observation() : $r->notes, 'montant' => (float) $r->montant])
        : collect($pendingPayments)->map(fn ($p, $i) => ['key' => $i, 'date' => \Illuminate\Support\Carbon::parse($p['date'])->format('d/m/Y'),
            'mode' => \App\Support\SalesLists::MODES[$p['mode']] ?? $p['mode'], 'compte' => null,
            'detail' => $detail($p['banque'] ?? null, $p['numero_cheque'] ?? null, ! empty($p['date_echeance']) ? \Illuminate\Support\Carbon::parse($p['date_echeance'])->format('d/m/Y') : null, $p['tireur'] ?? null), 'obs' => $p['notes'] ?? null, 'montant' => (float) $p['montant']]);
    $regle = $rows->sum('montant');
    $reste = round((float) $this->totalTtc - $regle, 2);
    $field = 'w-full rounded-md border-gray-300 text-sm';
    $modes = $payGroup === 'papier' ? ['cheque' => 'Chèque', 'effet' => 'Effet'] : ['especes' => 'Espèces', 'virement' => 'Virement', 'versement' => 'Versement', 'transfert' => 'Transfert cash'];
    if ($payMode && ! isset($modes[$payMode])) {
        $modes[$payMode] = \App\Support\SalesLists::MODES[$payMode] ?? $payMode;   // an older payment edited here keeps its mode
    }
    // 2026-11-27: one small button; the payment types show only after a click
    $choices = ['especes' => 'Espèces', 'virement' => 'Virement', 'versement' => 'Versement', 'transfert' => 'Transfert cash', 'cheque' => 'Chèque', 'effet' => 'Effet'];
    $editing = $payEditKey !== null;
@endphp
<div id="reglements" class="rounded-xl border border-gray-200 bg-white" x-data="{ choose: false }">
    <div class="p-4 flex flex-wrap items-center justify-between gap-3 border-b border-gray-200">
        <div>
            <h3 class="font-semibold text-gray-900">Règlements</h3>
            <p class="text-xs text-gray-500">{{ $payments ? 'Chaque règlement est enregistré tout de suite.' : ($payLater ?? 'Ils seront enregistrés avec le bon de livraison.') }}</p>
        </div>
        <button type="button" @click="choose = ! choose" :aria-expanded="choose" data-sp="pay-add" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-[#16a34a] text-white text-sm font-semibold hover:bg-[#15803d]">
            <x-sp-icon name="plus" class="w-4 h-4" /> Ajouter un règlement
        </button>
    </div>
    <div x-show="choose" x-cloak class="px-4 py-3 border-b border-gray-200 bg-gray-50">
        <p class="text-xs font-medium text-gray-500 mb-2">Type de règlement</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($choices as $k => $l)
                <button type="button" wire:click="openPayment('{{ $k }}')" @click="choose = false"
                    class="h-9 px-3 rounded-lg text-sm font-semibold border {{ in_array($k, ['cheque', 'effet'], true) ? 'border-[#e6b800] bg-[#ffcc00]/25 text-zinc-900 hover:bg-[#ffcc00]/50' : 'border-[#16a34a]/40 bg-white text-[#15803d] hover:bg-green-50' }}">{{ $l }}</button>
            @endforeach
        </div>
    </div>

    @if ($payGroup)
        <div class="p-4 bg-zinc-50 border-b border-gray-200">
            <p class="mb-3 text-sm font-semibold text-gray-900">{{ $editing ? 'Modifier le règlement' : 'Nouveau règlement' }}</p>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Mode</label>
                    <select wire:model.live="payMode" class="{{ $field }}">
                        @foreach ($modes as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Montant</label>
                    <input wire:model="payMontant" type="number" step="0.01" min="0" class="{{ $field }}">
                    @error('payMontant') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                    <input wire:model="payDate" type="date" class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Compte <span class="font-normal text-gray-400">(facultatif)</span></label>
                    <select wire:model="payCompte" class="{{ $field }}">
                        <option value="">—</option>
                        @foreach ($comptes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                @if (in_array($payMode, ['cheque', 'effet', 'virement', 'versement'], true))
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Banque</label>
                        <input wire:model="payBanque" list="stk-banks" type="text" maxlength="100" placeholder="Choisir ou écrire une banque" class="{{ $field }}">
                    </div>
                @endif
                @if ($payMode !== 'especes')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ in_array($payMode, ['cheque', 'effet'], true) ? 'N° '.($payMode === 'cheque' ? 'du chèque' : 'de l’effet') : 'Référence' }}</label>
                        <input wire:model="payNumero" type="text" maxlength="60" class="{{ $field }}">
                    </div>
                @endif
                @if ($payGroup === 'papier')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Date d’échéance</label>
                        <input wire:model="payEcheance" type="date" class="{{ $field }}">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $payMode === 'effet' ? 'Nom du tireur' : 'Nom sur le chèque' }}</label>
                        <input wire:model="payTireur" type="text" maxlength="120" placeholder="Ex. Martin SARL" class="{{ $field }}">
                    </div>
                @endif
                <div class="col-span-2 lg:col-span-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Observation <span class="font-normal text-gray-400">(facultatif)</span></label>
                    <input wire:model="payNotes" type="text" maxlength="500" placeholder="Ex. remis par le chauffeur, payé au dépôt…" class="{{ $field }}">
                </div>
            </div>
            <datalist id="stk-banks">@foreach ($banks as $b)<option value="{{ $b }}"></option>@endforeach</datalist>
            <div class="mt-3 flex justify-end gap-2">
                <button type="button" wire:click="closePayment" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                <button type="button" wire:click="addPayment" class="px-4 py-2 rounded-lg bg-zinc-900 text-white text-sm font-semibold hover:bg-zinc-700">{{ $editing ? 'Enregistrer la modification' : 'Ajouter le règlement' }}</button>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500">
                <tr><th class="hidden sm:table-cell px-2 sm:px-4 py-2 text-start font-medium">Date</th><th class="px-2 sm:px-4 py-2 text-start font-medium">Type</th><th class="px-2 sm:px-4 py-2 text-end font-medium">Montant</th><th class="px-2 py-2"></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $r)
                    <tr wire:key="pay-{{ $payments ? 'r' : 'p' }}-{{ $r['key'] }}">
                        <td class="hidden sm:table-cell px-2 sm:px-4 py-2.5 whitespace-nowrap text-gray-700">{{ $r['date'] }}</td>
                        <td class="px-2 sm:px-4 py-2.5">
                            <span class="font-medium text-gray-900">{{ $r['mode'] }}</span><span class="sm:hidden block text-xs text-gray-500">{{ $r['date'] }}</span>
                            @if ($r['detail'] || $r['compte'])<span class="block text-xs text-gray-500">{{ implode(' · ', array_filter([$r['detail'], $r['compte']])) }}</span>@endif
                            @if (! empty($r['obs']))<span class="block text-xs italic text-gray-500">{{ $r['obs'] }}</span>@endif
                        </td>
                        <td class="px-2 sm:px-4 py-2.5 text-end font-semibold tabular-nums whitespace-nowrap">{{ $money($r['montant']) }}</td>
                        <td class="ps-0 pe-1 sm:px-2 py-2.5 text-end whitespace-nowrap">
                            <button type="button" wire:click="editPayment({{ $r['key'] }})" data-sp="pay-edit" class="inline-flex items-center gap-1 h-8 px-2.5 rounded-md bg-[#ffcc00] text-zinc-900 text-xs font-bold hover:bg-[#ffd633] align-middle" title="Modifier"><x-sp-icon name="pencil" class="w-3.5 h-3.5" />Modifier</button>
                            <button type="button" wire:click="removePayment({{ $r['key'] }})" wire:confirm="Supprimer ce règlement ?" class="p-1 sm:p-1.5 rounded text-gray-400 hover:text-red-600" title="Supprimer"><x-sp-icon name="trash" class="w-4 h-4" /></button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">Aucun règlement pour l’instant.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="border-t border-gray-200">
                <tr><td class="hidden sm:table-cell"></td><td class="px-2 sm:px-4 py-2 text-end font-semibold text-gray-700">Total des règlements</td><td class="px-2 sm:px-4 py-2 text-end font-bold tabular-nums whitespace-nowrap">{{ $money($regle) }}</td><td></td></tr>
                <tr>
                    <td class="hidden sm:table-cell"></td><td class="px-2 sm:px-4 py-2 text-end font-semibold text-gray-700">Reste à payer</td>
                    <td class="px-2 sm:px-4 py-2 text-end font-bold tabular-nums whitespace-nowrap {{ $reste > 0.01 ? 'text-red-600' : ((float) $this->totalTtc > 0 ? 'text-green-700' : 'text-gray-400') }}">{{ $reste > 0.01 ? $money($reste) : ($reste < -0.01 ? 'Trop-perçu '.$money(-$reste) : ((float) $this->totalTtc > 0 ? 'Payé' : '—')) }}</td><td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
