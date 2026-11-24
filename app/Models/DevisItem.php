<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevisItem extends Model
{
    protected $fillable = [
        'devis_id', 'product_id', 'quantity', 'unit_price', 'tva_rate',
        'remise_type', 'remise_value', 'total',
    ];

    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
