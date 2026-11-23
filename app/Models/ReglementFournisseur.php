<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReglementFournisseur extends Model
{
    use ScopedToDemoMode;

    protected $table = 'reglements_fournisseurs';

    protected $fillable = [
        'fournisseur_id', 'commande_fournisseur_id', 'tresorerie_compte_id',
        'montant', 'date', 'mode', 'numero_cheque', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function commandeFournisseur(): BelongsTo
    {
        return $this->belongsTo(CommandeFournisseur::class);
    }

    public function tresorerieCompte(): BelongsTo
    {
        return $this->belongsTo(TresorerieCompte::class, 'tresorerie_compte_id');
    }

    /**
     * Logs the cash-out movement on the chosen account.
     */
    public function applyEffects(): void
    {
        if ($this->tresorerie_compte_id) {
            MouvementBancaire::create([
                'tresorerie_compte_id' => $this->tresorerie_compte_id,
                'type' => 'exit',
                'montant' => $this->montant,
                'date' => $this->date,
                'reference_type' => 'reglement_fournisseur',
                'reference_id' => $this->id,
                'description' => 'Reglement fournisseur #'.$this->fournisseur_id,
            ]);
        }
    }
}
