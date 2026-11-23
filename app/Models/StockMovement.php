<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use ScopedToDemoMode;

    protected $fillable = [
        'product_id', 'type', 'quantity',
        'reference_type', 'reference_id', 'date', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
