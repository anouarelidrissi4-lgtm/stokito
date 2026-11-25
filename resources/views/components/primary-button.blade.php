<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-zinc-900 border border-zinc-900 rounded-lg font-semibold text-sm text-white hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-[#ffcc00] focus:ring-offset-2 disabled:opacity-50 transition']) }}>
    {{ $slot }}
</button>
