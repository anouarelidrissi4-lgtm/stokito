<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BonDeChargement extends Model
{
    use ScopedToDemoMode;

    protected $table = 'bons_de_chargement';

    protected $fillable = ['numero', 'bon_livraison_id', 'date', 'vehicule', 'chauffeur', 'commercial', 'status', 'notes'];

    public const STATUTS = ['preparation' => 'À charger', 'partiel' => 'Partiellement chargé', 'charge' => 'Chargé', 'livre' => 'Livré'];

    protected $casts = [
        'date' => 'date',
    ];

    public function lignes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BonChargementLigne::class)->orderBy('position')->orderBy('id');
    }

    /** the delivery note this slip was made from (automatic slips, 2026-11-27) */
    public function bonLivraison(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BonLivraison::class);
    }

    /** status from the loaded quantities; "livré" is set by hand and kept */
    public function refreshStatus(): void
    {
        if ($this->status === 'livre') {
            return;
        }
        $st = $this->lignes()->get()->map->statut();
        $this->status = $st->isEmpty() || $st->every(fn ($s) => $s === 'a_charger') ? 'preparation' : ($st->every(fn ($s) => $s === 'charge') ? 'charge' : 'partiel');
        $this->save();
    }

    public function bonLivraisons(): BelongsToMany
    {
        return $this->belongsToMany(BonLivraison::class, 'bon_chargement_items')
            ->withTimestamps();
    }
}
