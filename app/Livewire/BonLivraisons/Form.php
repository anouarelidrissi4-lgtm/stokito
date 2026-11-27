<?php
namespace App\Livewire\BonLivraisons;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\BonLivraison;
use App\Models\Client;
use App\Models\Product;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Form extends Component
{
    use RequiresPermission;
    use \App\Livewire\Concerns\CopilotPrefill;
    use \App\Livewire\Concerns\HasLineVat;

    public ?BonLivraison $bonLivraison = null;

    #[Validate('required|exists:clients,id')]
    public $client_id = '';

    #[Validate('required|date')]
    public $date;

    public $show_prices = true;

    public $status = 'draft';

    public $tva_rate = 20;

    #[Validate('required|in:percent,fixed')]
    public $remise_type = 'percent';

    #[Validate('nullable|numeric|min:0')]
    public $remise_value = 0;

    /* ---- payments on the delivery note (2026-11-26) ---- */
    public string $payGroup = '';          // '' closed, 'cash' or 'papier'

    public $payMode = 'especes';

    public $payMontant = null;

    public $payDate = null;

    public $payCompte = null;

    public $payBanque = '';

    public $payNumero = '';

    public $payEcheance = null;

    public $payTireur = '';

    public $payNotes = '';

    public array $pendingPayments = [];    // payments typed before the delivery note exists

    public array $items = [];

    public bool $tarif_gros = false;

    // --- Recherche d'un BL existant par numero ---
    public string $numeroSearch = '';

    public array $numeroSearchResults = [];

    public bool $numeroSearchPerformed = false;

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

    public function mount($bonLivraisonId = null)
    {
        $this->requirePermission($bonLivraisonId ? 'bl_edition' : 'bl_creer');

        $this->date = now()->format('Y-m-d');

        if ($bonLivraisonId) {
            $this->bonLivraison = BonLivraison::with('items')->findOrFail($bonLivraisonId);
            $this->client_id = $this->bonLivraison->client_id;
            $this->date = $this->bonLivraison->date->format('Y-m-d');
            $this->show_prices = $this->bonLivraison->show_prices;
            $this->status = $this->bonLivraison->status;
            $this->tva_rate = $this->bonLivraison->tva_rate ?? 20;
            $this->remise_type = $this->bonLivraison->remise_type ?? 'percent';
            $this->remise_value = $this->bonLivraison->remise_value ?? 0;
            $this->items = $this->bonLivraison->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
                'tva_rate' => $i->tva_rate !== null ? (float) $i->tva_rate : (float) $this->tva_rate,
            ])->toArray();
        } else {
            $this->tva_rate = $this->defaultVatRate();
            $this->addItem();
        }
        $this->applyCopilotPrefill();
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'quantity' => 1, 'unit_price' => 0, 'tva_rate' => (float) $this->tva_rate];
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

    /**
     * Called by the barcode-scanner component: find the product and add/increment a line.
     */
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
        ];
    }

    // --- Recherche d'un BL existant par numero ---
    public function searchByNumero()
    {
        $this->numeroSearchPerformed = true;

        if (trim($this->numeroSearch) === '') {
            $this->numeroSearchResults = [];

            return;
        }

        $this->numeroSearchResults = BonLivraison::query()
            ->with('client')
            ->where('numero', 'like', '%'.$this->numeroSearch.'%')
            ->latest('date')
            ->limit(10)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'numero' => $b->numero,
                'client' => $b->client->name ?? '-',
                'date' => $b->date->format('d/m/Y'),
                'total' => $b->total,
            ])
            ->toArray();
    }

    public function clearNumeroSearch()
    {
        $this->numeroSearch = '';
        $this->numeroSearchResults = [];
        $this->numeroSearchPerformed = false;
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
        $this->new_product_name = trim((string) $this->new_product_name);
        $this->validate([
            'new_product_reference' => ['required', 'string', 'max:255', \App\Support\Tenant::unique('products', 'reference')],
            'new_product_barcode' => ['nullable', 'string', 'max:255', \App\Support\Tenant::unique('products', 'barcode')],
            'new_product_name' => ['required', 'string', 'max:255', \App\Models\Product::uniqueNameRule()],
            'new_product_unit' => 'required|string|max:50',
            'new_product_purchase_price' => 'required|numeric|min:0',
            'new_product_sale_price' => 'required|numeric|min:0',
            'new_product_wholesale_price' => 'nullable|numeric|min:0',
            'new_product_stock_qty' => 'required|integer|min:0',
            'new_product_stock_alert_threshold' => 'nullable|integer|min:0',
        ], ['new_product_reference.unique' => 'Cette référence existe déjà.', 'new_product_name.unique' => 'Un produit porte déjà ce nom.', 'new_product_barcode.unique' => 'Ce code-barres est déjà utilisé par un autre produit.']);

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
            ];
        }

        $this->showProductModal = false;
        session()->flash('success', 'Produit "'.$product->name.'" cree et ajoute.');
    }

    public function getBrutHtProperty()
    {
        return collect($this->items)->sum(fn ($i) => (float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0));
    }

    public function getMontantRemiseProperty()
    {
        $brut = $this->brutHt;

        if ($this->remise_type === 'percent') {
            return round($brut * ((float) ($this->remise_value ?: 0)) / 100, 2);
        }

        return min((float) ($this->remise_value ?: 0), $brut);
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

            return ((float) ($i['unit_price'] ?? 0) - $purchase) * (float) ($i['quantity'] ?? 0);
        });
    }

    /**
     * When "Verification du stock" is active on Ma Societe, block the save
     * if any line asks for more than the product's current stock_qty.
     */
    protected function checkStockAvailability(): bool
    {
        if (! \App\Models\Societe::current()->stock_verification) {
            return true;
        }

        $productIds = collect($this->items)->pluck('product_id')->filter()->unique()->values();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $ok = true;
        foreach ($this->items as $index => $item) {
            if (empty($item['product_id']) || ! $products->has($item['product_id'])) {
                continue;
            }

            $product = $products[$item['product_id']];
            $quantity = (float) ($item['quantity'] ?? 0);

            if ($quantity > (float) $product->stock_qty) {
                $this->addError("items.{$index}.quantity", "Stock insuffisant pour \"{$product->name}\" (disponible : {$product->stock_qty}).");
                $ok = false;
            }
        }

        return $ok;
    }

    public function openPayment(string $group): void
    {
        $this->resetValidation();
        $this->payGroup = in_array($group, ['cash', 'papier'], true) ? $group : '';
        $this->payMode = $group === 'papier' ? 'cheque' : 'especes';
        $this->payDate = now()->format('Y-m-d');
        $this->payEcheance = $group === 'papier' ? now()->format('Y-m-d') : null;
        $this->payBanque = '';
        $this->payNumero = '';
        $this->payTireur = '';
        $this->payNotes = '';
        $this->payCompte = null;
        $this->payMontant = max(0, round($this->resteAPayer(), 2)) ?: null;
    }

    public function closePayment(): void
    {
        $this->payGroup = '';
    }

    /** total of the delivery note as typed, minus what is already paid */
    public function resteAPayer(): float
    {
        $paid = $this->bonLivraison ? $this->bonLivraison->montantRegle() : array_sum(array_column($this->pendingPayments, 'montant'));

        return round((float) $this->totalTtc - $paid, 2);
    }

    public function addPayment(): void
    {
        $modes = $this->payGroup === 'papier' ? \App\Support\SalesLists::MODES_PAPIER : ['especes', 'virement', 'versement', 'transfert'];
        $this->validate([
            'payMode' => 'required|in:'.implode(',', $modes),
            'payMontant' => 'required|numeric|min:0.01',
            'payDate' => 'required|date',
            'payEcheance' => 'nullable|date',
            'payBanque' => 'nullable|string|max:100',
            'payNumero' => 'nullable|string|max:60',
            'payTireur' => 'nullable|string|max:120',
            'payNotes' => 'nullable|string|max:500',
        ], [], ['payMontant' => 'montant', 'payDate' => 'date', 'payTireur' => 'nom sur le chèque', 'payNotes' => 'observation']);
        if ($this->payCompte && ! \App\Models\TresorerieCompte::find($this->payCompte)) {
            $this->addError('payCompte', 'Compte introuvable.');

            return;
        }
        $data = [
            'montant' => round((float) $this->payMontant, 2),
            'date' => $this->payDate,
            'mode' => $this->payMode,
            'tresorerie_compte_id' => $this->payCompte ?: null,
            'banque' => in_array($this->payMode, ['cheque', 'effet', 'virement', 'versement'], true) ? (trim($this->payBanque) ?: null) : null,
            'numero_cheque' => trim($this->payNumero) ?: null,
            'date_echeance' => $this->payGroup === 'papier' ? ($this->payEcheance ?: null) : null,
            'tireur' => $this->payGroup === 'papier' ? (trim((string) $this->payTireur) ?: null) : null,
        ];
        if (($obs = trim((string) $this->payNotes)) !== '') {
            $data['notes'] = $obs;                         // otherwise the automatic "Règlement du bon de livraison …"
        }
        if ($this->bonLivraison) {
            $this->createPayment($this->bonLivraison, $data);
            $this->bonLivraison->refresh();
        } else {
            $this->pendingPayments[] = $data;
        }
        $this->payGroup = '';
    }

    public function removePayment($key): void
    {
        if ($this->bonLivraison) {
            $r = \App\Models\ReglementClient::where('bon_livraison_id', $this->bonLivraison->id)->findOrFail((int) $key);
            \App\Support\Tenant::tryDelete($r);
            $this->bonLivraison->refresh();
        } else {
            unset($this->pendingPayments[(int) $key]);
            $this->pendingPayments = array_values($this->pendingPayments);
        }
    }

    private function createPayment(BonLivraison $bl, array $data): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($bl, $data) {
            $r = \App\Models\ReglementClient::create($data + [
                'client_id' => $bl->client_id,
                'bon_livraison_id' => $bl->id,
                'facture_id' => $bl->facture?->id,
                'notes' => 'Règlement du bon de livraison '.$bl->numero,
            ]);
            $r->applyEffects();
        });
    }

    public function save()
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
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

        if (! $this->checkStockAvailability()) {
            return;
        }

        $data = [
            'client_id' => $this->client_id,
            'date' => $this->date,
            'show_prices' => $this->show_prices,
            'status' => $this->status,
            'tva_rate' => $this->tva_rate,
            'remise_type' => $this->remise_type,
            'remise_value' => $this->remise_value ?: 0,
        ];

        if ($this->bonLivraison) {
            $this->bonLivraison->update($data);
            $this->bonLivraison->items()->delete();
        } else {
            $data['numero'] = \App\Support\Tenant::nextNumero(BonLivraison::class, 'BL-');
            $this->bonLivraison = BonLivraison::create($data);
        }

        foreach ($this->items as $item) {
            $this->bonLivraison->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tva_rate' => $this->lineRate($item),
            ]);
        }

        $this->bonLivraison->recalculateTotals();
        \App\Support\StockSync::bonLivraison($this->bonLivraison->fresh());
        if (\App\Support\Chargement::enabled()) {
            \App\Support\Chargement::syncFromBl($this->bonLivraison->fresh());   // its loading slip follows (2026-11-27)
        }
        foreach ($this->pendingPayments as $p) {
            $this->createPayment($this->bonLivraison->fresh(), $p);
        }
        $this->pendingPayments = [];
        // payments follow the delivery note's customer if it was changed
        \App\Models\ReglementClient::where('bon_livraison_id', $this->bonLivraison->id)->where('client_id', '!=', $this->bonLivraison->client_id)
            ->update(['client_id' => $this->bonLivraison->client_id]);

        session()->flash('success', 'Bon de livraison enregistré.');

        return $this->redirect(route('bon-livraisons.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.bon-livraisons.form', [
            'clients' => Client::orderBy('name')->get(),
            'products' => Product::with('brand')->orderBy('name')->get(),
            'categories' => \App\Models\Category::orderBy('name')->get(),
            'brands' => \App\Models\Brand::orderBy('name')->get(),
            'fournisseurs' => \App\Models\Fournisseur::orderBy('name')->get(),
            'payments' => $this->bonLivraison ? $this->bonLivraison->reglements()->with('tresorerieCompte')->orderBy('date')->orderBy('id')->get() : null,
            'banks' => \App\Support\Banks::for(),
            'comptes' => \App\Models\TresorerieCompte::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
