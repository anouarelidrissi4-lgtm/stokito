<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A product line of a loading slip; always reached through its (tenant-scoped) BonDeChargement. */
class BonChargementLigne extends Model
{
    protected $table = 'bon_chargement_lignes';

    protected $fillable = ['product_id', 'designation', 'unite', 'quantite_commandee', 'quantite_chargee', 'position'];

    protected $casts = ['quantite_commandee' => 'float', 'quantite_chargee' => 'float'];

    public const STATUTS = ['a_charger' => 'À charger', 'partiel' => 'Partiel', 'charge' => 'Chargé'];

    public function statut(): string
    {
        $c = (float) $this->quantite_chargee;

        return $c <= 0 ? 'a_charger' : ($c + 0.0001 < (float) $this->quantite_commandee ? 'partiel' : 'charge');
    }

    public function bonDeChargement(): BelongsTo
    {
        return $this->belongsTo(BonDeChargement::class);
    }
}
