<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-white border border-zinc-300 rounded-lg font-semibold text-sm text-zinc-800 shadow-sm hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:ring-offset-2 disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>
