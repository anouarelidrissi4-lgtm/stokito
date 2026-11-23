<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BonDeChargement extends Model
{
    use ScopedToDemoMode;

    protected $table = 'bons_de_chargement';

    protected $fillable = ['numero', 'date', 'vehicule', 'chauffeur', 'status', 'notes'];

    protected $casts = [
        'date' => 'date',
    ];

    public function bonLivraisons(): BelongsToMany
    {
        return $this->belongsToMany(BonLivraison::class, 'bon_chargement_items')
            ->withTimestamps();
    }
}
