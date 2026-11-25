<x-row-actions>
    <button type="button" wire:click="edit({{ $reglement->id }})"><x-sp-icon name="pencil" class="w-4 h-4 shrink-0 text-zinc-500" /> Modifier</button>
    @if ($reglement->facture)
        <a href="{{ route('factures.pdf', $reglement->facture->id) }}" target="_blank" rel="noopener"><x-sp-icon name="printer" class="w-4 h-4 shrink-0 text-zinc-500" /> Voir la facture {{ $reglement->facture->numero }}</a>
    @endif
    <button type="button" wire:click="delete({{ $reglement->id }})" wire:confirm="Supprimer ce règlement ? La facture redevient à payer." class="!text-[#b42318] border-t border-[#e3e0d8]"><x-sp-icon name="trash" class="w-4 h-4 shrink-0" /> Supprimer</button>
</x-row-actions>
