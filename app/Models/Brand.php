<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use ScopedToDemoMode;

    protected $fillable = ['name'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
