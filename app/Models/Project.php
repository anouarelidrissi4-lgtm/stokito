<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    protected $fillable = ['name', 'is_active', 'country_code', 'contact_phone', 'signup_source', 'plan', 'plan_ends_at', 'plan_period', 'plan_trial'];

    protected static function booted(): void
    {
        // every new company starts with the Premium trial (2026-11-27)
        static::creating(function (Project $p) {
            if (! $p->plan || $p->plan === 'free') {
                $p->plan = 'premium';
                $p->plan_trial = true;
                $p->plan_ends_at = now()->addMonths(\App\Support\Plans::TRIAL_MONTHS);
            }
        });
    }

    protected $casts = [
        'is_active' => 'boolean',
        'plan_ends_at' => 'datetime',
        'plan_trial' => 'boolean',
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
