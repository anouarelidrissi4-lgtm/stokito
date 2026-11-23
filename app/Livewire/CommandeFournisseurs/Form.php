<?php
namespace App\Livewire\CommandeFournisseurs;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\CommandeFournisseur;
use App\Models\Fournisseur;
use App\Models\Product;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Form extends Component
{
    use RequiresPermission;

    public ?CommandeFournisseur $commande = null;

    #[Validate('required|exists:fournisseurs,id')]
    public $fournisseur_id = '';

    #[Validate('required|date')]
    public $date;

    public $status = 'draft';

    public $tva_rate = 20;

    #[Validate('required|in:percent,fixed')]
    public $remise_type = 'percent';

    #[Validate('nullable|numeric|min:0')]
    public $remise_value = 0;

    public array $items = [];

    // --- Recherche d'une commande existante par numero ---
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

    public function mount($commandeId = null)
    {
        $this->requirePermission('gestion_commandes_fournisseurs');

        $this->date = now()->format('Y-m-d');

        if ($commandeId) {
            $this->commande = CommandeFournisseur::with('items')->findOrFail($commandeId);
            $this->fournisseur_id = $this->commande->fournisseur_id;
            $this->date = $this->commande->date->format('Y-m-d');
            $this->status = $this->commande->status;
            $this->tva_rate = $this->commande->tva_rate ?? 20;
            $this->remise_type = $this->commande->remise_type ?? 'percent';
            $this->remise_value = $this->commande->remise_value ?? 0;
            $this->items = $this->commande->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
            ])->toArray();
        } else {
            $this->tva_rate = \App\Models\Societe::current()->tva_rate_default;
            $this->addItem();
        }
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'quantity' => 1, 'unit_price' => 0];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key)
    {
        [$index, $field] = explode('.', $key);
        if ($field === 'product_id' && $value) {
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['unit_price'] = (float) $product->purchase_price;
            }
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
                $this->items[$index]['unit_price'] = (float) $product->purchase_price;

                return;
            }
        }

        $this->items[] = [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => (float) $product->purchase_price,
        ];
    }

    // --- Recherche d'une commande existante par numero ---
    public function searchByNumero()
    {
        $this->numeroSearchPerformed = true;

        if (trim($this->numeroSearch) === '') {
            $this->numeroSearchResults = [];

            return;
        }

        $this->numeroSearchResults = CommandeFournisseur::query()
            ->with('fournisseur')
            ->where('numero', 'like', '%'.$this->numeroSearch.'%')
            ->latest('date')
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'numero' => $c->numero,
                'fournisseur' => $c->fournisseur->name ?? '-',
                'date' => $c->date->format('d/m/Y'),
                'total' => $c->total,
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
            $this->items[$this->productModalItemIndex]['unit_price'] = (float) $product->purchase_price;
        } else {
            $this->items[] = [
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => (float) $product->purchase_price,
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
        return $this->totalHt * (1 + ((float) $this->tva_rate) / 100);
    }

    public function save()
    {
        $this->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
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
        ]);

        $data = [
            'fournisseur_id' => $this->fournisseur_id,
            'date' => $this->date,
            'status' => $this->status,
            'tva_rate' => $this->tva_rate,
            'remise_type' => $this->remise_type,
            'remise_value' => $this->remise_value ?: 0,
        ];

        if ($this->commande) {
            $this->commande->update($data);
            $this->commande->items()->delete();
        } else {
            $data['numero'] = \App\Support\Tenant::nextNumero(CommandeFournisseur::class, 'CF-');
            $this->commande = CommandeFournisseur::create($data);
        }

        foreach ($this->items as $item) {
            $total = (float) $item['quantity'] * (float) $item['unit_price'];
            $this->commande->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $total,
            ]);
        }

        $this->commande->recalculateTotals();

        session()->flash('success', 'Commande fournisseur enregistrée.');

        return $this->redirect(route('commande-fournisseurs.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.commande-fournisseurs.form', [
            'fournisseurs' => Fournisseur::orderBy('name')->get(),
            'products' => Product::with('brand')->orderBy('name')->get(),
            'categories' => \App\Models\Category::orderBy('name')->get(),
            'brands' => \App\Models\Brand::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
