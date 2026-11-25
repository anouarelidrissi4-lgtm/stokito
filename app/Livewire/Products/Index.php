<?php

namespace App\Livewire\Products;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Fournisseur;
use App\Models\Product;
use App\Support\ProductSheet;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination, RequiresPermission;

    #[\Livewire\Attributes\Url(as: 'q', except: '')]
    public string $search = '';
    public string $categoryFilter = '';
    public bool $showModal = false;
    public bool $showImportModal = false;
    public ?int $editingId = null;

    // product photo (2026-11-25)
    public $image = null;

    public ?string $imagePath = null;

    public bool $removeImage = false;

    public function mount(): void
    {
        $this->requirePermission('produits_consulter');
    }

    #[Validate('required|string|max:255')]
    public string $reference = '';

    public string $barcode = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    public ?int $category_id = null;
    public ?int $brand_id = null;
    public ?int $fournisseur_id = null;

    #[Validate('required|string|max:50')]
    public string $unit = 'piece';

    #[Validate('required|numeric|min:0')]
    public $purchase_price = 0;

    #[Validate('required|numeric|min:0')]
    public $sale_price = 0;

    public $wholesale_price = null;

    public $tva_rate = null;

    #[Validate('required|integer|min:0')]
    public $stock_qty = 0;

    public $stock_alert_threshold = null;

    public $importFile = null;

    public ?array $importReport = null;

    public function create(): void
    {
        $this->requirePermission('produits_creer');
        $this->resetForm();
        $this->reset(['image', 'imagePath', 'removeImage']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->requirePermission('produits_edition');
        $p = Product::findOrFail($id);
        $this->editingId = $p->id;
        $this->reference = $p->reference;
        $this->barcode = $p->barcode ?? '';
        $this->name = $p->name;
        $this->category_id = $p->category_id;
        $this->brand_id = $p->brand_id;
        $this->fournisseur_id = $p->fournisseur_id;
        $this->unit = $p->unit;
        $this->purchase_price = auth()->user()->hasPermission('produits_prix_achat') ? $p->purchase_price : 0;
        $this->sale_price = $p->sale_price;
        $this->wholesale_price = $p->wholesale_price;
        $this->tva_rate = $p->tva_rate;
        $this->stock_qty = $p->stock_qty;
        $this->stock_alert_threshold = $p->stock_alert_threshold;
        $this->reset(['image', 'removeImage']);
        $this->imagePath = $p->image_path;
        $this->showModal = true;
    }

    /**
     * Called by the barcode-scanner component inside the create/edit modal.
     */
    public function onBarcodeField(string $code): void
    {
        $this->barcode = $code;
    }

    /**
     * Called by the barcode-scanner component on the list toolbar: scan to search/find a product.
     */
    public function onBarcodeSearch(string $code): void
    {
        $this->search = $code;
        $this->resetPage();
    }

    public function save(): void
    {
        $this->requirePermission($this->editingId ? 'produits_edition' : 'produits_creer');

        $this->validate([
            'reference' => ['required', 'string', 'max:255', \App\Support\Tenant::unique('products', 'reference', $this->editingId)],
            'barcode' => ['nullable', 'string', 'max:255', \App\Support\Tenant::unique('products', 'barcode', $this->editingId)],
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'tva_rate' => 'nullable|numeric|min:0|max:100',
            'stock_qty' => 'required|integer|min:0',
            'stock_alert_threshold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $old = $this->editingId ? Product::find($this->editingId)?->image_path : null;
        $newPath = $old;
        if ($this->image) {
            $newPath = Product::storeImage($this->image->getRealPath(), auth()->user()->project_id) ?? $old;
        } elseif ($this->removeImage) {
            $newPath = null;
        }
        if ($old && $old !== $newPath) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($old);
        }

        Product::updateOrCreate(['id' => $this->editingId], [
            'reference' => $this->reference,
            'barcode' => $this->barcode ?: null,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'fournisseur_id' => $this->fournisseur_id,
            'unit' => $this->unit,
            'purchase_price' => auth()->user()->hasPermission('produits_prix_achat') ? $this->purchase_price : ($this->editingId ? Product::find($this->editingId)?->purchase_price ?? 0 : 0),
            'sale_price' => $this->sale_price,
            'wholesale_price' => $this->wholesale_price ?: null,
            'tva_rate' => $this->tva_rate === '' || $this->tva_rate === null ? null : $this->tva_rate,
            'stock_qty' => $this->stock_qty,
            'stock_alert_threshold' => $this->stock_alert_threshold,
            'image_path' => $newPath,
        ]);
        $this->reset(['image', 'imagePath', 'removeImage']);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Produit enregistré.');
    }

    public function delete(int $id): void
    {
        $this->requirePermission('produits_supprimer');
        if (\App\Support\Tenant::tryDelete(Product::findOrFail($id))) {
            session()->flash('success', 'Produit supprimé.');
        }
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->tva_rate = null;
        $this->reference = '';
        $this->barcode = '';
        $this->name = '';
        $this->category_id = null;
        $this->brand_id = null;
        $this->fournisseur_id = null;
        $this->unit = 'piece';
        $this->purchase_price = 0;
        $this->sale_price = 0;
        $this->wholesale_price = null;
        $this->stock_qty = 0;
        $this->stock_alert_threshold = null;
        $this->resetValidation();
    }

    protected function filteredQuery()
    {
        return Product::with(['category', 'brand', 'fournisseur'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('reference', 'like', "%{$this->search}%")
                ->orWhere('barcode', $this->search))
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->orderBy('name');
    }

    /** Real .xlsx export of the filtered list (2026-11-25, the old comma CSV opened in one column in Excel FR). */
    public function exportExcel()
    {
        $path = ProductSheet::export($this->filteredQuery()->get(), auth()->user()->hasPermission('produits_prix_achat'));

        return response()->download($path, 'produits-'.now()->format('Y-m-d').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    public function openImport(): void
    {
        $this->requirePermission('produits_creer');
        $this->importFile = null;
        $this->importReport = null;
        $this->showImportModal = true;
    }

    /**
     * Import .xlsx / .ods / .csv. Columns are found by their header name; a product is
     * matched by its reference (created when new). On an existing product an empty cell
     * keeps the current value, so a re-imported export never wipes data.
     */
    public function importExcel(): void
    {
        $this->requirePermission('produits_creer');
        $this->validate(['importFile' => 'required|file|max:5120|mimes:xlsx,ods,csv,txt'], [], ['importFile' => 'fichier']);

        $ext = strtolower($this->importFile->getClientOriginalExtension() ?: $this->importFile->extension());
        try {
            $sheet = ProductSheet::read($this->importFile->getRealPath(), $ext);
        } catch (\Throwable $e) {
            report($e);
            $this->addError('importFile', __('Fichier illisible. Enregistrez-le au format Excel (.xlsx) puis réessayez.'));

            return;
        }

        $canBuy = auth()->user()->hasPermission('produits_prix_achat');
        $created = $updated = 0;
        $errors = [];
        $cats = $brands = $fourns = [];
        $str = fn ($v) => $v === null ? '' : trim(is_float($v) && floor($v) == $v ? (string) (int) $v : (string) $v);

        foreach ($sheet['rows'] as $r) {
            $ref = $str($r['reference'] ?? null);
            $name = $str($r['name'] ?? null);
            if ($ref === '') {
                $errors[] = __('Ligne :n : référence manquante', ['n' => $r['_line']]);

                continue;
            }
            $data = [];
            if ($name !== '') {
                $data['name'] = mb_substr($name, 0, 255);
            }
            if (($v = $str($r['barcode'] ?? null)) !== '') {
                $data['barcode'] = $v;
            }
            if (($v = $str($r['unit'] ?? null)) !== '') {
                $data['unit'] = mb_substr($v, 0, 50);
            }
            if (($v = $str($r['category'] ?? null)) !== '') {
                $data['category_id'] = $cats[mb_strtolower($v)] ??= Category::firstOrCreate(['name' => $v])->id;
            }
            if (($v = $str($r['brand'] ?? null)) !== '') {
                $data['brand_id'] = $brands[mb_strtolower($v)] ??= Brand::firstOrCreate(['name' => $v])->id;
            }
            if (($v = $str($r['fournisseur'] ?? null)) !== '') {
                $data['fournisseur_id'] = $fourns[mb_strtolower($v)] ??= Fournisseur::firstOrCreate(['name' => $v])->id;
            }
            foreach (['sale_price', 'wholesale_price', 'stock_qty', 'stock_alert_threshold'] + ($canBuy ? [4 => 'purchase_price'] : []) as $f) {
                if (array_key_exists($f, $r) && ($n = ProductSheet::num($r[$f])) !== null) {
                    $data[$f] = $f === 'stock_qty' || $f === 'stock_alert_threshold' ? (int) round($n) : max(0, $n);
                }
            }

            try {
                $product = Product::where('reference', $ref)->first();
                if ($product) {
                    $product->update($data);
                    $updated++;
                } else {
                    Product::create($data + ['reference' => $ref, 'name' => $name !== '' ? mb_substr($name, 0, 255) : $ref,
                        'unit' => $data['unit'] ?? 'piece', 'purchase_price' => $data['purchase_price'] ?? 0, 'sale_price' => $data['sale_price'] ?? 0,
                        'stock_qty' => $data['stock_qty'] ?? 0]);
                    $created++;
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $errors[] = str_contains($e->getMessage(), 'barcode')
                    ? __('Ligne :n : ce code-barres est déjà utilisé par un autre produit', ['n' => $r['_line']])
                    : __('Ligne :n : non enregistrée', ['n' => $r['_line']]);
            }
        }

        $this->importReport = ['created' => $created, 'updated' => $updated, 'errors' => $errors, 'unknown' => $sheet['unknown'], 'empty' => ! $sheet['rows']];
        $this->importFile = null;
        if ($created || $updated) {
            session()->flash('success', __(':c produit(s) créé(s), :u mis à jour.', ['c' => $created, 'u' => $updated]));
        }
    }

    public function render()
    {
        return view('livewire.products.index', [
            'products' => $this->filteredQuery()->paginate(10),
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'fournisseurs' => Fournisseur::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
