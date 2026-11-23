<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementBancaire extends Model
{
    use ScopedToDemoMode;

    protected $table = 'mouvements_bancaires';

    protected $fillable = [
        'tresorerie_compte_id', 'type', 'montant', 'date',
        'reference_type', 'reference_id', 'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function tresorerieCompte(): BelongsTo
    {
        return $this->belongsTo(TresorerieCompte::class, 'tresorerie_compte_id');
    }
}
