@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#111] focus:ring-[#ffcc00] rounded-md shadow-sm']) }}>
