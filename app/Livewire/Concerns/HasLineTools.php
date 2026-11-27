<?php

namespace App\Livewire\Concerns;

use App\Models\Product;
use App\Support\LineDiscount;

/**
 * Line tools shared by the BL, devis and facture forms (2026-11-27, like Stokio):
 * line discount ("0 ou 0%"), editable TTC price, "Prix détail / Prix gros" under the product.
 * Needs HasLineVat (lineRate, productRate). A form may define its own ligneRemiseMontant / ligneTotal.
 */
trait HasLineTools
{
    public function ligneRemiseMontant(array $item): float
    {
        $brut = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);

        return LineDiscount::amount($brut, (string) ($item['remise_type'] ?? 'percent'), (float) ($item['remise_value'] ?? 0));
    }

    /** "Total ligne": the line after its own discount (also used by HasLineVat). */
    public function ligneTotal(array $item): float
    {
        return round((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0) - $this->ligneRemiseMontant($item), 2);
    }

    public function getMontantRemiseLignesProperty()
    {
        return collect($this->items)->sum(fn ($i) => $this->ligneRemiseMontant($i));
    }

    /** "Prix TTC" typed on a line: the line stays HT, converted with the line's VAT rate. */
    public function setLinePriceTtc($index, $value): void
    {
        if (! isset($this->items[$index])) {
            return;
        }
        $ttc = max(0, (float) str_replace([' ', ','], ['', '.'], (string) $value));
        $this->items[$index]['unit_price'] = round($ttc / (1 + $this->lineRate($this->items[$index]) / 100), 2);
    }

    /** One "0 ou 0%" field: "10%" = 10 percent, "10" = an amount. */
    public function setLineRemise($index, $text): void
    {
        if (! isset($this->items[$index])) {
            return;
        }
        [$type, $value] = LineDiscount::parse($text);
        $this->items[$index]['remise_type'] = $type;
        $this->items[$index]['remise_value'] = $value;
        $this->resetValidation("items.{$index}.remise_value");
    }

    public function ligneRemiseTexte(array $item): string
    {
        return LineDiscount::text((string) ($item['remise_type'] ?? 'percent'), (float) ($item['remise_value'] ?? 0));
    }

    /** "Prix détail" / "Prix gros" under a line: puts that catalogue price (TTC → HT) on the line. */
    public function applyPrice($index, string $which): void
    {
        $item = $this->items[$index] ?? null;
        $product = $item && ! empty($item['product_id']) ? Product::find($item['product_id']) : null;
        if (! $product) {
            return;
        }
        $ttc = $which === 'gros' && $product->wholesale_price !== null ? (float) $product->wholesale_price : (float) $product->sale_price;
        $this->items[$index]['unit_price'] = round($ttc / (1 + $this->productRate($product) / 100), 2);
    }

    /** A percent discount above 100 % is refused before saving. */
    protected function lineDiscountsValid(): bool
    {
        foreach ($this->items as $index => $item) {
            if (($item['remise_type'] ?? 'percent') === 'percent' && (float) ($item['remise_value'] ?? 0) > 100) {
                $this->addError("items.{$index}.remise_value", 'La réduction ne peut pas dépasser 100 %.');

                return false;
            }
        }

        return true;
    }
}
