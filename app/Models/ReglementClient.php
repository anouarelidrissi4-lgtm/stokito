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
        'client_id', 'facture_id', 'bon_livraison_id', 'tresorerie_compte_id',
        'montant', 'date', 'mode', 'numero_cheque', 'banque', 'date_echeance', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'date_echeance' => 'date',
    ];

    protected static function booted(): void
    {
        // a deleted payment leaves no treasury entry behind and the invoice goes back to unpaid (2026-11-26)
        static::deleted(function (ReglementClient $r) {
            MouvementBancaire::where('reference_type', 'reglement_client')->where('reference_id', $r->id)->delete();
            if ($r->facture_id && ($f = Facture::find($r->facture_id))) {
                $f->refreshStatus();
            }
        });
    }

    /** undo applyEffects() before an edit (the invoice status is refreshed again by applyEffects) */
    public function removeEffects(): void
    {
        MouvementBancaire::where('reference_type', 'reglement_client')->where('reference_id', $this->id)->delete();
    }

    public function modeLabel(): string
    {
        return \App\Support\SalesLists::MODES[$this->mode] ?? ucfirst((string) $this->mode);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function bonLivraison(): BelongsTo
    {
        return $this->belongsTo(BonLivraison::class);
    }

    /** the document it pays: invoice first, else delivery note */
    public function documentNumero(): ?string
    {
        return $this->facture?->numero ?? $this->bonLivraison?->numero;
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
