<?php

namespace App\Models;

use App\Models\Concerns\AppendOnly;
use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** A cash-register ticket (sale or cancellation). Append-only, chained by hash — see App\Support\Caisse. */
class CaisseTicket extends Model
{
    use AppendOnly, ScopedToDemoMode;

    protected $guarded = [];

    protected $casts = [
        'tva_detail' => 'array',
        'sequence' => 'integer',
    ];

    public function lignes(): HasMany
    {
        return $this->hasMany(CaisseTicketLigne::class, 'ticket_id')->orderBy('id');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(CaissePaiement::class, 'ticket_id')->orderBy('id');
    }

    /** The cancellation ticket of this sale, if any. */
    public function annulation(): HasOne
    {
        return $this->hasOne(self::class, 'annule_ticket_id');
    }

    /** For a cancellation ticket: the sale it cancels. */
    public function annule(): BelongsTo
    {
        return $this->belongsTo(self::class, 'annule_ticket_id');
    }

    public function isAnnulation(): bool
    {
        return $this->type === 'annulation';
    }
}
