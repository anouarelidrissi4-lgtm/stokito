<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandeFournisseurItem extends Model
{
    protected $fillable = ['commande_fournisseur_id', 'product_id', 'quantity', 'unit_price', 'total'];

    public function commandeFournisseur(): BelongsTo
    {
        return $this->belongsTo(CommandeFournisseur::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
