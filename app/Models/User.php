<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'first_name', 'last_name', 'login', 'email', 'password',
    'role', 'status', 'permissions', 'project_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_demo' => 'boolean',
            'is_superadmin' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDemo(): bool
    {
        return (bool) $this->is_demo;
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_superadmin;
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    /**
     * Granular permission check. Admins always pass (their role already
     * grants full access); for non-admins this looks at the stored
     * "permissions" list, matching the checklist on the Utilisateur form.
     */
    public function hasPermission(string $key): bool
    {
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return true;
        }

        return in_array($key, $this->permissions ?? [], true);
    }

    public function rappels()
    {
        return $this->hasMany(Rappel::class);
    }
}
