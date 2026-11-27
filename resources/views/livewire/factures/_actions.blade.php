<x-row-actions>
    @if ($facture->resteAPayer() > 0)
        <a href="{{ route('reglements-clients.create', ['factureId' => $facture->id]) }}" wire:navigate class="!font-semibold"><x-sp-icon name="coins" class="w-4 h-4 shrink-0 text-zinc-500" /> Encaisser</a>
    @endif
    <button type="button" wire:click="duplicate({{ $facture->id }})"><x-sp-icon name="copy" class="w-4 h-4 shrink-0 text-zinc-500" /> Dupliquer</button>
    <a href="{{ route('factures.edit', $facture->id) }}" wire:navigate><x-sp-icon name="pencil" class="w-4 h-4 shrink-0 text-zinc-500" /> Modifier</a>
    <a href="{{ route('factures.pdf', $facture->id) }}" target="_blank" rel="noopener"><x-sp-icon name="printer" class="w-4 h-4 shrink-0 text-zinc-500" /> Imprimer</a>
    @if (\App\Support\FacturX::applicable())
        <a href="{{ route('factures.xml', $facture->id) }}" title="XML Factur-X (EN 16931) pour votre logiciel comptable ou votre plateforme agréée"><x-sp-icon name="download" class="w-4 h-4 shrink-0 text-zinc-500" /> XML Factur-X</a>
    @endif
    <button type="button" wire:click="openSend({{ $facture->id }})" title="{{ $facture->sent_at ? 'Envoyée le '.$facture->sent_at->format('d/m/Y H:i') : 'Envoyer par email' }}"><x-sp-icon name="send" class="w-4 h-4 shrink-0 text-zinc-500" /> {{ $facture->sent_at ? 'Renvoyer par email' : 'Envoyer par email' }}</button>
    @if (\App\Support\WhatsAppShare::isLate($facture) && ($wa = \App\Support\Relance::whatsappUrl($facture)))
        <a href="{{ $wa }}" target="_blank" rel="noopener" class="!text-[#0a6b37]" title="Relancer le client sur WhatsApp"><svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.78 1.22h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.01c-.24.68-1.4 1.3-1.93 1.38-.49.08-1.12.11-1.81-.11-.42-.13-.95-.31-1.64-.6-2.89-1.25-4.78-4.15-4.92-4.34-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.12 1.01-2.41.26-.29.57-.36.76-.36l.55.01c.18.01.41-.07.64.49.24.58.81 2.01.88 2.16.07.15.12.32.02.51-.1.19-.14.31-.28.48-.14.17-.29.37-.41.5-.14.14-.28.3-.12.58.16.28.72 1.19 1.55 1.93 1.07.95 1.96 1.25 2.24 1.39.28.14.44.12.6-.07.16-.19.68-.79.87-1.06.18-.27.37-.22.62-.13.26.09 1.63.77 1.91.91.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg> Relancer sur WhatsApp</a>
    @else
        <a href="{{ \App\Support\WhatsAppShare::url($facture) }}" target="_blank" rel="noopener" class="!text-[#0a6b37]" title="Envoyer la facture sur WhatsApp"><svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.78 1.22h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.01c-.24.68-1.4 1.3-1.93 1.38-.49.08-1.12.11-1.81-.11-.42-.13-.95-.31-1.64-.6-2.89-1.25-4.78-4.15-4.92-4.34-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.12 1.01-2.41.26-.29.57-.36.76-.36l.55.01c.18.01.41-.07.64.49.24.58.81 2.01.88 2.16.07.15.12.32.02.51-.1.19-.14.31-.28.48-.14.17-.29.37-.41.5-.14.14-.28.3-.12.58.16.28.72 1.19 1.55 1.93 1.07.95 1.96 1.25 2.24 1.39.28.14.44.12.6-.07.16-.19.68-.79.87-1.06.18-.27.37-.22.62-.13.26.09 1.63.77 1.91.91.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg> WhatsApp</a>
    @endif
    <button type="button" wire:click="delete({{ $facture->id }})" wire:confirm="Supprimer cette facture ?" class="!text-[#b42318] border-t border-[#e3e0d8]"><x-sp-icon name="trash" class="w-4 h-4 shrink-0" /> Supprimer</button>
</x-row-actions>
