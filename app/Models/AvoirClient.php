<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvoirClient extends Model
{
    use ScopedToDemoMode;

    protected $table = 'avoirs_clients';

    protected $fillable = [
        'client_id', 'facture_id', 'numero', 'date', 'montant', 'motif', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }
}
