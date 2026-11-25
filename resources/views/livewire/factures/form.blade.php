<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $facture ? 'Modifier' : 'Nouvelle' }} Facture</h1>
        <a href="{{ route('factures.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">&larr; Retour</a>
    </div>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    @if (! $facture)
        <div class="bg-white shadow rounded-lg p-4 mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher une facture existante par numéro</label>
            <form wire:submit.prevent="searchByNumero" class="flex gap-2">
                <input wire:model="numeroSearch" type="text" placeholder="N&deg; de facture..." class="w-full sm:w-64 rounded-md border-gray-300 text-sm">
                <button type="submit" class="shrink-0 bg-gray-100 text-gray-700 px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-200">Rechercher</button>
                @if ($numeroSearchPerformed)
                    <button type="button" wire:click="clearNumeroSearch" title="Effacer" class="shrink-0 px-2 text-gray-400 hover:text-gray-600">&times;</button>
                @endif
            </form>

            @if ($numeroSearchPerformed)
                <div class="mt-3 border rounded-md divide-y divide-gray-100 max-h-56 overflow-y-auto">
                    @forelse ($numeroSearchResults as $result)
                        <a href="{{ route('factures.edit', $result['id']) }}" wire:navigate class="flex items-center justify-between px-3 py-2 text-sm hover:bg-[#fff3c4]">
                            <span>
                                <span class="font-medium text-gray-900">{{ $result['numero'] }}</span>
                                <span class="text-gray-500"> &middot; {{ $result['client'] }} &middot; {{ $result['date'] }}</span>
                            </span>
                            <span class="text-gray-700">{{ \App\Support\Fmt::money($result['total_ttc']) }}</span>
                        </a>
                    @empty
                        <div class="px-3 py-2 text-sm text-gray-400">Aucune facture trouvee pour "{{ $numeroSearch }}".</div>
                    @endforelse
                </div>
            @endif
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                <select wire:model="client_id" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">-- Choisir --</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('client_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input wire:model="date" type="date" class="w-full rounded-md border-gray-300 text-sm">
                @error('date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Échéance</label>
                <input wire:model="due_date" type="date" class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex items-end gap-6">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select wire:model="status" class="w-full rounded-md border-gray-300 text-sm">
                        <option value="brouillon">Brouillon</option>
                        <option value="envoyee">Envoyee</option>
                        <option value="partiellement_payee">Partiellement payée</option>
                        <option value="payee">Payee</option>
                        <option value="en_retard">En retard</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            @if ($bon_livraison_id)
                <div class="text-xs text-gray-400">Generee a partir du bon de livraison #{{ $bon_livraison_id }}</div>
            @else
                <div></div>
            @endif
            <div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <span class="text-sm text-gray-600">Tarif Normal</span>
                    <span class="relative inline-block w-10 h-5">
                        <input wire:model.live="tarif_gros" type="checkbox" class="peer sr-only">
                        <span class="absolute inset-0 rounded-full bg-zinc-300 peer-checked:bg-[#ffcc00] transition-colors"></span>
                        <span class="absolute left-0.5 top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm text-gray-600">Gros</span>
                </label>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-700">Lignes</label>
                <button type="button" wire:click="addItem" class="text-sm text-indigo-600 hover:underline">+ Ajouter une ligne</button>
            </div>
            @error('items') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

            <div class="border rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase min-w-[12rem]">Produit</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">Quantité</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-32">Prix U. HT</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-20">TVA %</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-32">Total</th>
                            @if (auth()->user()->hasPermission('marges'))
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">Marge</th>
                            @endif
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($items as $index => $item)
                            <tr wire:key="item-{{ $index }}">
                                <td class="px-4 py-2">
                                    <div class="flex gap-1">
                                        <button type="button" wire:click="openProductModal({{ $index }})" title="Ajouter un produit" class="shrink-0 h-9 w-9 flex items-center justify-center rounded-md border border-gray-300 text-gray-600 hover:bg-gray-50">+</button>
                                        <div class="relative flex-1"
                                            x-data="{
                                                query: @js(optional($products->firstWhere('id', $item['product_id'] ?: null))->name ?? ''),
                                                allProducts: @js($products->map(fn ($p) => [
                                                    'id' => $p->id,
                                                    'name' => $p->name,
                                                    'reference' => $p->reference ?? '',
                                                    'search' => mb_strtolower($p->name.' '.($p->reference ?? '').' '.($p->brand->name ?? '')),
                                                ])->values()),
                                                open: false,
                                                highlighted: 0,
                                                menuStyle: '',
                                                get filtered() {
                                                    const q = this.query.toLowerCase();
                                                    return q === '' ? this.allProducts : this.allProducts.filter(p => p.search.includes(q));
                                                },
                                                openMenu() {
                                                    this.open = true;
                                                    this.highlighted = 0;
                                                    this.$nextTick(() => {
                                                        const rect = this.$refs.productInput.getBoundingClientRect();
                                                        this.menuStyle = 'position:fixed; top:' + (rect.bottom + 4) + 'px; left:' + rect.left + 'px; width:' + rect.width + 'px;';
                                                    });
                                                },
                                                moveDown() {
                                                    if (! this.open) { this.openMenu(); return; }
                                                    this.highlighted = Math.min(this.highlighted + 1, this.filtered.length - 1);
                                                    this.scrollToHighlighted();
                                                },
                                                moveUp() {
                                                    if (! this.open) { this.openMenu(); return; }
                                                    this.highlighted = Math.max(this.highlighted - 1, 0);
                                                    this.scrollToHighlighted();
                                                },
                                                scrollToHighlighted() {
                                                    this.$nextTick(() => {
                                                        const el = this.$refs['opt' + this.highlighted];
                                                        if (el) el.scrollIntoView({ block: 'nearest' });
                                                    });
                                                },
                                                selectHighlighted() {
                                                    if (! this.open || ! this.filtered.length) { return; }
                                                    const p = this.filtered[this.highlighted];
                                                    if (! p) { return; }
                                                    this.query = p.name;
                                                    this.open = false;
                                                    $wire.set('items.{{ $index }}.product_id', p.id);
                                                }
                                            }">
                                            <input x-ref="productInput" type="text" x-model="query" @focus="openMenu()" @input="openMenu()"
                                                @keydown.arrow-down.prevent="moveDown()" @keydown.arrow-up.prevent="moveUp()"
                                                @keydown.enter.prevent="selectHighlighted()" @keydown.escape="open = false"
                                                placeholder="Rechercher référence, désignation, marque..." class="w-full rounded-md border-gray-300 text-sm" autocomplete="off">
                                            <template x-teleport="body">
                                                <div x-show="open" @click.outside="open = false" x-cloak :style="menuStyle"
                                                    class="z-50 bg-white border border-gray-200 rounded-md shadow-lg max-h-56 overflow-y-auto">
                                                    <template x-for="(p, idx) in filtered" :key="p.id">
                                                        <div :x-ref="'opt' + idx"
                                                            @click="query = p.name; open = false; $wire.set('items.{{ $index }}.product_id', p.id)"
                                                            @mouseenter="highlighted = idx"
                                                            :class="{ 'bg-[#fff3c4] font-medium': idx === highlighted }"
                                                            class="px-3 py-2 text-sm hover:bg-[#fff3c4] cursor-pointer">
                                                            <span x-text="p.name"></span>
                                                            <span class="text-gray-400 text-xs" x-text="p.reference"></span>
                                                        </div>
                                                    </template>
                                                    <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-400">Aucun produit.</div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    @error("items.{$index}.product_id") <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm text-right">
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm text-right">
                                </td>
                                <td class="px-1 py-2">
                                    @php $rateOpts = collect($this->vatRateOptions())->push($this->lineRate($item))->map(fn ($r) => (float) $r)->unique()->sortDesc(); @endphp
                                    <select wire:model.live="items.{{ $index }}.tva_rate" class="w-full min-w-[4.75rem] rounded-md border-gray-300 text-sm ps-2 pe-7">
                                        @foreach ($rateOpts as $r)<option value="{{ \App\Support\DocTotals::key($r) }}">{{ \App\Support\DocTotals::label($r) }}</option>@endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-2 text-right text-sm text-gray-900">
                                    {{ \App\Support\Fmt::money((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)) }}
                                </td>
                                @if (auth()->user()->hasPermission('marges'))
                                <td class="px-4 py-2 text-right text-sm text-green-700">
                                    @php
                                        $itemProduct = $products->firstWhere('id', $item['product_id'] ?: null);
                                        $itemMarge = ((float) ($item['unit_price'] ?? 0) - (float) ($itemProduct->purchase_price ?? 0)) * (float) ($item['quantity'] ?? 0);
                                    @endphp
                                    {{ \App\Support\Fmt::money($itemMarge) }}
                                </td>
                                @endif
                                <td class="px-4 py-2 text-center">
                                    <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700">&times;</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
</div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between gap-6">
            <div class="w-full sm:w-72">
                <label class="block text-sm font-medium text-gray-700 mb-1">Remise</label>
                <div class="flex gap-2">
                    <select wire:model.live="remise_type" class="rounded-md border-gray-300 text-sm">
                        <option value="percent">%</option>
                        <option value="fixed">{{ \App\Models\Societe::current()->currency }}</option>
                    </select>
                    <input wire:model.live="remise_value" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                @error('remise_value') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-4">Objet <span class="text-gray-400 font-normal">(facultatif, imprimé sous le titre)</span></label>
                <input wire:model="objet" type="text" maxlength="255" placeholder="Ex. Fourniture d’une échelle métallique" class="w-full rounded-md border-gray-300 text-sm">

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-4">Notes</label>
                <textarea wire:model="notes" rows="2" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                @if ($devis_numero)
                <p class="mt-4 text-sm text-gray-600">Facture établie à partir du devis <strong>{{ $devis_numero }}</strong>. Le stock sortira à l’enregistrement.</p>
                @endif
                <label class="block text-sm font-medium text-gray-700 mb-1 mt-4">Adresse de livraison <span class="text-gray-400 font-normal">(si différente de celle du client)</span></label>
                <textarea wire:model="adresse_livraison" rows="2" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                @if ((\App\Models\Societe::current()->country_code ?? '') === 'FR')
                <label class="block text-sm font-medium text-gray-700 mb-1 mt-4">Nature des opérations</label>
                <select wire:model="nature_operation" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">—</option>
                    <option value="biens">Livraison de biens</option>
                    <option value="services">Prestation de services</option>
                    <option value="mixte">Biens et services</option>
                </select>
                @endif
            </div>

            <div class="w-full sm:w-64 space-y-2">
                @if ($this->montantRemise > 0)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Total brut HT</span>
                        <span class="font-medium text-gray-900">{{ \App\Support\Fmt::money($this->brutHt) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Remise</span>
                        <span class="font-medium text-red-600">- {{ \App\Support\Fmt::money($this->montantRemise) }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Total HT</span>
                    <span class="font-medium text-gray-900">{{ \App\Support\Fmt::money($this->totalHt) }}</span>
                </div>
                @foreach ($this->vat['groups'] as $g)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">TVA {{ \App\Support\DocTotals::label($g['rate']) }} %@if (count($this->vat['groups']) > 1) <span class="text-xs">sur {{ \App\Support\Fmt::money($g['base']) }}</span>@endif</span>
                    <span class="font-medium text-gray-900">{{ \App\Support\Fmt::money($g['tva']) }}</span>
                </div>
                @endforeach
                <div class="flex items-center justify-between text-base font-bold border-t pt-2">
                    <span>Total TTC</span>
                    <span>{{ \App\Support\Fmt::money($this->totalTtc) }}</span>
                </div>
                @if (auth()->user()->hasPermission('marges'))
                <div class="flex items-center justify-between text-sm pt-1">
                    <span class="text-gray-500">Marge totale</span>
                    <span class="font-medium text-green-700">{{ \App\Support\Fmt::money($this->margeTotale) }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t">
            <a href="{{ route('factures.index') }}" wire:navigate class="px-4 py-2 text-sm text-gray-600">Annuler</a>
            <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
        </div>
    </div>

    @if ($showProductModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="$set('showProductModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">Nouveau produit</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Référence</label>
                        <input wire:model="new_product_reference" type="text" class="w-full rounded-md border-gray-300 text-sm">
                        @error('new_product_reference') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code-barres</label>
                        <div class="flex gap-2">
                            <input wire:model="new_product_barcode" type="text" class="w-full rounded-md border-gray-300 text-sm">
                            <x-barcode-scanner on-scan="onNewProductBarcodeScanned" label="" />
                        </div>
                        @error('new_product_barcode') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <select wire:model="new_product_category_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">-- Aucune --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Désignation</label>
                        <input wire:model="new_product_name" type="text" class="w-full rounded-md border-gray-300 text-sm">
                        @error('new_product_name') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Marque</label>
                        <select wire:model="new_product_brand_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">Sans marque</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
                        <select wire:model="new_product_fournisseur_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">-- Aucun --</option>
                            @foreach ($fournisseurs as $f)
                                <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unité</label>
                        <input wire:model="new_product_unit" type="text" class="w-full rounded-md border-gray-300 text-sm">
                        @error('new_product_unit') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Seuil d'alerte</label>
                        <input wire:model="new_product_stock_alert_threshold" type="number" class="w-full rounded-md border-gray-300 text-sm">
                        @error('new_product_stock_alert_threshold') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    @if (auth()->user()->hasPermission('produits_prix_achat'))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix d'achat</label>
                        <input wire:model="new_product_purchase_price" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm">
                        @error('new_product_purchase_price') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix de vente TTC</label>
                        <input wire:model="new_product_sale_price" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm">
                        @error('new_product_sale_price') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix de vente Gros</label>
                        <input wire:model="new_product_wholesale_price" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm">
                        @error('new_product_wholesale_price') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock initial</label>
                        <input wire:model="new_product_stock_qty" type="number" class="w-full rounded-md border-gray-300 text-sm">
                        @error('new_product_stock_qty') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showProductModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button wire:click="saveProduct" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Créer et ajouter</button>
                </div>
            </div>
        </div>
    @endif
</div>
