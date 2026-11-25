@php
    $md = fn ($t) => \Illuminate\Support\Str::markdown((string) $t, ['html_input' => 'escape', 'allow_unsafe_links' => false]);
    $suggestions = [
        'Combien j’ai vendu ce mois-ci ?',
        'Quelles factures ne sont pas encore payées ?',
        'Quels produits sont en stock bas ?',
        'Devis pour Bâti Plus : 50 fer rond Ø10 et 6 peintures antirouille',
        'Chhal bi3t lyoum?',
    ];
@endphp
<div class="p-3 sm:p-5 lg:p-6 max-w-4xl flex flex-col min-h-[calc(100vh-7rem)]">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
        <div>
            <h1 class="ind-h text-3xl sm:text-4xl leading-none">Copilot <span class="align-middle text-sm bg-zinc-900 text-white px-1.5 py-0.5">IA</span></h1>
            <p class="mt-1 text-sm text-zinc-500">Écrivez en français, en arabe ou en darija. Le Copilot répond avec vos chiffres et prépare vos documents ; vous vérifiez avant d’enregistrer.</p>
        </div>
        <div class="flex items-center gap-3 text-xs text-zinc-500">
            <span class="ind-mono">{{ $left }} message(s) restant(s) ce mois-ci</span>
            @if ($thread)
                <button type="button" wire:click="reset_" class="border border-zinc-200 bg-white px-2.5 py-1 font-semibold hover:bg-zinc-100 rounded-lg">Nouvelle conversation</button>
            @endif
        </div>
    </div>

    <div class="flex-1 border border-zinc-200 bg-white flex flex-col rounded-lg">
        <div class="flex-1 p-3 sm:p-5 space-y-4 overflow-y-auto max-h-[62vh]" id="copilot-thread"
            x-data x-init="$el.scrollTop = $el.scrollHeight" @copilot-scroll.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)">
            @forelse ($thread as $m)
                @if ($m['role'] === 'user')
                    <div class="flex justify-end" dir="auto"><div class="max-w-[85%] bg-zinc-900 text-white px-4 py-2.5 text-[15px] whitespace-pre-line">{{ $m['text'] }}</div></div>
                @else
                    <div class="flex justify-start">
                        <div class="max-w-[90%] border border-zinc-200 {{ ! empty($m['error']) ? 'bg-[#fde3e3]' : 'bg-zinc-50' }} px-4 py-3 text-[15px] rounded-lg" dir="auto">
                            <div class="prose-sm [&_p]:mb-2 [&_ul]:list-disc [&_ul]:ps-5 [&_ol]:list-decimal [&_ol]:ps-5 [&_li]:mb-0.5 [&_strong]:font-bold [&_table]:text-sm [&_td]:pe-3 [&_th]:pe-3 [&_th]:text-start">{!! $md($m['text']) !!}</div>
                            @foreach ($m['actions'] ?? [] as $a)
                                <a href="{{ $a['url'] }}" wire:navigate class="mt-2 inline-flex items-center gap-2 border border-zinc-200 bg-[#ffcc00] px-3 py-1.5 text-sm font-bold shadow-sm rounded-lg">{{ $a['label'] }} →</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @empty
                <div class="text-center py-8">
                    <div class="ind-h text-2xl">Que voulez-vous faire ?</div>
                    <p class="text-sm text-zinc-500 mt-1">Exemples :</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        @foreach ($suggestions as $s)
                            <button type="button" wire:click="ask(@js($s))" class="border border-zinc-200 bg-white px-3 py-1.5 text-sm hover:bg-[#fff3c4] rounded-lg" dir="auto">{{ $s }}</button>
                        @endforeach
                    </div>
                </div>
            @endforelse
            <div wire:loading.flex wire:target="attachment" class="justify-start">
                <div class="border border-zinc-200 bg-[#fff3c4] px-4 py-3 text-sm flex items-center gap-2 rounded-lg">
                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    Lecture de la facture (10 à 40 secondes)…
                </div>
            </div>
            <div wire:loading.flex wire:target="send,ask" class="justify-start">
                <div class="border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm flex items-center gap-2 rounded-lg">
                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    Le Copilot réfléchit…
                </div>
            </div>
        </div>
        <form wire:submit="send" class="border-t border-zinc-200 p-2 sm:p-3 flex gap-2" x-data @submit="$dispatch('copilot-scroll')">
            <textarea wire:model="input" rows="2" maxlength="1500" dir="auto" placeholder="Ex. : Facture pour Bâti Plus, 20 tubes carrés 40×40 à 15 € HT"
                @keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $el.form.requestSubmit(); }"
                class="flex-1 resize-none border border-zinc-200 px-3 py-2 text-[15px] focus:ring-[#ffcc00] focus:border-[#111] rounded-lg"></textarea>
            <div class="shrink-0 flex flex-col gap-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="send,ask,attachment" class="flex-1 ind-h border border-zinc-200 bg-[#ffcc00] px-4 sm:px-5 disabled:opacity-40 rounded-lg">Envoyer</button>
                <label title="Joindre une facture fournisseur (photo ou PDF) : le Copilot la lit et prépare le bon de commande"
                    class="cursor-pointer inline-flex items-center justify-center gap-1.5 border border-zinc-200 bg-white px-3 py-1.5 text-xs font-bold hover:bg-[#fff3c4] rounded-lg"
                    wire:loading.class="opacity-40 pointer-events-none" wire:target="attachment,send,ask">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.66-.9l.82-1.2A2 2 0 0 1 10.07 4h3.86a2 2 0 0 1 1.66.9l.82 1.2a2 2 0 0 0 1.66.9H19a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><circle cx="12" cy="13" r="3"/></svg>
                    Facture
                    <input type="file" wire:model="attachment" accept="image/*,application/pdf" class="sr-only">
                </label>
            </div>
        </form>
        @error('attachment') <p class="px-3 pb-2 text-sm font-semibold text-[#b42318]">{{ $message }}</p> @enderror
    </div>
    <p class="mt-2 text-xs text-zinc-500">Le Copilot peut se tromper : vérifiez les documents avant de les enregistrer. Vos messages sont traités par Anthropic (modèle Claude) uniquement pour vous répondre.</p>
</div>
