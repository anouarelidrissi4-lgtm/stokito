<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommandeFournisseur extends Model
{
    use ScopedToDemoMode;

    protected $fillable = [
        'fournisseur_id', 'numero', 'reference_fournisseur', 'date', 'status', 'total',
        'total_ht', 'tva_rate', 'remise_type', 'remise_value',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CommandeFournisseurItem::class);
    }

    public function reglements(): HasMany
    {
        return $this->hasMany(ReglementFournisseur::class);
    }

    public function avoirs(): HasMany
    {
        return $this->hasMany(AvoirFournisseur::class);
    }

    public function montantBrutHt(): float
    {
        return (float) $this->items()->sum('total');
    }

    public function montantRemise(): float
    {
        $brut = $this->montantBrutHt();

        if ($this->remise_type === 'percent') {
            return round($brut * ((float) $this->remise_value) / 100, 2);
        }

        return min((float) $this->remise_value, $brut);
    }

    public function recalculateTotals(): void
    {
        $brutHt = $this->montantBrutHt();
        $remise = $this->remise_type === 'percent'
            ? round($brutHt * ((float) $this->remise_value) / 100, 2)
            : min((float) $this->remise_value, $brutHt);

        $totalHt = $brutHt - $remise;
        $totalTtc = $totalHt * (1 + $this->tva_rate / 100);

        $this->update(['total_ht' => $totalHt, 'total' => $totalTtc]);
    }

    public function markReceived(): void
    {
        if ($this->status === 'received') {
            return; // already in stock: never add the goods twice
        }
        foreach ($this->items as $item) {
            $product = $item->product;
            $product->increment('stock_qty', $item->quantity);
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'entry',
                'quantity' => $item->quantity,
                'reference_type' => 'commande_fournisseur',
                'reference_id' => $this->id,
                'date' => now(),
            ]);
        }
        $this->update(['status' => 'received']);
    }
}
