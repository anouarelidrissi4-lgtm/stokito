<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactureItem extends Model
{
    use \App\Models\Concerns\HasLineDiscount;   // réduction par ligne (2026-11-27)

    protected $fillable = ['facture_id', 'product_id', 'quantity', 'unit_price', 'remise_type', 'remise_value', 'tva_rate', 'total'];

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
