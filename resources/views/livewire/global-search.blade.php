<div x-data="{
        open: false, i: 0,
        show() { this.open = true; this.i = 0; this.$nextTick(() => this.$refs.q.focus()) },
        items() { return [...this.$el.querySelectorAll('[data-sr]')] },
        move(d) { const n = this.items().length; if (!n) return; this.i = (this.i + d + n) % n; this.items()[this.i].scrollIntoView({ block: 'nearest' }) },
        go() { const a = this.items()[this.i]; if (a) { this.open = false; a.click() } },
    }"
    @keydown.window.prevent.ctrl.k="show()" @keydown.window.prevent.meta.k="show()"
    @keydown.window="if ($event.key === '/' && !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName) && !document.activeElement.isContentEditable) { $event.preventDefault(); show() }"
    @keydown.escape.window="open = false">
    <button type="button" @click="show()" class="flex items-center gap-2 h-9 px-2.5 sm:px-3 border border-zinc-200 bg-zinc-50 text-zinc-500 hover:text-zinc-900 hover:border-zinc-300 rounded-lg" title="{{ __('Rechercher') }} (Ctrl+K)">
        <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <span class="hidden md:inline text-sm">{{ __('Rechercher') }}</span>
        <kbd class="hidden md:inline text-[10px] border border-zinc-200 bg-white px-1 rounded">Ctrl K</kbd>
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 bg-black/50 flex items-start justify-center p-3 sm:pt-24" @click.self="open = false">
        <div class="w-full max-w-xl bg-white border border-zinc-200 shadow-2xl rounded-xl overflow-hidden" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="go()">
            <div class="flex items-center gap-2 px-3 border-b border-zinc-200">
                <svg viewBox="0 0 24 24" class="w-5 h-5 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input x-ref="q" wire:model.live.debounce.250ms="q" @input="i = 0" type="search" autocomplete="off" class="flex-1 border-0 focus:ring-0 py-3 text-base text-slate-900" placeholder="{{ __('Client, produit, n° de facture ou de devis…') }}">
                <button type="button" @click="open = false" class="ind-mono text-[10px] border border-slate-300 px-1.5 py-0.5 text-slate-500 rounded-lg">Échap</button>
            </div>
            <div class="max-h-[60vh] overflow-y-auto">
                @if (mb_strlen(trim($q)) < 2)
                    <p class="px-4 py-6 text-sm text-slate-500">{{ __('Tapez au moins 2 caractères. Astuce : Ctrl+K ou / ouvre la recherche partout.') }}</p>
                @elseif (! $groups)
                    <p class="px-4 py-6 text-sm text-slate-500">{{ __('Aucun résultat.') }}</p>
                @else
                    @php $n = 0; @endphp
                    @foreach ($groups as $label => $items)
                        <div class="px-4 pt-3 pb-1 ind-mono text-[11px] uppercase text-zinc-500">{{ $label }}</div>
                        @foreach ($items as $it)
                            <a data-sr href="{{ $it['url'] }}" wire:navigate @click="open = false" @mouseenter="i = {{ $n }}"
                               :class="i === {{ $n }} ? 'bg-[#ffcc00]' : ''" class="block px-4 py-2 border-s-4 border-transparent" :style="i === {{ $n }} ? 'border-color:#111' : ''">
                                <div class="text-sm font-semibold text-slate-900 truncate">{{ $it['title'] }}</div>
                                @if ($it['sub'])<div class="text-xs text-slate-600 truncate">{{ $it['sub'] }}</div>@endif
                            </a>
                            @php $n++; @endphp
                        @endforeach
                    @endforeach
                @endif
            </div>
            <div class="px-4 py-2 border-t border-slate-200 ind-mono text-[10px] text-slate-500">↑ ↓ {{ __('pour choisir') }} · Entrée {{ __('pour ouvrir') }}</div>
        </div>
    </div>
</div>
