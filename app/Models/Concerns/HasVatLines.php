<?php

namespace App\Models\Concerns;

/** VAT breakdown of a sales document (devis, bon de livraison, facture). A line without its own rate uses the document's. */
trait HasVatLines
{
    public function vatTotals(): array
    {
        $lines = $this->items()->get()->map(fn ($i) => [
            (float) ($i->total ?? ((float) $i->quantity * (float) ($i->unit_price ?? 0))),
            (float) ($i->tva_rate ?? $this->tva_rate ?? 0),
        ])->all();

        $remise = method_exists($this, 'montantRemiseGlobale') ? $this->montantRemiseGlobale() : $this->montantRemise();

        return \App\Support\DocTotals::compute($lines, (float) $remise);
    }
}
