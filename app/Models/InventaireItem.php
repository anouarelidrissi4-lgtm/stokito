<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventaireItem extends Model
{
    protected $fillable = ['inventaire_id', 'product_id', 'stock_systeme', 'stock_compte', 'ecart', 'purchase_price'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
