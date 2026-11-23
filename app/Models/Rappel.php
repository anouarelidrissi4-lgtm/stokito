<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rappel extends Model
{
    use ScopedToDemoMode;

    protected $fillable = [
        'user_id', 'title', 'description', 'due_date',
        'reference_type', 'reference_id', 'is_done',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_done' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isEnRetard(): bool
    {
        return ! $this->is_done && $this->due_date->isPast();
    }
}
