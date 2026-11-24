<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonLivraisonItem extends Model
{
    protected $fillable = ['bon_livraison_id', 'product_id', 'quantity', 'unit_price', 'tva_rate'];

    public function bonLivraison(): BelongsTo
    {
        return $this->belongsTo(BonLivraison::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
