<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BonLivraison extends Model
{
    use ScopedToDemoMode;
    use Concerns\HasVatLines;

    protected static function booted(): void
    {
        // give the stock back before the document disappears (see App\Support\StockSync)
        static::deleting(fn ($doc) => \App\Support\StockSync::bonLivraison($doc, true));
    }

    protected $fillable = [
        'client_id', 'devis_id', 'numero', 'date',
        'show_prices', 'status', 'total',
        'total_ht', 'tva_rate', 'remise_type', 'remise_value',
    ];

    protected $casts = [
        'date' => 'date',
        'show_prices' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BonLivraisonItem::class);
    }

    public function facture(): HasOne
    {
        return $this->hasOne(Facture::class);
    }

    public function bonsDeChargement(): BelongsToMany
    {
        return $this->belongsToMany(BonDeChargement::class, 'bon_chargement_items')
            ->withTimestamps();
    }

    public function montantBrutHt(): float
    {
        return (float) $this->items->sum(fn ($i) => (float) $i->quantity * (float) ($i->unit_price ?? 0));
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
        $totalTtc = $this->vatTotals()['ttc'];

        $this->update(['total_ht' => $totalHt, 'total' => $totalTtc]);
    }
}
