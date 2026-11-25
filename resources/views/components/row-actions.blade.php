{{-- Row actions grouped in one "Actions" button (2026-11-24). The menu is position:fixed, so the
     table's horizontal scroll never cuts it; it opens upwards near the bottom of the screen. --}}
<div class="relative inline-block text-start" @keydown.escape.window="open = false" @scroll.window="open = false" @resize.window="open = false" @click.outside="open = false"
    x-data="{
        open: false,
        style: '',
        toggle() {
            if (this.open) { this.open = false; return; }
            const r = this.$refs.btn.getBoundingClientRect(), w = 264;
            const rtl = document.documentElement.dir === 'rtl';
            const left = Math.max(8, Math.min(window.innerWidth - w - 8, rtl ? r.left : r.right - w));
            const below = window.innerHeight - r.bottom > 280;
            this.style = 'left:' + left + 'px;' + (below ? 'top:' + (r.bottom + 4) + 'px' : 'bottom:' + (window.innerHeight - r.top + 4) + 'px');
            this.open = true;
        }
    }">
    <button type="button" x-ref="btn" @click="toggle()" :aria-expanded="open" aria-haspopup="true"
        class="inline-flex items-center gap-1 border border-zinc-200 px-2.5 py-1 text-xs font-semibold hover:bg-zinc-100 rounded-lg" :class="open ? 'bg-[#ffcc00]' : 'bg-white'">
        {{ __('Actions') }}
        <svg class="w-3.5 h-3.5 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <div x-show="open" x-cloak x-transition.opacity.duration.100ms @click="open = false" :style="style" role="menu"
        class="fixed z-50 w-[264px] bg-white border border-zinc-200 shadow-md shadow-zinc-900/5 py-1 text-sm text-zinc-900 [&>*]:flex [&>*]:w-full [&>*]:items-center [&>*]:gap-2 [&>*]:px-4 [&>*]:py-2.5 [&>*]:text-start [&>*]:font-medium [&>*:hover]:bg-[#fff3c4] rounded-lg">
        {{ $slot }}
    </div>
</div>
