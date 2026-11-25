<?php
namespace App\Livewire\Devis;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Product;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Form extends Component
{
    use RequiresPermission;
    use \App\Livewire\Concerns\CopilotPrefill;
    use \App\Livewire\Concerns\HasLineVat;

    public ?Devis $devis = null;

    #[Validate('required|exists:clients,id')]
    public $client_id = '';

    #[Validate('required|date')]
    public $date;

    public $tva_rate = 20;

    public $status = 'draft';

    public $valid_until = '';

    public $notes = '';

    #[Validate('required|in:percent,fixed')]
    public $remise_type = 'percent';

    #[Validate('nullable|numeric|min:0')]
    public $remise_value = 0;

    public array $items = [];

    public bool $justSaved = false;

    public bool $tarif_gros = false;

    // --- Recherche d'un devis existant par numero ---
    public string $numeroSearch = '';

    public array $numeroSearchResults = [];

    public bool $numeroSearchPerformed = false;

    // --- Quick-create: Client ---
    public bool $showClientModal = false;

    #[Validate('required|string|max:255')]
    public $new_client_name = '';

    public $new_client_phone = '';

    public $new_client_email = '';

    // --- Quick-create: Product ---
    public bool $showProductModal = false;

    public ?int $productModalItemIndex = null;

    public $new_product_reference = '';

    public $new_product_barcode = '';

    public ?int $new_product_category_id = null;

    public $new_product_name = '';

    public ?int $new_product_brand_id = null;

    public ?int $new_product_fournisseur_id = null;

    public $new_product_unit = 'piece';

    public $new_product_stock_alert_threshold = null;

    public $new_product_purchase_price = 0;

    public $new_product_sale_price = 0;

    public $new_product_wholesale_price = null;

    public $new_product_stock_qty = 0;

    public function mount($devisId = null)
    {
        $this->requirePermission($devisId ? 'devis_edition' : 'devis_creer');

        $this->date = now()->format('Y-m-d');

        if ($devisId) {
            $this->devis = Devis::with('items')->findOrFail($devisId);
            $this->client_id = $this->devis->client_id;
            $this->date = $this->devis->date->format('Y-m-d');
            $this->valid_until = optional($this->devis->valid_until)->format('Y-m-d') ?? '';
            $this->tva_rate = $this->devis->tva_rate;
            $this->status = $this->devis->status;
            $this->notes = $this->devis->notes ?? '';
            $this->remise_type = $this->devis->remise_type ?? 'percent';
            $this->remise_value = $this->devis->remise_value ?? 0;
            $this->items = $this->devis->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
                'tva_rate' => $i->tva_rate !== null ? (float) $i->tva_rate : (float) $this->tva_rate,
                'remise_type' => $i->remise_type ?? 'percent',
                'remise_value' => (float) ($i->remise_value ?? 0),
            ])->toArray();
        } else {
            $this->tva_rate = $this->defaultVatRate();
            $this->valid_until = now()->addDays((int) (\App\Models\Societe::current()->devis_validite_jours ?: 30))->format('Y-m-d');
            $this->addItem();
        }
        $this->applyCopilotPrefill();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '', 'quantity' => 1, 'unit_price' => 0, 'tva_rate' => (float) $this->tva_rate,
            'remise_type' => 'percent', 'remise_value' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /**
     * Returns the unit price to use for this product, respecting the
     * "Tarif: En Gros" toggle (falls back to the retail price when no
     * wholesale price is set on the product).
     */
    protected function priceForProduct(Product $product): float
    {
        // Catalogue prices (vente / gros) are TTC; document lines are HT and the
        // TVA is added on the total, so convert here (2026-11-22, owner decision).
        $ttc = ($this->tarif_gros && $product->wholesale_price !== null)
            ? (float) $product->wholesale_price
            : (float) $product->sale_price;

        return round($ttc / (1 + $this->productRate($product) / 100), 2);
    }

    public function updatedItems($value, $key)
    {
        [$index, $field] = explode('.', $key);
        if ($field === 'product_id' && $value) {
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['unit_price'] = $this->priceForProduct($product);
                $this->items[$index]['tva_rate'] = $this->productRate($product);
            }
        }
    }

    /**
     * When the "Tarif: En Gros" toggle changes, re-price every line that
     * already has a product selected (not just future selections).
     */
    public function updatedTarifGros(): void
    {
        $productIds = collect($this->items)->pluck('product_id')->filter()->unique()->values();

        if ($productIds->isEmpty()) {
            return;
        }

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($this->items as $index => $item) {
            if (empty($item['product_id']) || ! $products->has($item['product_id'])) {
                continue;
            }

            $this->items[$index]['unit_price'] = $this->priceForProduct($products[$item['product_id']]);

            $this->items[$index]['tva_rate'] = $this->productRate($products[$item['product_id']]);
        }
    }

    public function onBarcodeScanned(string $code): void
    {
        $product = Product::where('barcode', $code)->first();

        if (! $product) {
            $this->addError('items', "Aucun produit trouve pour le code-barres {$code}.");

            return;
        }

        foreach ($this->items as $index => $item) {
            if ((int) ($item['product_id'] ?? 0) === $product->id) {
                $this->items[$index]['quantity'] = (float) $this->items[$index]['quantity'] + 1;

                return;
            }
        }

        foreach ($this->items as $index => $item) {
            if (empty($item['product_id'])) {
                $this->items[$index]['product_id'] = $product->id;
                $this->items[$index]['unit_price'] = $this->priceForProduct($product);
                $this->items[$index]['tva_rate'] = $this->productRate($product);

                return;
            }
        }

        $this->items[] = [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $this->priceForProduct($product),
            'tva_rate' => $this->productRate($product),
            'remise_type' => 'percent',
            'remise_value' => 0,
        ];
    }

    // --- Recherche d'un devis existant par numero ---
    public function searchByNumero()
    {
        $this->numeroSearchPerformed = true;

        if (trim($this->numeroSearch) === '') {
            $this->numeroSearchResults = [];

            return;
        }

        $this->numeroSearchResults = Devis::query()
            ->with('client')
            ->where('numero', 'like', '%'.$this->numeroSearch.'%')
            ->latest('date')
            ->limit(10)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'numero' => $d->numero,
                'client' => $d->client->name ?? '-',
                'date' => $d->date->format('d/m/Y'),
                'total_ttc' => $d->total_ttc,
            ])
            ->toArray();
    }

    public function clearNumeroSearch()
    {
        $this->numeroSearch = '';
        $this->numeroSearchResults = [];
        $this->numeroSearchPerformed = false;
    }

    // --- Quick-create client ---
    public function openClientModal()
    {
        $this->reset(['new_client_name', 'new_client_phone', 'new_client_email']);
        $this->resetValidation();
        $this->showClientModal = true;
    }

    public function saveClient()
    {
        $this->validate([
            'new_client_name' => 'required|string|max:255',
            'new_client_phone' => 'nullable|string|max:50',
            'new_client_email' => 'nullable|email|max:255',
        ]);

        $client = Client::create([
            'name' => $this->new_client_name,
            'phone' => $this->new_client_phone ?: null,
            'email' => $this->new_client_email ?: null,
        ]);

        $this->client_id = $client->id;
        $this->showClientModal = false;
        session()->flash('success', 'Client "'.$client->name.'" cree et selectionne.');
    }

    public function getSelectedClientNameProperty()
    {
        if (! $this->client_id) {
            return '';
        }

        return Client::find($this->client_id)?->name ?? '';
    }

    // --- Quick-create product ---
    public function openProductModal($itemIndex = null)
    {
        $this->productModalItemIndex = $itemIndex;
        $this->reset([
            'new_product_reference', 'new_product_barcode', 'new_product_category_id',
            'new_product_name', 'new_product_brand_id', 'new_product_fournisseur_id',
            'new_product_unit', 'new_product_stock_alert_threshold', 'new_product_purchase_price',
            'new_product_sale_price', 'new_product_wholesale_price', 'new_product_stock_qty',
        ]);
        $this->new_product_unit = 'piece';
        $this->resetValidation();
        $this->showProductModal = true;
    }

    /**
     * Called by the barcode-scanner component inside the "Nouveau produit" modal.
     */
    public function onNewProductBarcodeScanned(string $code): void
    {
        $this->new_product_barcode = $code;
    }

    public function saveProduct()
    {
        $this->validate([
            'new_product_reference' => ['required', 'string', 'max:255', \App\Support\Tenant::unique('products', 'reference')],
            'new_product_barcode' => ['nullable', 'string', 'max:255', \App\Support\Tenant::unique('products', 'barcode')],
            'new_product_name' => 'required|string|max:255',
            'new_product_unit' => 'required|string|max:50',
            'new_product_purchase_price' => 'required|numeric|min:0',
            'new_product_sale_price' => 'required|numeric|min:0',
            'new_product_wholesale_price' => 'nullable|numeric|min:0',
            'new_product_stock_qty' => 'required|integer|min:0',
            'new_product_stock_alert_threshold' => 'nullable|integer|min:0',
        ]);

        $product = Product::create([
            'reference' => $this->new_product_reference,
            'barcode' => $this->new_product_barcode ?: null,
            'name' => $this->new_product_name,
            'category_id' => $this->new_product_category_id,
            'brand_id' => $this->new_product_brand_id,
            'fournisseur_id' => $this->new_product_fournisseur_id,
            'unit' => $this->new_product_unit,
            'purchase_price' => $this->new_product_purchase_price,
            'sale_price' => $this->new_product_sale_price,
            'wholesale_price' => $this->new_product_wholesale_price ?: null,
            'stock_qty' => $this->new_product_stock_qty,
            'stock_alert_threshold' => $this->new_product_stock_alert_threshold ?: null,
        ]);

        if (! is_null($this->productModalItemIndex) && isset($this->items[$this->productModalItemIndex])) {
            $this->items[$this->productModalItemIndex]['product_id'] = $product->id;
            $this->items[$this->productModalItemIndex]['unit_price'] = $this->priceForProduct($product);
            $this->items[$this->productModalItemIndex]['tva_rate'] = $this->productRate($product);
        } else {
            $this->items[] = [
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => $this->priceForProduct($product),
                'tva_rate' => $this->productRate($product),
                'remise_type' => 'percent',
                'remise_value' => 0,
            ];
        }

        $this->showProductModal = false;
        session()->flash('success', 'Produit "'.$product->name.'" cree et ajoute.');
    }

    public function getBrutHtProperty()
    {
        return collect($this->items)->sum(fn ($i) => (float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0));
    }

    /**
     * The discount amount of a single line (per-line "Reduction" column).
     */
    public function ligneRemiseMontant(array $item): float
    {
        $brutLigne = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
        $type = $item['remise_type'] ?? 'percent';
        $value = (float) ($item['remise_value'] ?? 0);

        if ($type === 'percent') {
            return round($brutLigne * $value / 100, 2);
        }

        return min($value, $brutLigne);
    }

    /**
     * What a line is actually worth after its own discount ("Total ligne").
     */
    public function ligneTotal(array $item): float
    {
        $brutLigne = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);

        return $brutLigne - $this->ligneRemiseMontant($item);
    }

    /**
     * Sum of every line's own discount.
     */
    public function getLigneRemiseTotalProperty()
    {
        return collect($this->items)->sum(fn ($i) => $this->ligneRemiseMontant($i));
    }

    /**
     * The global "Autre reduction", applied on top of the lines' own
     * discounts (on what remains after them).
     */
    public function getRemiseGlobaleProperty()
    {
        $apresLignes = $this->brutHt - $this->ligneRemiseTotal;

        if ($this->remise_type === 'percent') {
            return round($apresLignes * ((float) ($this->remise_value ?: 0)) / 100, 2);
        }

        return min((float) ($this->remise_value ?: 0), $apresLignes);
    }

    /**
     * Combined discount (lines + global) shown as a single "Reduction"
     * total, matching the single line in the totals panel.
     */
    public function getMontantRemiseProperty()
    {
        return $this->ligneRemiseTotal + $this->remiseGlobale;
    }

    public function getTotalHtProperty()
    {
        return $this->brutHt - $this->montantRemise;
    }

    public function getTotalTtcProperty()
    {
        return $this->vat['ttc'];
    }

    public function getMargeTotaleProperty()
    {
        $productIds = collect($this->items)->pluck('product_id')->filter()->unique()->values();

        if ($productIds->isEmpty()) {
            return 0;
        }

        $purchasePrices = Product::whereIn('id', $productIds)->pluck('purchase_price', 'id');

        return collect($this->items)->sum(function ($i) use ($purchasePrices) {
            if (empty($i['product_id'])) {
                return 0;
            }

            $purchase = (float) ($purchasePrices[$i['product_id']] ?? 0);
            $margeBrute = ((float) ($i['unit_price'] ?? 0) - $purchase) * (float) ($i['quantity'] ?? 0);

            return $margeBrute - $this->ligneRemiseMontant($i);
        });
    }

    public function save()
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date',
            'remise_type' => 'required|in:percent,fixed',
            'remise_value' => 'nullable|numeric|min:0',
        ]);

        if (empty($this->items)) {
            $this->addError('items', 'Ajoutez au moins une ligne.');

            return;
        }

        $this->validate([
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tva_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $data = [
            'client_id' => $this->client_id,
            'date' => $this->date,
            'valid_until' => $this->valid_until ?: null,
            'tva_rate' => $this->tva_rate,
            'status' => $this->status,
            'notes' => $this->notes,
            'remise_type' => $this->remise_type,
            'remise_value' => $this->remise_value ?: 0,
        ];

        if ($this->devis) {
            $this->devis->update($data);
            $this->devis->items()->delete();
        } else {
            $data['numero'] = \App\Support\Tenant::nextNumero(Devis::class, 'DEV-');
            $this->devis = Devis::create($data);
        }

        foreach ($this->items as $item) {
            $this->devis->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tva_rate' => $this->lineRate($item),
                'remise_type' => $item['remise_type'] ?? 'percent',
                'remise_value' => $item['remise_value'] ?? 0,
                'total' => $this->ligneTotal($item),
            ]);
        }

        $this->devis->recalculateTotals();

        $this->justSaved = true;
        session()->flash('success', 'Devis enregistré.');
    }

    public function goToIndex()
    {
        return $this->redirect(route('devis.index'), navigate: true);
    }

    public function convertToBonLivraison()
    {
        if (! $this->devis) {
            return;
        }

        $bl = $this->devis->fresh()->toBonLivraison();

        return $this->redirect(route('bon-livraisons.edit', $bl->id), navigate: true);
    }

    /** Invoice straight from the quote (no delivery note): opens the invoice form pre-filled; stock moves when it is saved. */
    public function convertToFacture()
    {
        if (! $this->devis) {
            return;
        }
        $this->requirePermission('devis_valider');

        return $this->redirect(route('factures.create').'?devis='.$this->devis->id, navigate: true);
    }

    public function render()
    {
        return view('livewire.devis.form', [
            'clients' => Client::orderBy('name')->get(),
            'products' => Product::with('brand')->orderBy('name')->get(),
            'categories' => \App\Models\Category::orderBy('name')->get(),
            'brands' => \App\Models\Brand::orderBy('name')->get(),
            'fournisseurs' => \App\Models\Fournisseur::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
