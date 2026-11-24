<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use ScopedToDemoMode;

    protected $fillable = [
        'category_id', 'brand_id', 'fournisseur_id', 'reference', 'barcode', 'name', 'unit',
        'purchase_price', 'sale_price', 'wholesale_price', 'tva_rate', 'stock_qty', 'stock_alert_threshold',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_alert_threshold !== null && $this->stock_qty <= $this->stock_alert_threshold;
    }
}
