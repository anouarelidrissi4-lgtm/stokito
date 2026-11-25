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
            <h1 class="ind-h text-3xl sm:text-4xl uppercase leading-none">Copilot <span class="align-middle text-sm bg-[#111] text-[#ffcc00] px-1.5 py-0.5">IA</span></h1>
            <p class="mt-1 text-sm text-[#555]">Écrivez en français, en arabe ou en darija. Le Copilot répond avec vos chiffres et prépare vos documents ; vous vérifiez avant d’enregistrer.</p>
        </div>
        <div class="flex items-center gap-3 text-xs text-[#666]">
            <span class="ind-mono">{{ $left }} message(s) restant(s) ce mois-ci</span>
            @if ($thread)
                <button type="button" wire:click="reset_" class="border-2 border-[#111] bg-white px-2.5 py-1 font-semibold hover:bg-[#ffcc00]">Nouvelle conversation</button>
            @endif
        </div>
    </div>

    <div class="flex-1 border-2 border-[#111] bg-white flex flex-col">
        <div class="flex-1 p-3 sm:p-5 space-y-4 overflow-y-auto max-h-[62vh]" id="copilot-thread"
            x-data x-init="$el.scrollTop = $el.scrollHeight" @copilot-scroll.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)">
            @forelse ($thread as $m)
                @if ($m['role'] === 'user')
                    <div class="flex justify-end" dir="auto"><div class="max-w-[85%] bg-[#111] text-white px-4 py-2.5 text-[15px] whitespace-pre-line">{{ $m['text'] }}</div></div>
                @else
                    <div class="flex justify-start">
                        <div class="max-w-[90%] border-2 border-[#111] {{ ! empty($m['error']) ? 'bg-[#fde3e3]' : 'bg-[#f4f2ec]' }} px-4 py-3 text-[15px]" dir="auto">
                            <div class="prose-sm [&_p]:mb-2 [&_ul]:list-disc [&_ul]:ps-5 [&_ol]:list-decimal [&_ol]:ps-5 [&_li]:mb-0.5 [&_strong]:font-bold [&_table]:text-sm [&_td]:pe-3 [&_th]:pe-3 [&_th]:text-start">{!! $md($m['text']) !!}</div>
                            @foreach ($m['actions'] ?? [] as $a)
                                <a href="{{ $a['url'] }}" wire:navigate class="mt-2 inline-flex items-center gap-2 border-2 border-[#111] bg-[#ffcc00] px-3 py-1.5 text-sm font-bold shadow-[3px_3px_0_#111]">{{ $a['label'] }} →</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @empty
                <div class="text-center py-8">
                    <div class="ind-h text-2xl uppercase">Que voulez-vous faire ?</div>
                    <p class="text-sm text-[#666] mt-1">Exemples :</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        @foreach ($suggestions as $s)
                            <button type="button" wire:click="ask(@js($s))" class="border-2 border-[#111] bg-white px-3 py-1.5 text-sm hover:bg-[#fff3c4]" dir="auto">{{ $s }}</button>
                        @endforeach
                    </div>
                </div>
            @endforelse
            <div wire:loading.flex wire:target="send,ask" class="justify-start">
                <div class="border-2 border-[#111] bg-[#f4f2ec] px-4 py-3 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    Le Copilot réfléchit…
                </div>
            </div>
        </div>
        <form wire:submit="send" class="border-t-2 border-[#111] p-2 sm:p-3 flex gap-2" x-data @submit="$dispatch('copilot-scroll')">
            <textarea wire:model="input" rows="2" maxlength="1500" dir="auto" placeholder="Ex. : Facture pour Bâti Plus, 20 tubes carrés 40×40 à 15 € HT"
                @keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $el.form.requestSubmit(); }"
                class="flex-1 resize-none border-2 border-[#111] px-3 py-2 text-[15px] focus:ring-[#ffcc00] focus:border-[#111]"></textarea>
            <button type="submit" wire:loading.attr="disabled" wire:target="send,ask" class="shrink-0 ind-h uppercase tracking-wider border-2 border-[#111] bg-[#ffcc00] px-4 sm:px-5 disabled:opacity-40">Envoyer</button>
        </form>
    </div>
    <p class="mt-2 text-xs text-[#777]">Le Copilot peut se tromper : vérifiez les documents avant de les enregistrer. Vos messages sont traités par Anthropic (modèle Claude) uniquement pour vous répondre.</p>
</div>
