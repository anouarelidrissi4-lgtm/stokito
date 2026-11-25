<x-row-actions>
                                @php $converti = $devis->bonLivraisons()->exists() || $devis->factures()->exists(); @endphp
                                @if (! $converti && auth()->user()->hasPermission('devis_valider'))
                                    <button type="button" wire:click="convertToBonLivraison({{ $devis->id }})" wire:confirm="Valider ce devis et créer le bon de livraison ?" class="!font-semibold"><x-sp-icon name="truck" class="w-4 h-4 shrink-0 text-zinc-500" /> Valider en bon de livraison</button>
                                @endif
                                @if (auth()->user()->hasPermission('devis_creer'))
                                    <button type="button" wire:click="duplicate({{ $devis->id }})"><x-sp-icon name="copy" class="w-4 h-4 shrink-0 text-zinc-500" /> Dupliquer</button>
                                @endif
                                <a href="{{ route('devis.edit', $devis->id) }}" wire:navigate><x-sp-icon name="pencil" class="w-4 h-4 shrink-0 text-zinc-500" /> Modifier</a>
                                <a href="{{ route('devis.pdf', $devis->id) }}" target="_blank" rel="noopener"><x-sp-icon name="printer" class="w-4 h-4 shrink-0 text-zinc-500" /> Imprimer</a>
                                @if (! $converti && auth()->user()->hasPermission('devis_valider'))
                                    <button type="button" wire:click="convertToFacture({{ $devis->id }})"><x-sp-icon name="doc" class="w-4 h-4 shrink-0 text-zinc-500" /> Facturer</button>
                                @endif
                                <button type="button" wire:click="openSend({{ $devis->id }})" title="{{ $devis->sent_at ? 'Envoyé le '.$devis->sent_at->format('d/m/Y H:i') : 'Envoyer par email' }}"><x-sp-icon name="send" class="w-4 h-4 shrink-0 text-zinc-500" /> {{ $devis->sent_at ? 'Renvoyer par email' : 'Envoyer par email' }}</button>
                                <a href="{{ \App\Support\WhatsAppShare::url($devis) }}" target="_blank" rel="noopener" class="!text-[#0a6b37]" title="Envoyer le devis sur WhatsApp (lien vers le PDF)"><svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.78 1.22h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.01c-.24.68-1.4 1.3-1.93 1.38-.49.08-1.12.11-1.81-.11-.42-.13-.95-.31-1.64-.6-2.89-1.25-4.78-4.15-4.92-4.34-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.12 1.01-2.41.26-.29.57-.36.76-.36l.55.01c.18.01.41-.07.64.49.24.58.81 2.01.88 2.16.07.15.12.32.02.51-.1.19-.14.31-.28.48-.14.17-.29.37-.41.5-.14.14-.28.3-.12.58.16.28.72 1.19 1.55 1.93 1.07.95 1.96 1.25 2.24 1.39.28.14.44.12.6-.07.16-.19.68-.79.87-1.06.18-.27.37-.22.62-.13.26.09 1.63.77 1.91.91.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg> WhatsApp</a>
                                @if (auth()->user()->hasPermission('devis_supprimer'))
                                    <button type="button" wire:click="delete({{ $devis->id }})" wire:confirm="Supprimer ce devis ?" class="!text-[#b42318] border-t border-[#e3e0d8]"><x-sp-icon name="trash" class="w-4 h-4 shrink-0" /> Supprimer</button>
                                @endif
                            </x-row-actions>
