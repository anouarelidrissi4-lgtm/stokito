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
        'montant', 'date', 'mode', 'numero_cheque', 'notes', 'banque', 'date_echeance', 'tireur',
    ];

    protected $casts = [
        'date' => 'date',
        'date_echeance' => 'date',
    ];

    protected static function booted(): void
    {
        // a deleted payment leaves no treasury entry or pending cheque behind (2026-11-27)
        static::deleting(function (ReglementFournisseur $r) {
            ChequeEffetFournisseur::where('reglement_fournisseur_id', $r->id)->where('status', 'en_attente')->delete();
        });
        static::deleted(function (ReglementFournisseur $r) {
            MouvementBancaire::where('reference_type', 'reglement_fournisseur')->where('reference_id', $r->id)->delete();
        });
    }

    /** undo applyEffects() before an edit */
    public function removeEffects(): void
    {
        MouvementBancaire::where('reference_type', 'reglement_fournisseur')->where('reference_id', $this->id)->delete();
    }

    public function modeLabel(): string
    {
        return \App\Support\SalesLists::MODES[$this->mode] ?? ucfirst((string) $this->mode);
    }

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
