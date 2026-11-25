<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use ScopedToDemoMode;

    protected $fillable = ['name', 'ice', 'phone', 'email', 'address', 'credit_limit'];

    public function devis()
    {
        return $this->hasMany(Devis::class);
    }

    public function bonLivraisons()
    {
        return $this->hasMany(BonLivraison::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function reglements()
    {
        return $this->hasMany(ReglementClient::class);
    }

    public function avoirs()
    {
        return $this->hasMany(AvoirClient::class);
    }

    public function solde(): float
    {
        $totalFactures = $this->factures()->whereNull('caisse_ticket_id')->sum('total_ttc');
        $totalRegle = $this->reglements()->sum('montant');
        $totalAvoirs = $this->avoirs()->sum('montant');

        return (float) $totalFactures - (float) $totalRegle - (float) $totalAvoirs;
    }

    public function isOverCreditLimit(): bool
    {
        return $this->credit_limit !== null
            && $this->credit_limit > 0
            && $this->solde() > (float) $this->credit_limit;
    }
}
