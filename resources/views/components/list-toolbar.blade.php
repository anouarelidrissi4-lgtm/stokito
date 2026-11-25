{{-- Search + period + reset + print, shared by the sales lists (2026-11-26). Lives inside a Livewire component. --}}
@props(['printUrl' => null, 'placeholder' => 'Rechercher…', 'dates' => true])
<div class="p-4 border-b border-zinc-200 grid grid-cols-2 md:flex md:flex-wrap md:items-end gap-2">
    <div class="col-span-2 md:flex-1 md:min-w-[220px]">
        <label class="block text-xs font-medium text-zinc-500 mb-1">Recherche</label>
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ $placeholder }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    @if ($dates)
        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">Du</label>
            <input wire:model.live="dateStart" type="date" class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">Au</label>
            <input wire:model.live="dateEnd" type="date" class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <button type="button" wire:click="resetFilters" class="h-[38px] px-3 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Réinitialiser</button>
    @endif
    @if ($printUrl)
        <a href="{{ $printUrl }}" target="_blank" rel="noopener" class="h-[38px] px-3 inline-flex items-center justify-center gap-1.5 rounded-md border border-gray-300 bg-white text-sm font-semibold text-gray-800 hover:bg-gray-50 {{ $dates ? '' : 'col-span-2 md:col-span-1' }}">
            <x-sp-icon name="printer" class="w-4 h-4" /> Imprimer
        </a>
    @endif
</div>
