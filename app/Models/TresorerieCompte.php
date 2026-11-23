<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TresorerieCompte extends Model
{
    use ScopedToDemoMode;

    protected $table = 'tresorerie_comptes';

    protected $fillable = [
        'name', 'type', 'bank_name', 'rib', 'initial_balance', 'is_active',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementBancaire::class, 'tresorerie_compte_id');
    }

    public function currentBalance(): float
    {
        $entries = $this->mouvements()->where('type', 'entry')->sum('montant');
        $exits = $this->mouvements()->where('type', 'exit')->sum('montant');

        return (float) $this->initial_balance + (float) $entries - (float) $exits;
    }
}
