<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devis extends Model
{
    use ScopedToDemoMode;

    protected $table = 'devis';

    protected $fillable = [
        'client_id', 'numero', 'date', 'status',
        'total_ht', 'tva_rate', 'total_ttc', 'notes',
        'remise_type', 'remise_value',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DevisItem::class);
    }

    public function bonLivraisons(): HasMany
    {
        return $this->hasMany(BonLivraison::class);
    }

    /**
     * Always queries the items fresh from the database rather than relying
     * on a possibly-stale cached relation (important right after items are
     * deleted/recreated in Form::save(), before recalculateTotals() runs).
     */
    protected function freshItems()
    {
        return $this->items()->with('product')->get();
    }

    /**
     * Sum of quantity*unit_price across all lines, before any per-line or
     * global discount ("Sous-total" in the UI).
     */
    public function montantBrutHt(): float
    {
        return (float) $this->freshItems()->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price);
    }

    /**
     * Sum of each line's own discount amount.
     */
    public function montantRemiseLignes(): float
    {
        return (float) $this->freshItems()->sum(function ($i) {
            $brutLigne = (float) $i->quantity * (float) $i->unit_price;

            if ($i->remise_type === 'percent') {
                return round($brutLigne * ((float) $i->remise_value) / 100, 2);
            }

            return min((float) $i->remise_value, $brutLigne);
        });
    }

    /**
     * The global "Autre reduction" amount, applied on top of the lines'
     * own discounts.
     */
    public function montantRemiseGlobale(): float
    {
        $apresLignes = $this->montantBrutHt() - $this->montantRemiseLignes();

        if ($this->remise_type === 'percent') {
            return round($apresLignes * ((float) $this->remise_value) / 100, 2);
        }

        return min((float) $this->remise_value, $apresLignes);
    }

    /**
     * Combined discount (lines + global), shown as a single "Reduction"
     * line in the UI/PDF totals.
     */
    public function montantRemise(): float
    {
        return $this->montantRemiseLignes() + $this->montantRemiseGlobale();
    }

    /**
     * Total margin (sale - purchase price) across all lines.
     */
    public function margeTotale(): float
    {
        return (float) $this->freshItems()->sum(function ($i) {
            $achat = (float) ($i->product->purchase_price ?? 0);

            return ((float) $i->unit_price - $achat) * (float) $i->quantity
                - $this->ligneRemiseMontant($i);
        });
    }

    protected function ligneRemiseMontant(DevisItem $i): float
    {
        $brutLigne = (float) $i->quantity * (float) $i->unit_price;

        if ($i->remise_type === 'percent') {
            return round($brutLigne * ((float) $i->remise_value) / 100, 2);
        }

        return min((float) $i->remise_value, $brutLigne);
    }

    public function recalculateTotals(): void
    {
        $brutHt = $this->montantBrutHt();
        $remiseLignes = $this->montantRemiseLignes();
        $apresLignes = $brutHt - $remiseLignes;

        $remiseGlobale = $this->remise_type === 'percent'
            ? round($apresLignes * ((float) $this->remise_value) / 100, 2)
            : min((float) $this->remise_value, $apresLignes);

        $totalHt = $apresLignes - $remiseGlobale;
        $totalTtc = $totalHt * (1 + $this->tva_rate / 100);

        $this->update(['total_ht' => $totalHt, 'total_ttc' => $totalTtc]);
    }
}
