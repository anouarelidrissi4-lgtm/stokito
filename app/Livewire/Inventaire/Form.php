<?php

namespace App\Livewire\Inventaire;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Category;
use App\Models\Inventaire;
use App\Models\InventaireItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\Tenant;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * New stock count: the user types the counted quantity next to each product.
 * Only products with a typed value are part of the inventaire. On validation the
 * stock becomes the counted quantity and every gap is logged as a stock movement.
 * With $inventaireId it shows a past inventaire (read only).
 */
class Form extends Component
{
    use RequiresPermission;

    public ?int $inventaireId = null;

    public string $search = '';

    public string $category = '';

    public bool $onlyCounted = false;

    /** product_id => counted quantity (string from the input, '' = not counted) */
    public array $counted = [];

    public string $notes = '';

    public function mount($inventaireId = null)
    {
        $this->requirePermission('inventaire');
        $this->inventaireId = $inventaireId ? (int) $inventaireId : null;
        if ($this->inventaireId) {
            Inventaire::findOrFail($this->inventaireId); // scoped: 404 for another project
        }
    }

    /** Fill the empty inputs of the visible products with their current stock. */
    public function fillVisible(): void
    {
        foreach ($this->products()->get() as $p) {
            if (($this->counted[$p->id] ?? '') === '') {
                $this->counted[$p->id] = (string) $p->stock_qty;
            }
        }
    }

    public function resetCounts(): void
    {
        $this->counted = [];
    }

    protected function countedValues(): array
    {
        return collect($this->counted)
            ->filter(fn ($v) => $v !== '' && $v !== null && is_numeric($v))
            ->map(fn ($v) => (int) round((float) $v))
            ->all();
    }

    public function validateInventaire()
    {
        abort_if($this->inventaireId, 403);
        $this->requirePermission('inventaire');
        $values = $this->countedValues();

        if (! $values) {
            $this->addError('counted', 'Saisissez au moins une quantité comptée.');

            return;
        }

        $inv = DB::transaction(function () use ($values) {
            $products = Product::whereIn('id', array_keys($values))->lockForUpdate()->get()->keyBy('id');
            $inv = Inventaire::create([
                'numero' => Tenant::nextNumero(Inventaire::class, 'INV-'),
                'date' => now()->toDateString(),
                'user_id' => auth()->id(),
                'notes' => $this->notes ?: null,
                'nb_produits' => $products->count(),
                'nb_ecarts' => 0,
            ]);
            $gaps = 0;
            foreach ($values as $pid => $qty) {
                $p = $products[$pid] ?? null;
                if (! $p) {
                    continue;
                }
                $ecart = $qty - (int) $p->stock_qty;
                InventaireItem::create([
                    'inventaire_id' => $inv->id, 'product_id' => $p->id,
                    'stock_systeme' => (int) $p->stock_qty, 'stock_compte' => $qty, 'ecart' => $ecart,
                    'purchase_price' => $p->purchase_price ?? 0,
                ]);
                if ($ecart !== 0) {
                    $gaps++;
                    StockMovement::create([
                        'product_id' => $p->id, 'type' => $ecart > 0 ? 'entry' : 'exit', 'quantity' => abs($ecart),
                        'reference_type' => 'inventaire', 'reference_id' => $inv->id,
                        'date' => now()->toDateString(), 'notes' => 'Inventaire '.$inv->numero,
                    ]);
                    $p->update(['stock_qty' => $qty]);
                }
            }
            $inv->update(['nb_ecarts' => $gaps]);

            return $inv;
        });

        session()->flash('success', "Inventaire {$inv->numero} validé : stock mis à jour pour {$inv->nb_ecarts} produit(s).");

        return $this->redirect(route('inventaire.show', $inv->id), navigate: true);
    }

    protected function products()
    {
        return Product::with('category')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")->orWhere('reference', 'like', "%{$this->search}%")->orWhere('barcode', $this->search)))
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->when($this->onlyCounted, fn ($q) => $q->whereIn('id', array_keys($this->countedValues()) ?: [0]))
            ->orderBy('name');
    }

    public function render()
    {
        $seePrice = auth()->user()->hasPermission('produits_prix_achat');

        if ($this->inventaireId) {
            $inv = Inventaire::with(['items.product', 'user'])->findOrFail($this->inventaireId);

            return view('livewire.inventaire.show', ['inv' => $inv, 'seePrice' => $seePrice])->layout('layouts.app');
        }

        $values = $this->countedValues();
        $all = Product::whereIn('id', array_keys($values))->get(['id', 'stock_qty', 'purchase_price'])->keyBy('id');
        $gaps = collect($values)->filter(fn ($q, $id) => isset($all[$id]) && $q !== (int) $all[$id]->stock_qty);

        return view('livewire.inventaire.form', [
            'products' => $this->products()->get(),
            'categories' => Category::orderBy('name')->get(),
            'nbCounted' => count($values),
            'nbGaps' => $gaps->count(),
            'gapValue' => $gaps->sum(fn ($q, $id) => ($q - (int) $all[$id]->stock_qty) * (float) $all[$id]->purchase_price),
            'seePrice' => $seePrice,
        ])->layout('layouts.app');
    }
}
