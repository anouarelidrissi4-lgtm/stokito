<x-row-actions>
    @if ($bl->status === 'draft')
        <button type="button" wire:click="markDelivered({{ $bl->id }})" wire:confirm="Marquer ce bon comme livré ? Les quantités seront retirées du stock." class="!font-semibold"><x-sp-icon name="check" class="w-4 h-4 shrink-0 text-zinc-500" /> Marquer livré</button>
    @endif
    @if ($bl->facture)
        <a href="{{ route('factures.edit', $bl->facture->id) }}" wire:navigate><x-sp-icon name="doc" class="w-4 h-4 shrink-0 text-zinc-500" /> Voir la facture {{ $bl->facture->numero }}</a>
    @elseif (auth()->user()->hasPermission('bl_facturer'))
        <a href="{{ route('factures.create', ['blId' => $bl->id]) }}" wire:navigate class="!font-semibold"><x-sp-icon name="doc" class="w-4 h-4 shrink-0 text-zinc-500" /> Facturer</a>
    @endif
    @if (auth()->user()->hasPermission('bl_creer'))
        <button type="button" wire:click="duplicate({{ $bl->id }})"><x-sp-icon name="copy" class="w-4 h-4 shrink-0 text-zinc-500" /> Dupliquer</button>
    @endif
    <a href="{{ route('bon-livraisons.edit', $bl->id) }}" wire:navigate><x-sp-icon name="pencil" class="w-4 h-4 shrink-0 text-zinc-500" /> Modifier</a>
    <a href="{{ route('bon-livraisons.pdf', $bl->id) }}" target="_blank" rel="noopener"><x-sp-icon name="printer" class="w-4 h-4 shrink-0 text-zinc-500" /> Imprimer</a>
    @if (auth()->user()->hasPermission('bl_supprimer'))
        <button type="button" wire:click="delete({{ $bl->id }})" wire:confirm="Supprimer ce bon de livraison ?" class="!text-[#b42318] border-t border-[#e3e0d8]"><x-sp-icon name="trash" class="w-4 h-4 shrink-0" /> Supprimer</button>
    @endif
</x-row-actions>
