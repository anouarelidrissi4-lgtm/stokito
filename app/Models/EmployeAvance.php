<?php

namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A salary advance: paid now (a "salaires" expense), taken from the next pay. */
class EmployeAvance extends Model
{
    use ScopedToDemoMode;

    protected $fillable = ['employe_id', 'date', 'montant', 'mode_paiement', 'tresorerie_compte_id', 'depense_id', 'paie_id', 'notes'];

    protected $casts = ['date' => 'date', 'montant' => 'decimal:2'];

    protected static function booted(): void
    {
        static::deleting(function (EmployeAvance $a) {
            if ($a->depense_id) {
                Depense::find($a->depense_id)?->delete();     // also removes its treasury movement
            }
        });
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    public function paie(): BelongsTo
    {
        return $this->belongsTo(EmployePaie::class, 'paie_id');
    }
}
