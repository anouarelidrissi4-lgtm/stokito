<?php

namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Money lent to a worker, repaid by a monthly amount taken from the pay. Not an expense: lending moves cash
 * (a treasury exit when an account is chosen, reference_type 'credit_employe'), the repayments are netted in the pay.
 */
class EmployeCredit extends Model
{
    use ScopedToDemoMode;

    protected $fillable = ['employe_id', 'date', 'montant', 'mensualite', 'rembourse', 'mode_paiement', 'tresorerie_compte_id', 'notes'];

    protected $casts = ['date' => 'date', 'montant' => 'decimal:2', 'mensualite' => 'decimal:2', 'rembourse' => 'decimal:2'];

    protected static function booted(): void
    {
        static::deleting(function (EmployeCredit $c) {
            MouvementBancaire::where('reference_type', 'credit_employe')->where('reference_id', $c->id)->delete();
        });
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    public function restant(): float
    {
        return max(0, round((float) $this->montant - (float) $this->rembourse, 2));
    }

    public function syncTresorerie(): void
    {
        MouvementBancaire::where('reference_type', 'credit_employe')->where('reference_id', $this->id)->delete();
        if ($this->tresorerie_compte_id) {
            MouvementBancaire::create([
                'tresorerie_compte_id' => $this->tresorerie_compte_id, 'type' => 'exit', 'montant' => $this->montant, 'date' => $this->date,
                'reference_type' => 'credit_employe', 'reference_id' => $this->id,
                'description' => 'Crédit à '.($this->employe->nom ?? 'un employé'),
            ]);
        }
    }
}
