<?php

namespace App\Models;

use App\Models\Concerns\AppendOnly;
use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;

/** Z closure (jour / mois / annee) with the perpetual grand totals. Append-only, chained per type. */
class CaisseCloture extends Model
{
    use AppendOnly, ScopedToDemoMode;

    protected $guarded = [];

    protected $casts = [
        'tva_detail' => 'array',
        'paiements' => 'array',
        'sequence' => 'integer',
    ];
}
