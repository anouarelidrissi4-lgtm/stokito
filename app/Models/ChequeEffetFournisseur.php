<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChequeEffetFournisseur extends Model
{
    use ScopedToDemoMode;

    protected $table = 'cheques_effets_fournisseurs';

    protected $fillable = [
        'fournisseur_id', 'reglement_fournisseur_id', 'numero_cheque', 'banque',
        'montant', 'date_emission', 'date_echeance', 'status', 'notes',
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_echeance' => 'date',
    ];

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function reglementFournisseur(): BelongsTo
    {
        return $this->belongsTo(ReglementFournisseur::class);
    }

    public function isEnRetard(): bool
    {
        return $this->status === 'en_attente' && $this->date_echeance->isPast();
    }
}
