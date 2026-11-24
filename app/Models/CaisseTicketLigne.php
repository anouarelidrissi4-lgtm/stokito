<?php

namespace App\Models;

use App\Models\Concerns\AppendOnly;
use Illuminate\Database\Eloquent\Model;

class CaisseTicketLigne extends Model
{
    use AppendOnly;

    protected $table = 'caisse_ticket_lignes';

    protected $guarded = [];
}
