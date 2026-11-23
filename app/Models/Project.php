<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    protected $fillable = ['name', 'is_active', 'country_code', 'contact_phone', 'signup_source'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function societe(): HasOne
    {
        return $this->hasOne(Societe::class);
    }
}
