<?php

namespace App\Models;

use App\Models\Concerns\AppendOnly;
use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;

/** Technical event journal of the cash register (prints, duplicates, cancellations, closures...). */
class CaisseJournal extends Model
{
    use AppendOnly, ScopedToDemoMode;

    protected $table = 'caisse_journal';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'sequence' => 'integer',
    ];
}
