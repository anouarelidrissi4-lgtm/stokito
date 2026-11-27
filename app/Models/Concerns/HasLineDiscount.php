<?php

namespace App\Models\Concerns;

use App\Support\LineDiscount;

/** Lines of a delivery note / invoice with their own discount (remise_type, remise_value, total). 2026-11-27 */
trait HasLineDiscount
{
    public function brut(): float
    {
        return (float) $this->quantity * (float) ($this->unit_price ?? 0);
    }

    public function remiseMontant(): float
    {
        return LineDiscount::amount($this->brut(), (string) ($this->remise_type ?? 'percent'), (float) ($this->remise_value ?? 0));
    }

    /** What the line is worth after its own discount. */
    public function net(): float
    {
        return round($this->brut() - $this->remiseMontant(), 2);
    }

    /** Net unit price, to carry the line discount into a document whose lines have no discount. */
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
        return LineDiscount::amount($brut, $type, $value);
    }
}
