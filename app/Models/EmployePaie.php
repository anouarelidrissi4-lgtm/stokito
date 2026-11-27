<?php

namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A pay for a period: base (days × rate, or the week / month minus absences) + bonus − advances − loan repayment = net.
 * The net paid is recorded as a "salaires" expense (depense_id). Deleting a pay gives the advances and loan repayments back.
 */
class EmployePaie extends Model
{
    use ScopedToDemoMode;

    protected $fillable = ['employe_id', 'periode_debut', 'periode_fin', 'jours', 'base', 'prime', 'avances', 'retenue_credit', 'credit_allocation',
        'net', 'date_paiement', 'mode_paiement', 'tresorerie_compte_id', 'depense_id', 'notes'];

    protected $casts = [
        'periode_debut' => 'date', 'periode_fin' => 'date', 'date_paiement' => 'date', 'credit_allocation' => 'array',
        'jours' => 'decimal:1', 'base' => 'decimal:2', 'prime' => 'decimal:2', 'avances' => 'decimal:2',
        'retenue_credit' => 'decimal:2', 'net' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::deleting(function (EmployePaie $p) {
            EmployeAvance::where('paie_id', $p->id)->update(['paie_id' => null]);
            foreach ((array) $p->credit_allocation as $creditId => $amount) {
                if ($c = EmployeCredit::find($creditId)) {
                    $c->update(['rembourse' => max(0, (float) $c->rembourse - (float) $amount)]);
                }
            }
            if ($p->depense_id) {
                Depense::find($p->depense_id)?->delete();
            }
        });
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    public function avancesDeduites(): HasMany
    {
        return $this->hasMany(EmployeAvance::class, 'paie_id');
    }

    public function periodeLabel(): string
    {
        return $this->periode_debut->format('d/m/Y').' → '.$this->periode_fin->format('d/m/Y');
    }
}
