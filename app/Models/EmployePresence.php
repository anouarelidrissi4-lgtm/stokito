<?php

namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One day of attendance (pointage). No row = not marked yet. */
class EmployePresence extends Model
{
    use ScopedToDemoMode;

    /** code => [label, short] — the order is the click cycle of the attendance grid */
    public const STATUTS = [
        'P' => ['Présent', '✓'],
        'D' => ['Demi-journée', '½'],
        'A' => ['Absent', '✗'],
        'C' => ['Congé', 'C'],
        'F' => ['Férié', 'F'],
    ];

    protected $fillable = ['employe_id', 'date', 'statut'];

    protected $casts = ['date' => 'date'];

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }
}
