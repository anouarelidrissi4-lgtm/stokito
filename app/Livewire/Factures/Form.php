<?php
namespace App\Livewire\Factures;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\BonLivraison;
use App\Models\Client;
use App\Models\Facture;
use App\Models\Product;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Form extends Component
{
    use RequiresPermission;
    use \App\Livewire\Concerns\CopilotPrefill;
    use \App\Livewire\Concerns\HasLineVat;
    use \App\Livewire\Concerns\HasLineTools;

    public ?Facture $facture = null;

    #[Validate('required|exists:clients,id')]
    public $client_id = '';

    public ?int $bon_livraison_id = null;

    #[\Livewire\Attributes\Locked]
    public ?int $devis_id = null;

    /** Invoice made for a till ticket (2026-11-25). */
    public ?int $caisse_ticket_id = null;

    #[\Livewire\Attributes\Locked]
    public string $devis_numero = '';

    #[Validate('required|date')]
    public $date;

    public $due_date = '';

    public $tva_rate = 20;

    public $status = 'brouillon';

    public $notes = '';

    public $objet = '';

    public $adresse_livraison = '';

    public $nature_operation = '';

    #[Validate('required|in:percent,fixed')]
    public $remise_type = 'percent';

    #[Validate('nullable|numeric|min:0')]
    public $remise_value = 0;

    public array $items = [];

    public bool $tarif_gros = false;

    // --- Recherche d'une facture existante par numero ---
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

    public function mount($factureId = null, $blId = null)
    {
        $this->requirePermission('factures_consulter');

        $this->date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->nature_operation = \App\Models\Societe::current()->nature_operation_default ?? '';

        if ($factureId) {
            $this->facture = Facture::with('items')->findOrFail($factureId);
            $this->client_id = $this->facture->client_id;
            $this->bon_livraison_id = $this->facture->bon_livraison_id;
            $this->devis_id = $this->facture->devis_id;
            $this->caisse_ticket_id = $this->facture->caisse_ticket_id;
            $this->devis_numero = (string) ($this->facture->devis?->numero ?? '');
            $this->date = $this->facture->date->format('Y-m-d');
            $this->due_date = optional($this->facture->due_date)->format('Y-m-d') ?? '';
            $this->tva_rate = $this->facture->tva_rate;
            $this->status = $this->facture->status;
            $this->notes = $this->facture->notes ?? '';
            $this->objet = $this->facture->objet ?? '';
            $this->adresse_livraison = $this->facture->adresse_livraison ?? '';
            $this->nature_operation = $this->facture->nature_operation ?? '';
            $this->remise_type = $this->facture->remise_type ?? 'percent';
            $this->remise_value = $this->facture->remise_value ?? 0;
            $this->items = $this->facture->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
                'remise_type' => $i->remise_type ?? 'percent',
                'remise_value' => (float) ($i->remise_value ?? 0),
                'tva_rate' => $i->tva_rate !== null ? (float) $i->tva_rate : (float) $this->tva_rate,
            ])->toArray();
        } elseif ($blId) {
            $bl = BonLivraison::with('items')->findOrFail($blId);
            $this->client_id = $bl->client_id;
            $this->bon_livraison_id = $bl->id;
            // keep the delivery note's VAT and discount, so the invoice matches what was agreed
            $this->tva_rate = $bl->tva_rate !== null ? (float) $bl->tva_rate : $this->defaultVatRate();
            $this->remise_type = $bl->remise_type ?? 'percent';
            $this->remise_value = (float) ($bl->remise_value ?? 0);
            $this->items = $bl->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) ($i->unit_price ?? 0),
                'remise_type' => $i->remise_type ?? 'percent',      // the BL's line discount is kept on the invoice line
                'remise_value' => (float) ($i->remise_value ?? 0),
                'tva_rate' => $i->tva_rate !== null ? (float) $i->tva_rate : (float) $this->tva_rate,
            ])->toArray();
        } elseif ($ticketId = (int) request()->query('ticket')) {
            // invoice asked by a till customer: same lines and prices; the ticket already paid and took the stock
            $t = \App\Models\CaisseTicket::with('lignes')->findOrFail($ticketId);
            abort_if($t->isAnnulation() || \App\Models\CaisseTicket::where('annule_ticket_id', $t->id)->exists(), 403, 'Ce ticket est annulé.');
            if ($existing = Facture::where('caisse_ticket_id', $t->id)->first()) {
                $this->redirect(route('factures.edit', $existing->id), navigate: true);

                return;
            }
            $this->caisse_ticket_id = $t->id;
            $this->client_id = $t->client_id ?: '';
            $this->date = $t->created_at->format('Y-m-d');
            $this->due_date = $t->created_at->format('Y-m-d');
            $this->tva_rate = $this->defaultVatRate();
            $this->notes = 'Facture du ticket de caisse '.$t->numero.' du '.$t->created_at->format('d/m/Y').', réglé à la caisse.';
            $this->items = $t->lignes->map(fn ($l) => [
                'product_id' => $l->product_id,
                'quantity' => (float) $l->quantity,
                'unit_price' => (float) $l->quantity != 0.0 ? round((float) $l->total_ht / (float) $l->quantity, 2) : 0,
                'tva_rate' => (float) $l->tva_rate,
            ])->values()->all();
        } elseif ($devisId = (int) request()->query('devis')) {
            // invoice straight from an accepted quote (no delivery note): same lines, VAT and discount
            $devis = \App\Models\Devis::findOrFail($devisId);
            $this->devis_id = $devis->id;
            $this->devis_numero = $devis->numero;
            $this->objet = (string) ($devis->objet ?? '');
            $this->client_id = $devis->client_id;
            $this->tva_rate = (float) $devis->tva_rate;
            $this->remise_type = $devis->remise_type ?? 'percent';
            $this->remise_value = (float) ($devis->remise_value ?? 0);
            $this->items = $devis->linesForConversion();      // a line discount of the quote is carried in the net unit price
        } else {
            $this->tva_rate = $this->defaultVatRate();
            $this->addItem();
        }
        $this->applyCopilotPrefill();
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'quantity' => 1, 'unit_price' => 0, 'remise_type' => 'percent', 'remise_value' => 0, 'tva_rate' => (float) $this->tva_rate];
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

    // --- Recherche d'une facture existante par numero ---
    public function searchByNumero()
    {
        $this->numeroSearchPerformed = true;

        if (trim($this->numeroSearch) === '') {
            $this->numeroSearchResults = [];

            return;
        }

        $this->numeroSearchResults = Facture::query()
            ->with('client')
            ->where('numero', 'like', '%'.$this->numeroSearch.'%')
            ->latest('date')
            ->limit(10)
            ->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'numero' => $f->numero,
                'client' => $f->client->name ?? '-',
                'date' => $f->date->format('d/m/Y'),
                'total_ttc' => $f->total_ttc,
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

    /** Document discount, on the lines after their own discounts (2026-11-27). */
    public function getMontantRemiseProperty()
    {
        $brut = $this->brutHt - $this->montantRemiseLignes;

        if ($this->remise_type === 'percent') {
            return round($brut * ((float) ($this->remise_value ?: 0)) / 100, 2);
        }

        return min((float) ($this->remise_value ?: 0), $brut);
    }

    public function getTotalHtProperty()
    {
        return $this->brutHt - $this->montantRemiseLignes - $this->montantRemise;
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

            return ((float) ($i['unit_price'] ?? 0) - $purchase) * (float) ($i['quantity'] ?? 0) - $this->ligneRemiseMontant($i);
        });
    }

    /**
     * When "Verification du stock" is active on Ma Societe, block the save
     * if any line asks for more than the product's current stock_qty.
     */
    protected function checkStockAvailability(): bool
    {
        if (! \App\Models\Societe::current()->stock_verification || $this->caisse_ticket_id) {
            return true; // a till invoice does not take stock
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

    public function save()
    {
        if (! $this->facture && ! \App\Support\Plans::canCreateFacture()) {
            $this->addError('plan', \App\Support\Plans::factureLimitMessage());

            return;
        }
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
            'items.*.remise_type' => 'nullable|in:percent,fixed',
            'items.*.remise_value' => 'nullable|numeric|min:0',
            'items.*.tva_rate' => 'nullable|numeric|min:0|max:100',
        ], [], ['items.*.remise_value' => 'réduction']);
        if (! $this->lineDiscountsValid()) {
            return;
        }

        if (! $this->checkStockAvailability()) {
            return;
        }

        $data = [
            'client_id' => $this->client_id,
            'bon_livraison_id' => $this->bon_livraison_id,
            'devis_id' => $this->devis_id,
            'caisse_ticket_id' => $this->caisse_ticket_id,
            'date' => $this->date,
            'due_date' => $this->due_date ?: null,
            'tva_rate' => $this->tva_rate,
            'status' => $this->status,
            'notes' => $this->notes,
            'objet' => mb_substr(trim((string) $this->objet), 0, 255) ?: null,
            'adresse_livraison' => trim((string) $this->adresse_livraison) ?: null,
            'nature_operation' => in_array($this->nature_operation, ['biens', 'services', 'mixte'], true) ? $this->nature_operation : null,
            'remise_type' => $this->remise_type,
            'remise_value' => $this->remise_value ?: 0,
        ];

        if ($this->facture) {
            $this->facture->update($data);
            $this->facture->items()->delete();
        } else {
            $data['numero'] = \App\Support\Tenant::nextNumero(Facture::class, 'FAC-');
            $this->facture = Facture::create($data);
        }

        foreach ($this->items as $item) {
            $total = $this->ligneTotal($item);      // after the line's own discount
            $this->facture->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'remise_type' => ($item['remise_type'] ?? 'percent') === 'fixed' ? 'fixed' : 'percent',
                'remise_value' => (float) ($item['remise_value'] ?? 0) ?: 0,
                'tva_rate' => $this->lineRate($item),
                'total' => $total,
            ]);
        }

        $this->facture->recalculateTotals();
        if ($this->facture->bon_livraison_id) {
            \App\Models\ReglementClient::where('bon_livraison_id', $this->facture->bon_livraison_id)->whereNull('facture_id')
                ->update(['facture_id' => $this->facture->id]);
        }
        $this->facture->refreshStatus();
        \App\Support\StockSync::facture($this->facture->fresh());

        session()->flash('success', 'Facture enregistrée.');

        return $this->redirect(route('factures.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.factures.form', [
            'clients' => Client::orderBy('name')->get(),
            'products' => Product::with(['brand', 'category'])->orderBy('name')->get(),
            'categories' => \App\Models\Category::orderBy('name')->get(),
            'brands' => \App\Models\Brand::orderBy('name')->get(),
            'fournisseurs' => \App\Models\Fournisseur::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
