@if ($showSend)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="$set('showSend', false)">
        <div class="bg-white border-2 border-[#111] shadow-[8px_8px_0_#111] w-full max-w-lg">
            <div class="h-2 bg-[#ffcc00] border-b-2 border-[#111]"></div>
            <div class="p-5 space-y-3">
                <h3 class="ind-h text-2xl uppercase leading-none">Envoyer par email</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">À</label>
                    <input wire:model="sendTo" type="email" class="w-full rounded-md border-gray-300 text-sm" placeholder="client@exemple.com">
                    @error('sendTo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Objet</label>
                    <input wire:model="sendSujet" type="text" class="w-full rounded-md border-gray-300 text-sm">
                    @error('sendSujet') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea wire:model="sendMessage" rows="8" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                    @error('sendMessage') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <p class="text-xs text-gray-500">Le PDF est joint automatiquement. Les réponses du client arrivent sur l’email de Ma Société.</p>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" wire:click="$set('showSend', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button type="button" wire:click="sendDocument" wire:loading.attr="disabled" class="bg-[#111] text-[#ffcc00] px-4 py-2 text-sm font-bold">
                        <span wire:loading.remove wire:target="sendDocument">Envoyer</span><span wire:loading wire:target="sendDocument">Envoi…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
