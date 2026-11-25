<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devis extends Model
{
    use ScopedToDemoMode;
    use Concerns\HasVatLines;

    protected $table = 'devis';

    protected $fillable = [
        'client_id', 'numero', 'date', 'valid_until', 'status', 'sent_at',
        'total_ht', 'tva_rate', 'total_ttc', 'notes', 'objet',
        'remise_type', 'remise_value',
    ];

    protected $casts = [
        'date' => 'date',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DevisItem::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }

    /** Still a proposal and past its validity date. */
    public function isExpired(): bool
    {
        return $this->valid_until && in_array($this->status, ['draft', 'sent'], true) && $this->valid_until->lt(today());
    }

    /** Lines for an invoice/BL made from this quote: a per-line discount is carried in the net unit price. */
    public function linesForConversion(): array
    {
        return $this->items()->get()->map(function ($item) {
            $qty = (float) $item->quantity;
            $price = (float) $item->unit_price;
            if ($qty > 0 && (float) $item->remise_value > 0) {
                $price = round((float) $item->total / $qty, 2);
            }

            return [
                'product_id' => $item->product_id,
                'quantity' => $qty,
                'unit_price' => $price,
                'tva_rate' => (float) ($item->tva_rate ?? $this->tva_rate),
            ];
        })->all();
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

    /**
     * Creates the delivery note for this quote (2026-11-24). Keeps the VAT rates and the global
     * discount; a per-line discount (no such column on the BL) is carried in the net unit price,
     * so the BL and then the invoice keep the quoted amount.
     */
    public function toBonLivraison(): BonLivraison
    {
        $bl = BonLivraison::create([
            'client_id' => $this->client_id,
            'devis_id' => $this->id,
            'numero' => \App\Support\Tenant::nextNumero(BonLivraison::class, 'BL-'),
            'date' => now()->format('Y-m-d'),
            'show_prices' => true,
            'status' => 'draft',
            'tva_rate' => $this->tva_rate,
            'remise_type' => $this->remise_type ?? 'percent',
            'remise_value' => $this->remise_value ?? 0,
            'total' => $this->total_ttc,
        ]);

        foreach ($this->linesForConversion() as $line) {
            $bl->items()->create($line);
        }

        $bl->load('items');
        $bl->recalculateTotals();

        return $bl;
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
        $totalTtc = $this->vatTotals()['ttc'];

        $this->update(['total_ht' => $totalHt, 'total_ttc' => $totalTtc]);
    }
}
