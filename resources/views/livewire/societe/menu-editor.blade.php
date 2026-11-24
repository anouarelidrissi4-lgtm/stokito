<div class="bg-white shadow rounded-lg overflow-hidden mt-6">
    <div class="p-6 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide">Menu lateral</h2>
            <p class="text-xs text-gray-400 mt-1">Renommez les liens, reorganisez les sections ou deplacez des liens hors section. Cliquez sur une section pour la deplier.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="resetToDefault" wire:confirm="Retablir le menu par defaut ? Vos personnalisations non enregistrees seront perdues."
                class="text-xs font-medium text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50">Retablir par défaut</button>
            <button type="button" wire:click="addSection"
                class="text-xs font-medium text-indigo-600 hover:text-indigo-700 px-3 py-2 rounded-lg hover:bg-[#fff3c4]">+ Ajouter une section</button>
        </div>
    </div>

    @if (session('menu_success'))
        <div class="mx-6 mt-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('menu_success') }}</div>
    @endif

    <div class="p-6 space-y-3">

        <!-- Liens sans section -->
        <div x-data="{ open: false }" class="border border-gray-200 rounded-lg">
            <button type="button" @click="open = ! open" class="w-full flex items-center gap-2 px-4 py-3 bg-gray-50 rounded-t-lg text-left">
                <span class="text-gray-400 text-sm shrink-0" x-text="open ? '▾' : '▸'"></span>
                <span class="text-sm font-semibold text-gray-600">Liens sans section</span>
                <span class="text-xs text-gray-400 ml-auto">{{ collect($items)->where('section', '')->count() }} lien(s)</span>
            </button>

            <div x-show="open" x-cloak class="border-t border-gray-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-wide">
                            <th class="text-left px-4 py-2 font-medium">Nom actuel / lien</th>
                            <th class="text-left px-4 py-2 font-medium">Nouvelle etiquette</th>
                            <th class="text-left px-4 py-2 font-medium w-20">Ordre</th>
                            <th class="text-left px-4 py-2 font-medium w-48">Section</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $hasUnsectioned = false; @endphp
                        @foreach ($items as $index => $item)
                            @continue($item['section'] !== '')
                            @php $hasUnsectioned = true; @endphp
                            <tr wire:key="item-row-{{ $item['key'] }}">
                                <td class="px-4 py-2 text-gray-500">
                                    {{ $item['defaultLabel'] }}
                                    <div class="text-xs text-gray-300">{{ $item['route'] }}</div>
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model="items.{{ $index }}.label" type="text" class="w-full rounded-md border-gray-300 text-sm">
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model="items.{{ $index }}.order" type="number" class="w-20 rounded-md border-gray-300 text-sm">
                                </td>
                                <td class="px-4 py-2">
                                    <select wire:model="items.{{ $index }}.section" class="w-full rounded-md border-gray-300 text-sm">
                                        <option value="">Sans section</option>
                                        @foreach ($sectionOptions as $opt)
                                            <option value="{{ $opt['key'] }}">{{ $opt['icon'] }} {{ $opt['label'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                        @if (! $hasUnsectioned)
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-xs text-gray-400 italic">Aucun lien hors section. Utilisez la colonne "Section" d'un lien ci-dessous pour l'en retirer.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sections -->
        @foreach ($sectionOptions as $section)
            <div x-data="{ open: false }" class="border border-gray-200 rounded-lg" wire:key="section-block-{{ $section['key'] }}">
                <button type="button" @click="open = ! open" class="w-full flex items-center gap-2 px-4 py-3 bg-gray-50 rounded-t-lg text-left">
                    <span class="text-gray-400 text-sm shrink-0" x-text="open ? '▾' : '▸'"></span>
                    <span class="text-lg">{{ $section['icon'] }}</span>
                    <span class="text-sm font-semibold text-gray-700">{{ $section['label'] }}</span>
                    <span class="text-xs text-gray-400 ml-auto">{{ collect($items)->where('section', $section['key'])->count() }} lien(s)</span>
                </button>

                <div x-show="open" x-cloak class="border-t border-gray-200">
                    <div class="px-4 py-3 flex flex-wrap items-end gap-3 bg-gray-50/50 border-b border-gray-100">
                        <div class="flex-1 min-w-[10rem]">
                            <label class="block text-xs text-gray-400 mb-0.5">Étiquette de section</label>
                            @foreach ($sections as $sIndex => $s)
                                @continue($s['key'] !== $section['key'])
                                <input wire:model="sections.{{ $sIndex }}.label" type="text" class="w-full rounded-md border-gray-300 text-sm">
                            @endforeach
                        </div>
                        <div class="w-24">
                            <label class="block text-xs text-gray-400 mb-0.5">Ordre section</label>
                            @foreach ($sections as $sIndex => $s)
                                @continue($s['key'] !== $section['key'])
                                <input wire:model="sections.{{ $sIndex }}.order" type="number" class="w-full rounded-md border-gray-300 text-sm">
                            @endforeach
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" wire:click="moveSectionUp('{{ $section['key'] }}')" title="Monter" class="w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 text-gray-500 hover:bg-gray-100">↑</button>
                            <button type="button" wire:click="moveSectionDown('{{ $section['key'] }}')" title="Descendre" class="w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 text-gray-500 hover:bg-gray-100">↓</button>
                            <button type="button" wire:click="removeSection('{{ $section['key'] }}')" wire:confirm="Supprimer cette section ? Ses liens passeront en 'Liens sans section'." title="Supprimer" class="w-7 h-7 flex items-center justify-center rounded-md border border-red-200 text-red-500 hover:bg-red-50">✕</button>
                        </div>
                    </div>

                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-400 uppercase tracking-wide">
                                <th class="text-left px-4 py-2 font-medium">Nom actuel / lien</th>
                                <th class="text-left px-4 py-2 font-medium">Nouvelle etiquette</th>
                                <th class="text-left px-4 py-2 font-medium w-20">Ordre</th>
                                <th class="text-left px-4 py-2 font-medium w-48">Section</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php $hasItems = false; @endphp
                            @foreach ($items as $index => $item)
                                @continue($item['section'] !== $section['key'])
                                @php $hasItems = true; @endphp
                                <tr wire:key="item-row-{{ $item['key'] }}">
                                    <td class="px-4 py-2 text-gray-500">
                                        {{ $item['defaultLabel'] }}
                                        <div class="text-xs text-gray-300">{{ $item['route'] }}</div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input wire:model="items.{{ $index }}.label" type="text" class="w-full rounded-md border-gray-300 text-sm">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input wire:model="items.{{ $index }}.order" type="number" class="w-20 rounded-md border-gray-300 text-sm">
                                    </td>
                                    <td class="px-4 py-2">
                                        <select wire:model="items.{{ $index }}.section" class="w-full rounded-md border-gray-300 text-sm">
                                            <option value="">Sans section</option>
                                            @foreach ($sectionOptions as $opt)
                                                <option value="{{ $opt['key'] }}">{{ $opt['icon'] }} {{ $opt['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                            @if (! $hasItems)
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-xs text-gray-400 italic">Aucun lien dans cette section.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <div class="p-6 border-t border-gray-100 flex justify-end">
        <button type="button" wire:click="save" class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer le menu</button>
    </div>
</div>
