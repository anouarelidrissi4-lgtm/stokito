<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonLivraisonItem extends Model
{
    protected $fillable = ['bon_livraison_id', 'product_id', 'quantity', 'unit_price', 'remise_type', 'remise_value', 'total', 'tva_rate'];

    public function bonLivraison(): BelongsTo
    {
        return $this->belongsTo(BonLivraison::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /* ---- réduction de la ligne (2026-11-27) ---- */

    public function brut(): float
    {
        return (float) $this->quantity * (float) ($this->unit_price ?? 0);
    }

    public function remiseMontant(): float
    {
        return self::remiseFor($this->brut(), (string) ($this->remise_type ?? 'percent'), (float) ($this->remise_value ?? 0));
    }

    /** What the line is worth after its own discount. */
    public function net(): float
    {
        return round($this->brut() - $this->remiseMontant(), 2);
    }

    /** Net unit price, to carry the line discount into an invoice (whose lines have no discount). */
    public function netUnitPrice(): float
    {
        $q = (float) $this->quantity;

        return $q > 0 && $this->remiseMontant() > 0 ? round($this->net() / $q, 2) : (float) ($this->unit_price ?? 0);
    }

    /** "remise 10 %" / "remise 5,00", or null. */
    public function remiseLabel(): ?string
    {
        if ($this->remiseMontant() <= 0) {
            return null;
        }

        return 'remise '.($this->remise_type === 'fixed' ? \App\Support\Fmt::money($this->remise_value) : \App\Support\DocTotals::label($this->remise_value).' %');
    }

    public static function remiseFor(float $brut, string $type, float $value): float
    {
        if ($value <= 0 || $brut <= 0) {
            return 0.0;
        }

        return $type === 'fixed' ? min(round($value, 2), $brut) : round($brut * min($value, 100) / 100, 2);
    }
}
