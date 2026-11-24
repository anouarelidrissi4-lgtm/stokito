<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-[#111] border-2 border-[#111] font-bold text-xs text-[#ffcc00] uppercase tracking-widest hover:bg-black focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:ring-offset-2 disabled:opacity-50 transition']) }}>
    {{ $slot }}
</button>
