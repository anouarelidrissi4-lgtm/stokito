<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReglementClient extends Model
{
    use ScopedToDemoMode;

    protected $table = 'reglements_clients';

    protected $fillable = [
        'client_id', 'facture_id', 'tresorerie_compte_id',
        'montant', 'date', 'mode', 'numero_cheque', 'notes',
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

    public function tresorerieCompte(): BelongsTo
    {
        return $this->belongsTo(TresorerieCompte::class, 'tresorerie_compte_id');
    }

    /**
     * Logs the cash-in movement on the chosen account and refreshes the invoice status.
     */
    public function applyEffects(): void
    {
        if ($this->tresorerie_compte_id) {
            MouvementBancaire::create([
                'tresorerie_compte_id' => $this->tresorerie_compte_id,
                'type' => 'entry',
                'montant' => $this->montant,
                'date' => $this->date,
                'reference_type' => 'reglement_client',
                'reference_id' => $this->id,
                'description' => 'Reglement client #'.$this->client_id,
            ]);
        }

        if ($this->facture_id) {
            $this->facture->refreshStatus();
        }
    }
}
