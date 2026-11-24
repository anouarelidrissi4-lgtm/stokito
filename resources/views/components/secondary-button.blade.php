<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-white border-2 border-[#111] font-bold text-xs text-[#111] uppercase tracking-widest hover:bg-[#ffcc00] focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:ring-offset-2 disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>
