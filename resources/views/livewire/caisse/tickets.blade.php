@php($money = fn ($v) => \App\Support\Fmt::money($v))
<div class="p-3 sm:p-5 lg:p-6 max-w-6xl">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="ind-h text-3xl sm:text-4xl uppercase leading-none">Tickets de caisse</h1>
        <div class="flex gap-2 text-sm font-semibold">
            <a href="{{ route('caisse.pos') }}" wire:navigate class="border-2 border-[#111] bg-[#ffcc00] px-3 py-1.5">← Caisse</a>
            @if (auth()->user()->hasPermission('caisse_cloturer'))
                <a href="{{ route('caisse.clotures') }}" wire:navigate class="border-2 border-[#111] bg-white px-3 py-1.5 hover:bg-[#ffcc00]">Clôtures (Z)</a>
            @endif
        </div>
    </div>
    @if (session('success'))
        <div class="mb-4 border-2 border-[#111] bg-[#dff3e6] px-4 py-2 text-sm font-semibold">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-end gap-3 mb-3">
        <label class="text-sm">Jour<input type="date" wire:model.live="date" class="block mt-1 border-2 border-[#111] px-2 py-1.5 text-sm"></label>
        <label class="text-sm">N° de ticket<input type="text" wire:model.live.debounce.300ms="numero" placeholder="T-000012" class="block mt-1 border-2 border-[#111] px-2 py-1.5 text-sm"></label>
        <div class="ms-auto text-sm">Total : <b class="ind-mono">{{ $money($sum) }} {{ $currency }}</b></div>
    </div>

    <div class="border-2 border-[#111] bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-[#111] text-[#ffcc00] ind-mono text-[11px]">
                <tr><th class="px-3 py-2 text-start">N°</th><th class="px-3 py-2 text-start">Heure</th><th class="px-3 py-2 text-start">Client</th><th class="px-3 py-2 text-start">Paiement</th><th class="px-3 py-2 text-end">Total TTC</th><th class="px-3 py-2"></th></tr>
            </thead>
            <tbody class="divide-y divide-[#e3e0d8]">
                @forelse ($tickets as $t)
                    <tr wire:key="t{{ $t->id }}" class="{{ $t->isAnnulation() ? 'bg-[#fde3e3]/50' : '' }}">
                        <td class="px-3 py-2.5 ind-mono">{{ $t->numero }}
                            @if ($t->isAnnulation())<span class="block text-[10px] text-[#b42318] font-semibold">ANNULATION</span>@elseif ($t->annulation)<span class="block text-[10px] text-[#b42318]">annulé ({{ $t->annulation->numero }})</span>@endif</td>
                        <td class="px-3 py-2.5 ind-mono">{{ $t->created_at->format('d/m H:i') }}<span class="block text-[10px] text-[#888]">{{ $t->user_name }}</span></td>
                        <td class="px-3 py-2.5">{{ $t->client_name ?: 'Client comptoir' }}</td>
                        <td class="px-3 py-2.5 text-xs">{{ $t->paiements->map(fn ($p) => (\App\Support\Caisse::MODES[$p->mode] ?? $p->mode))->unique()->join(', ') }}</td>
                        <td class="px-3 py-2.5 text-end ind-mono font-semibold {{ (float) $t->total_ttc < 0 ? 'text-[#b42318]' : '' }}">{{ $money($t->total_ttc) }}</td>
                        <td class="px-3 py-2.5 text-end whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('caisse.tickets.print', $t->id) }}" target="_blank" class="border-2 border-[#111] bg-white px-2 py-1 text-xs font-semibold hover:bg-[#ffcc00]">Imprimer</a>
                                @if ($t->facture)
                                    <a href="{{ route('factures.edit', $t->facture->id) }}" wire:navigate class="border-2 border-[#111] bg-[#fff3c4] px-2 py-1 text-xs font-semibold" title="Facture établie pour ce ticket">{{ $t->facture->numero }}</a>
                                @elseif (! $t->isAnnulation() && ! $t->annulation && auth()->user()->hasPermission('factures_consulter'))
                                    <a href="{{ route('factures.create', ['ticket' => $t->id]) }}" wire:navigate class="border-2 border-[#111] bg-white px-2 py-1 text-xs font-semibold hover:bg-[#ffcc00]" title="Le client demande une facture">Facture</a>
                                @endif
                                @if (! $t->isAnnulation() && ! $t->annulation && auth()->user()->hasPermission('caisse_annuler'))
                                    <button type="button" wire:click="askCancel({{ $t->id }})" class="border-2 border-[#b42318] text-[#b42318] bg-white px-2 py-1 text-xs font-semibold">Annuler</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-8 text-center text-[#888]">Aucun ticket.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $tickets->links() }}</div>
    <p class="mt-3 text-xs text-[#666]">Un ticket enregistré ne peut être ni modifié ni supprimé. Pour corriger une erreur, annulez-le : un ticket d’annulation (montants négatifs) est créé, le stock et les paiements sont repris. Chaque réimpression est notée « Duplicata ».</p>

    @if ($cancelId)
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" wire:click.self="$set('cancelId', null)">
            <div class="w-full max-w-md bg-white border-2 border-[#111] shadow-[8px_8px_0_#111] p-5">
                <h2 class="ind-h text-2xl uppercase">Annuler le ticket</h2>
                <p class="mt-1 text-sm text-[#444]">Un ticket d’annulation sera créé. Le stock et les paiements seront repris.</p>
                <label class="block mt-4 text-sm font-semibold">Motif (obligatoire)
                    <input type="text" wire:model="motif" maxlength="200" placeholder="Erreur de saisie, retour client…" class="mt-1 w-full border-2 border-[#111] px-2 py-2 text-sm"></label>
                @error('motif') <p class="mt-2 text-sm font-semibold text-[#b42318]">{{ $message }}</p> @enderror
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="$set('cancelId', null)" class="border-2 border-[#111] bg-white px-4 py-2 text-sm font-semibold">Retour</button>
                    <button type="button" wire:click="cancel" class="border-2 border-[#111] bg-[#b42318] text-white px-4 py-2 text-sm font-semibold">Créer l’annulation</button>
                </div>
            </div>
        </div>
    @endif
</div>
