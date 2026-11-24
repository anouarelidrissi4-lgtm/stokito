<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-red-700 border-2 border-[#111] font-bold text-xs text-white uppercase tracking-widest hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition']) }}>
    {{ $slot }}
</button>
