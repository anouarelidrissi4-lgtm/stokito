<?php

namespace App\Livewire\Products;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Fournisseur;
use App\Models\Product;
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

    public function exportCsv()
    {
        $products = $this->filteredQuery()->get();

        return response()->streamDownload(function () use ($products) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Code-barres', 'Designation', 'Categorie', 'Marque', 'Fournisseur', 'Unite', 'Prix Achat', 'Prix Vente TTC', 'Prix Vente Gros', 'Stock', 'Seuil Alerte'], ',', '"', '\\');
            foreach ($products as $p) {
                fputcsv($out, [
                    $p->reference,
                    $p->barcode,
                    $p->name,
                    $p->category?->name,
                    $p->brand?->name,
                    $p->fournisseur?->name,
                    $p->unit,
                    auth()->user()->hasPermission('produits_prix_achat') ? $p->purchase_price : '',
                    $p->sale_price,
                    $p->wholesale_price,
                    $p->stock_qty,
                    $p->stock_alert_threshold,
                ], ',', '"', '\\');
            }
            fclose($out);
        }, 'produits-'.now()->format('Y-m-d').'.csv');
    }

    public function openImport(): void
    {
        $this->requirePermission('produits_creer');
        $this->importFile = null;
        $this->showImportModal = true;
    }

    public function importCsv(): void
    {
        $this->requirePermission('produits_creer');
        $this->validate(['importFile' => 'required|file|mimes:csv,txt|max:5120']);

        $handle = fopen($this->importFile->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2 || empty($row[0])) {
                $skipped++;

                continue;
            }

            [$reference, $barcode, $name, $categoryName, $brandName, $fournisseurName, $unit, $purchasePrice, $salePrice, $wholesalePrice, $stockQty, $threshold] = array_pad($row, 12, null);

            $categoryId = $categoryName ? Category::firstOrCreate(['name' => trim($categoryName)])->id : null;
            $brandId = $brandName ? Brand::firstOrCreate(['name' => trim($brandName)])->id : null;
            $fournisseurId = $fournisseurName ? Fournisseur::firstOrCreate(['name' => trim($fournisseurName)])->id : null;

            Product::updateOrCreate(
                ['reference' => trim($reference)],
                [
                    'barcode' => $barcode ? trim($barcode) : null,
                    'name' => trim($name) ?: trim($reference),
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'fournisseur_id' => $fournisseurId,
                    'unit' => $unit ?: 'piece',
                    'purchase_price' => is_numeric($purchasePrice) ? $purchasePrice : 0,
                    'sale_price' => is_numeric($salePrice) ? $salePrice : 0,
                    'wholesale_price' => is_numeric($wholesalePrice) ? $wholesalePrice : null,
                    'stock_qty' => is_numeric($stockQty) ? (int) $stockQty : 0,
                    'stock_alert_threshold' => is_numeric($threshold) ? (int) $threshold : null,
                ]
            );
            $imported++;
        }
        fclose($handle);

        $this->showImportModal = false;
        session()->flash('success', "{$imported} produit(s) importe(s), {$skipped} ligne(s) ignoree(s).");
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
