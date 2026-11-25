<div class="py-6 px-4 sm:px-6 lg:px-8" x-data="{
        cols: (() => { const d = { categorie: true, marque: true, fournisseur: true, achat: true, gros: true }; try { return Object.assign(d, JSON.parse(localStorage.getItem('products_cols') || '{}')) } catch (e) { return d } })(),
        toggle(key) { this.cols[key] = !this.cols[key]; try { localStorage.setItem('products_cols', JSON.stringify(this.cols)) } catch (e) {} },
        printUrl() { return '{{ route('products.print') }}?' + new URLSearchParams({ q: this.$wire.search || '', categorie: this.$wire.categoryFilter || '', cols: Object.keys(this.cols).filter(k => this.cols[k]).join(',') }) }
    }">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Produits') }}</h1>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b flex flex-col lg:flex-row lg:items-center justify-between gap-3 print:hidden">
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ __('Liste des produits') }}</h2>
                <p class="text-sm text-gray-400">{{ $products->total() }} produit(s)</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 whitespace-nowrap">Recherche :</span>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Référence, désignation ou code-barres" class="rounded-md border-gray-300 text-sm">
                    <x-barcode-scanner on-scan="onBarcodeSearch" label="Scanner" />
                </div>
                <select wire:model.live="categoryFilter" class="rounded-md border-gray-300 text-sm">
                    <option value="">Toutes les catégories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="p-4 border-b flex flex-wrap items-center gap-2 print:hidden">
            <button wire:click="create" class="flex items-center gap-1.5 bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">
                <span>+</span> Nouveau produit
            </button>
            <button wire:click="exportExcel" class="flex items-center gap-1.5 bg-[#107c41] text-white border border-[#107c41] px-3 py-2 text-sm font-semibold hover:bg-[#0c6634] rounded-lg">
                <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="3" width="20" height="18" rx="3" fill="currentColor" opacity=".25"/><path d="M8 8l8 8M16 8l-8 8" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
                Exporter Excel
            </button>
            <button wire:click="openImport" class="flex items-center gap-1.5 bg-white text-[#107c41] border border-[#107c41] px-3 py-2 rounded-lg text-sm font-semibold hover:bg-[#107c41]/10">
                <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="3" width="20" height="18" rx="3" fill="currentColor" opacity=".25"/><path d="M8 8l8 8M16 8l-8 8" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
                Importer Excel
            </button>
            <a :href="printUrl()" target="_blank" rel="noopener" class="flex items-center gap-1.5 bg-white text-zinc-900 border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-100 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a1 1 0 001 1h8a1 1 0 001-1v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a1 1 0 00-1-1H6a1 1 0 00-1 1zm2 0h6v3H7V4zm6 10v3H7v-5h6v2z" /></svg>
                Imprimer
            </a>

            <div class="relative ms-auto" x-data="{ open: false }">
                <button @click="open = ! open" type="button" class="flex items-center gap-1.5 border border-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a1 1 0 011-1h3a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM8 4a1 1 0 011-1h3a1 1 0 011 1v12a1 1 0 01-1 1H9a1 1 0 01-1-1V4zM13 4a1 1 0 011-1h3a1 1 0 011 1v12a1 1 0 01-1 1h-3a1 1 0 01-1-1V4z" /></svg>
                    Colonnes
                </button>
                <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-20 text-sm p-2 space-y-1">
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" :checked="cols.categorie" @change="toggle('categorie')" class="rounded border-gray-300"> Catégorie
                    </label>
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" :checked="cols.marque" @change="toggle('marque')" class="rounded border-gray-300"> Marque
                    </label>
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" :checked="cols.fournisseur" @change="toggle('fournisseur')" class="rounded border-gray-300"> Fournisseurs
                    </label>
                    @if (auth()->user()->hasPermission('produits_prix_achat'))
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" :checked="cols.achat" @change="toggle('achat')" class="rounded border-gray-300"> Prix Achat
                    </label>
                    @endif
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" :checked="cols.gros" @change="toggle('gros')" class="rounded border-gray-300"> Prix Vente Gros
                    </label>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ref</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Désignation</th>
                    <th x-show="cols.categorie" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                    <th x-show="cols.marque" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marque</th>
                    <th x-show="cols.fournisseur" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseurs</th>
                    @if (auth()->user()->hasPermission('produits_prix_achat'))
                    <th x-show="cols.achat" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Prix Achat</th>
                    @endif
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Prix Vente TTC</th>
                    <th x-show="cols.gros" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Prix Vente Gros</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase print:hidden">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($products as $product)
                    <tr wire:key="prod-{{ $product->id }}">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product->reference }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $product->name }}</td>
                        <td x-show="cols.categorie" class="px-4 py-3 text-sm text-gray-500">{{ $product->category?->name ?? '-' }}</td>
                        <td x-show="cols.marque" class="px-4 py-3 text-sm text-gray-500">{{ $product->brand?->name ?? 'Sans marque' }}</td>
                        <td x-show="cols.fournisseur" class="px-4 py-3 text-sm text-gray-500">{{ $product->fournisseur?->name ?? '-' }}</td>
                        @if (auth()->user()->hasPermission('produits_prix_achat'))
                        <td x-show="cols.achat" class="px-4 py-3 text-sm text-right text-gray-700 whitespace-nowrap">{{ \App\Support\Fmt::money($product->purchase_price) }}</td>
                        @endif
                        <td class="px-4 py-3 text-sm text-right text-gray-700 whitespace-nowrap">{{ \App\Support\Fmt::money($product->sale_price) }}</td>
                        <td x-show="cols.gros" class="px-4 py-3 text-sm text-right text-gray-700">{{ $product->wholesale_price !== null ? \App\Support\Fmt::money($product->wholesale_price) : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            <span class="font-semibold {{ $product->stock_qty <= 0 ? 'text-red-600' : ($product->isLowStock() ? 'text-amber-600' : 'text-green-600') }}">
                                {{ \App\Support\Fmt::qty($product->stock_qty) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right print:hidden">
                            <div class="flex items-center justify-end gap-1.5">
                                <button wire:click="edit({{ $product->id }})" title="Modifier" class="h-8 w-8 flex items-center justify-center rounded-md bg-amber-400 text-white hover:bg-amber-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                                </button>
                                <button wire:click="delete({{ $product->id }})" wire:confirm="Supprimer ce produit ?" title="Supprimer" class="h-8 w-8 flex items-center justify-center rounded-md bg-red-500 text-white hover:bg-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-6 py-8 text-center text-sm text-gray-400">Aucun produit.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4 print:hidden">{{ $products->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">{{ $editingId ? 'Modifier' : 'Nouveau' }} produit</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Référence</label>
                        <input wire:model="reference" type="text" class="w-full rounded-md border-gray-300 text-sm">
                        @error('reference') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code-barres</label>
                        <div class="flex gap-2">
                            <input wire:model="barcode" type="text" class="w-full rounded-md border-gray-300 text-sm">
                            <x-barcode-scanner on-scan="onBarcodeField" label="" />
                        </div>
                        @error('barcode') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <select wire:model="category_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">-- Aucune --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Désignation</label>
                        <input wire:model="name" type="text" class="w-full rounded-md border-gray-300 text-sm">
                        <div class="mt-3 flex items-center gap-3">
                            @if ($image && method_exists($image, 'temporaryUrl'))
                                <img src="{{ $image->temporaryUrl() }}" alt="" class="w-16 h-16 object-cover border border-zinc-200 rounded-lg">
                            @elseif ($imagePath && ! $removeImage)
                                <img src="{{ asset('storage/'.$imagePath) }}" alt="" class="w-16 h-16 object-cover border border-zinc-200 rounded-lg">
                            @endif
                            <label class="cursor-pointer inline-flex items-center gap-1.5 border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-[#fff3c4] rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.66-.9l.82-1.2A2 2 0 0 1 10.07 4h3.86a2 2 0 0 1 1.66.9l.82 1.2a2 2 0 0 0 1.66.9H19a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><circle cx="12" cy="13" r="3"/></svg>
                                {{ ($imagePath && ! $removeImage) || $image ? 'Changer la photo' : 'Ajouter une photo' }}
                                <input type="file" wire:model="image" accept="image/*" class="sr-only">
                            </label>
                            @if (($imagePath && ! $removeImage) || $image)
                                <button type="button" wire:click="$set('image', null); $set('removeImage', true)" class="text-xs text-[#b42318] underline">Retirer</button>
                            @endif
                            <span wire:loading wire:target="image" class="text-xs text-gray-500">Envoi…</span>
                        </div>
                        @error('image') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        @error('name') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Marque</label>
                        <select wire:model="brand_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">Sans marque</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
                        <select wire:model="fournisseur_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">-- Aucun --</option>
                            @foreach ($fournisseurs as $f)
                                <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unité</label>
                        <input wire:model="unit" type="text" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Seuil d'alerte</label>
                        <input wire:model="stock_alert_threshold" type="number" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    @if (auth()->user()->hasPermission('produits_prix_achat'))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix d'achat</label>
                        <input wire:model="purchase_price" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix de vente TTC</label>
                        <input wire:model="sale_price" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix de vente Gros</label>
                        <input wire:model="wholesale_price" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">TVA du produit (%)</label>
                        <input wire:model="tva_rate" type="number" step="0.01" min="0" max="100" list="tva-rates-product" placeholder="Par défaut : {{ \App\Support\DocTotals::label(\App\Models\Societe::current()->tva_rate_default) }} %" class="w-full rounded-md border-gray-300 text-sm">
                        <datalist id="tva-rates-product">@foreach (\App\Support\Countries::for(\App\Models\Societe::current())['rates'] ?? [] as $r)<option value="{{ $r }}"></option>@endforeach</datalist>
                        @error('tva_rate') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock {{ $editingId ? '' : 'initial' }}</label>
                        <input wire:model="stock_qty" type="number" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showImportModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="$set('showImportModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-2 text-gray-900">Importer des produits (Excel)</h2>
                <p class="text-sm text-gray-500 mb-3">Fichier Excel (.xlsx), LibreOffice (.ods) ou CSV. Le plus simple : cliquez sur <button type="button" wire:click="exportExcel" class="underline font-semibold text-gray-800">Exporter Excel</button>, complétez le fichier puis importez-le. Un produit est retrouvé par sa <b>référence</b> ; s'il n'existe pas, il est créé. Une case vide ne change rien.</p>
                <input wire:model="importFile" type="file" accept=".xlsx,.ods,.csv" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @error('importFile') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror
                <div wire:loading wire:target="importFile" class="text-xs text-gray-400">Téléversement...</div>
                <div wire:loading wire:target="importExcel" class="text-xs text-gray-500">Import en cours...</div>
                @if ($importReport)
                    <div class="mt-3 rounded-lg border px-3 py-2 text-sm {{ $importReport['created'] || $importReport['updated'] ? 'border-green-200 bg-green-50 text-green-800' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
                        @if ($importReport['empty'])
                            Aucune ligne trouvée dans le fichier.
                        @else
                            <b>{{ $importReport['created'] }}</b> créé(s), <b>{{ $importReport['updated'] }}</b> mis à jour{{ $importReport['errors'] ? ', '.count($importReport['errors']).' ignoré(s)' : '' }}.
                        @endif
                        @if ($importReport['errors'])
                            <ul class="mt-1 list-disc ps-5 text-xs">
                                @foreach (array_slice($importReport['errors'], 0, 6) as $err)<li>{{ $err }}</li>@endforeach
                                @if (count($importReport['errors']) > 6)<li>…</li>@endif
                            </ul>
                        @endif
                        @if ($importReport['unknown'])
                            <p class="mt-1 text-xs">Colonnes non reconnues (ignorées) : {{ implode(', ', array_slice($importReport['unknown'], 0, 6)) }}</p>
                        @endif
                    </div>
                @endif
                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showImportModal', false)" class="px-4 py-2 text-sm text-gray-600">{{ $importReport ? 'Fermer' : 'Annuler' }}</button>
                    <button wire:click="importExcel" wire:loading.attr="disabled" class="!bg-[#107c41] hover:!bg-[#0c6634] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Importer</button>
                </div>
            </div>
        </div>
    @endif
</div>
