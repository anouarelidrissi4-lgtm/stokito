<?php

namespace App\Models;

use App\Models\Concerns\AppendOnly;
use Illuminate\Database\Eloquent\Model;

class CaissePaiement extends Model
{
    use AppendOnly;

    protected $table = 'caisse_paiements';

    protected $guarded = [];
}
