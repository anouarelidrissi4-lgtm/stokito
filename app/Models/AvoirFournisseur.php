<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvoirFournisseur extends Model
{
    use ScopedToDemoMode;

    protected $table = 'avoirs_fournisseurs';

    protected $fillable = [
        'fournisseur_id', 'commande_fournisseur_id', 'numero', 'date', 'montant', 'motif', 'notes',
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
}
