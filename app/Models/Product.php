<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use ScopedToDemoMode;

    protected $fillable = [
        'category_id', 'brand_id', 'fournisseur_id', 'reference', 'barcode', 'name', 'image_path', 'unit',
        'purchase_price', 'sale_price', 'wholesale_price', 'tva_rate', 'stock_qty', 'stock_alert_threshold',
        'type', 'conditionnement', 'poids', 'prix_achat_kg',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'poids' => 'decimal:3',
        'prix_achat_kg' => 'decimal:3',
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

    public function imageUrl(): ?string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : null;
    }

    /** Saves an uploaded photo as a light square-ish WebP (max 600 px) and returns its public-disk path. */
    public static function storeImage(string $tmpPath, ?int $projectId): ?string
    {
        $img = @imagecreatefromstring((string) file_get_contents($tmpPath));
        if (! $img) {
            return null;
        }
        if (function_exists('exif_read_data') && ($o = (int) (@exif_read_data($tmpPath)['Orientation'] ?? 1)) && ($a = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0)) {
            $img = imagerotate($img, $a, 0);
        }
        $w = imagesx($img);
        $h = imagesy($img);
        if (max($w, $h) > 600) {
            $r = 600 / max($w, $h);
            $img = imagescale($img, (int) round($w * $r), (int) round($h * $r), IMG_BICUBIC);
        }
        $path = 'products/'.($projectId ?: 0).'/'.\Illuminate\Support\Str::random(32).'.webp';
        ob_start();
        imagewebp($img, null, 80);
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, ob_get_clean());

        return $path;
    }

    public function isLowStock(): bool
    {
        return $this->stock_alert_threshold !== null && $this->stock_qty <= $this->stock_alert_threshold;
    }
}
