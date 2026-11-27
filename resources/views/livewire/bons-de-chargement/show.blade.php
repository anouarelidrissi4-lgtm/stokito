{{-- One loading slip: ordered vs loaded quantity per product (2026-11-27) --}}
@php
    $bl = $bc->bonLivraison;
    $labels = \App\Models\BonDeChargement::STATUTS;
    $badge = ['preparation' => 'bg-gray-100 text-gray-600', 'partiel' => 'bg-[#ffcc00]/30 text-zinc-900', 'charge' => 'bg-green-100 text-green-700', 'livre' => 'bg-zinc-900 text-white'];
    $lineBadge = ['a_charger' => 'bg-gray-100 text-gray-600', 'partiel' => 'bg-[#ffcc00]/30 text-zinc-900', 'charge' => 'bg-green-100 text-green-700'];
    $lineLabel = \App\Models\BonChargementLigne::STATUTS;
    $field = 'w-full rounded-md border-gray-300 text-sm';
@endphp
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl">
    <a href="{{ route('bons-de-chargement.index') }}" wire:navigate class="text-sm text-gray-500 hover:text-gray-900">← Bons de chargement</a>
    <div class="mt-1 mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">{{ $bc->numero }}</h1>
            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badge[$bc->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $labels[$bc->status] ?? $bc->status }}</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('bons-de-chargement.pdf', $bc->id) }}" target="_blank" class="inline-flex items-center gap-1.5 h-10 px-4 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-800 hover:border-zinc-900"><x-sp-icon name="printer" class="w-4 h-4" /> Imprimer</a>
            @if ($bc->status === 'livre')
                <button type="button" wire:click="marquerLivre(false)" class="h-10 px-4 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700">Annuler « livré »</button>
            @else
                <button type="button" wire:click="marquerLivre" wire:confirm="Marquer ce chargement comme livré ?" class="h-10 px-4 rounded-lg bg-zinc-900 text-white text-sm font-semibold hover:bg-zinc-700">Marquer comme livré</button>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg p-5 grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <p class="text-xs font-medium text-gray-500">Bon de livraison</p>
            <p class="mt-1 text-sm font-semibold">@if ($bl)<a href="{{ route('bon-livraisons.edit', $bl->id) }}" wire:navigate class="hover:underline">{{ $bl->numero }}</a>@else — @endif</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500">Client</p>
            <p class="mt-1 text-sm font-semibold">{{ $bl?->client ? \App\Support\SalesLists::clientLabel($bl->client) : '—' }}</p>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Immatriculation</label>
            <input wire:model="vehicule" type="text" maxlength="120" placeholder="Ex. 12345-A-6" class="{{ $field }}">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Nom du chauffeur</label>
            <input wire:model="chauffeur" type="text" maxlength="120" class="{{ $field }}">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Commercial responsable</label>
            <input wire:model="commercial" type="text" maxlength="120" class="{{ $field }}">
        </div>
    </div>

    <div class="mt-5 bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-gray-900">Produits à charger</h2>
            <button type="button" wire:click="toutCharger" class="h-9 px-3 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:border-zinc-900">Tout chargé</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-medium">Produit</th>
                        <th class="px-4 py-2.5 text-end font-medium">Qté commandée</th>
                        <th class="px-4 py-2.5 text-start font-medium">Unité</th>
                        <th class="px-4 py-2.5 text-start font-medium w-40">Qté chargée</th>
                        <th class="px-4 py-2.5 text-start font-medium">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($bc->lignes as $l)
                        @php
                            $v = $charge[$l->id] ?? '';
                            $st = ($v === '' || (float) $v <= 0) ? 'a_charger' : ((float) $v + 0.0001 < (float) $l->quantite_commandee ? 'partiel' : 'charge');
                        @endphp
                        <tr wire:key="l-{{ $l->id }}">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $l->designation }}</td>
                            <td class="px-4 py-3 text-end tabular-nums">{{ \App\Support\Fmt::qty($l->quantite_commandee) }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $l->unite ?: '—' }}</td>
                            <td class="px-4 py-3">
                                <input wire:model.blur="charge.{{ $l->id }}" type="number" step="0.001" min="0" inputmode="decimal" class="w-32 rounded-md border-gray-300 text-sm text-end">
                                @error('charge.'.$l->id) <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold {{ $lineBadge[$st] }}">{{ $lineLabel[$st] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200">
            <label class="block text-xs font-medium text-gray-500 mb-1">Notes</label>
            <textarea wire:model="notes" rows="2" maxlength="1000" class="{{ $field }}"></textarea>
        </div>
    </div>

    <div class="mt-4 flex justify-end">
        <button type="button" wire:click="save" class="h-11 px-6 rounded-lg bg-[#16a34a] text-white text-sm font-semibold hover:bg-[#15803d]">Enregistrer</button>
    </div>
</div>
