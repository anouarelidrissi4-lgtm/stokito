<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    use ScopedToDemoMode;
    use Concerns\HasVatLines;

    protected static function booted(): void
    {
        // give the stock back before the document disappears (see App\Support\StockSync)
        static::deleting(fn ($doc) => \App\Support\StockSync::facture($doc, true));
    }

    protected $fillable = [
        'client_id', 'bon_livraison_id', 'devis_id', 'numero', 'date', 'due_date',
        'total_ht', 'tva_rate', 'total_ttc', 'montant_paye', 'status', 'notes', 'adresse_livraison', 'nature_operation',
        'remise_type', 'remise_value',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }

    public function bonLivraison(): BelongsTo
    {
        return $this->belongsTo(BonLivraison::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FactureItem::class);
    }

    public function reglements(): HasMany
    {
        return $this->hasMany(ReglementClient::class);
    }

    public function avoirs(): HasMany
    {
        return $this->hasMany(AvoirClient::class);
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
        $totalTtc = $this->vatTotals()['ttc'];

        $this->update(['total_ht' => $totalHt, 'total_ttc' => $totalTtc]);
    }

    public function refreshStatus(): void
    {
        $paye = $this->reglements()->sum('montant');
        $avoirs = $this->avoirs()->sum('montant');
        $this->montant_paye = $paye;

        $reste = (float) $this->total_ttc - (float) $paye - (float) $avoirs;

        if ($reste <= 0.01) {
            $this->status = 'payee';
        } elseif ($paye > 0) {
            $this->status = 'partiellement_payee';
        } elseif ($this->due_date && $this->due_date->isPast()) {
            $this->status = 'en_retard';
        }

        $this->save();
    }

    public function resteAPayer(): float
    {
        return (float) $this->total_ttc - (float) $this->montant_paye - (float) $this->avoirs()->sum('montant');
    }
}
