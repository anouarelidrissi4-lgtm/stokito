<?php

namespace App\Livewire\Concerns;

use App\Models\Product;
use App\Models\Societe;
use App\Support\DocTotals;

/**
 * Per-line VAT for the devis / bon de livraison / facture forms (2026-11-24).
 * $this->tva_rate stays the document's default rate; each line carries its own 'tva_rate'.
 */
trait HasLineVat
{
    public function lineRate(array $item): float
    {
        $r = $item['tva_rate'] ?? '';

        return (float) ($r === '' || $r === null ? $this->tva_rate : $r);
    }

    /** Rate for a product line: the product's own rate, else the document default. */
    protected function productRate(Product $product): float
    {
        return $product->tva_rate !== null ? (float) $product->tva_rate : (float) $this->tva_rate;
    }

    /** Default rate for a new document: 0 when the company is under the VAT franchise (art. 293 B CGI). */
    protected function defaultVatRate(): float
    {
        $s = Societe::current();

        return $s->tva_franchise ? 0.0 : (float) $s->tva_rate_default;
    }

    public function getVatProperty(): array
    {
        $lines = collect($this->items)->map(fn ($i) => [
            method_exists($this, 'ligneTotal') ? $this->ligneTotal($i) : (float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0),
            $this->lineRate($i),
        ])->all();

        $remise = method_exists($this, 'getRemiseGlobaleProperty') ? $this->remiseGlobale : $this->montantRemise;

        return DocTotals::compute($lines, (float) $remise);
    }

    /** Suggested rates for the line input (free value still allowed). */
    public function vatRateOptions(): array
    {
        $c = \App\Support\Countries::for(Societe::current());

        return $c['rates'] ?? [$c['vat'], 0];
    }
}
