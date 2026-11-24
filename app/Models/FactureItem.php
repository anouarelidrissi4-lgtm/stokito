<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactureItem extends Model
{
    protected $fillable = ['facture_id', 'product_id', 'quantity', 'unit_price', 'tva_rate', 'total'];

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
