@props(['onScan', 'label' => 'Scanner'])

<div x-data="barcodeScanner('{{ $onScan }}')" x-cloak class="inline-block">
    <button type="button" @click="open()" class="h-9 px-3 flex items-center gap-1.5 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 whitespace-nowrap">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M3 4a1 1 0 011-1h2a1 1 0 010 2H5v1a1 1 0 01-2 0V4zm11-1h2a1 1 0 011 1v2a1 1 0 11-2 0V5h-1a1 1 0 110-2zM3 14a1 1 0 112 0v1h1a1 1 0 110 2H4a1 1 0 01-1-1v-2zm14-1a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 110-2h1v-1a1 1 0 011-1zM7 6a1 1 0 00-1 1v6a1 1 0 001 1h1V6H7zm3 0v8h1V6h-1zm3 0v8h1a1 1 0 001-1V7a1 1 0 00-1-1h-1z"/>
        </svg>
        {{ $label }}
    </button>

    <div x-show="show" style="display:none" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg overflow-hidden w-full max-w-sm" @click.outside="close()">
            <div class="p-3 border-b flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-900">Scanner un code-barres</span>
                <button type="button" @click="close()" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
            </div>
            <video x-ref="video" class="w-full bg-black" style="aspect-ratio: 4/3;"></video>
            <p x-show="error" x-text="error" class="text-red-600 text-xs p-2"></p>
            <p x-show="!error" class="text-xs text-gray-400 p-2 text-center">Visez le code-barres avec la camera...</p>
        </div>
    </div>
</div>
