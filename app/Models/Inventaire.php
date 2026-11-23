<?php

namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventaire extends Model
{
    use ScopedToDemoMode;

    protected $fillable = ['numero', 'date', 'user_id', 'notes', 'nb_produits', 'nb_ecarts'];

    protected $casts = ['date' => 'date'];

    public function items(): HasMany
    {
        return $this->hasMany(InventaireItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
