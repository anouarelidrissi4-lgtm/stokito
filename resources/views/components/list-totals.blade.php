{{-- Totals of a sales list for the current filters (2026-11-26). --}}
@props(['totals' => []])
@if ($totals)
    <div class="grid grid-cols-2 {{ [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3'][count($totals)] ?? 'lg:grid-cols-4' }} gap-2 sm:gap-3 mb-4">
        @foreach ($totals as $label => $value)
            <div class="rounded-xl border border-zinc-200 bg-white shadow-sm px-4 py-2.5">
                <div class="text-xs font-medium text-zinc-500">{{ $label }}</div>
                <div class="text-base sm:text-lg font-bold text-zinc-900 tabular-nums whitespace-nowrap">{{ $value }}</div>
            </div>
        @endforeach
    </div>
@endif
