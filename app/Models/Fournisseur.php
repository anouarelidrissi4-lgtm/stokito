<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use ScopedToDemoMode;

    protected $fillable = ['name', 'ice', 'phone', 'email', 'address'];

    public function commandes()
    {
        return $this->hasMany(CommandeFournisseur::class);
    }

    public function reglements()
    {
        return $this->hasMany(ReglementFournisseur::class);
    }

    public function avoirs()
    {
        return $this->hasMany(AvoirFournisseur::class);
    }

    public function chequesEffets()
    {
        return $this->hasMany(ChequeEffetFournisseur::class);
    }

    public function solde(): float
    {
        $totalCommandes = $this->commandes()->sum('total');
        $totalRegle = $this->reglements()->sum('montant');
        $totalAvoirs = $this->avoirs()->sum('montant');

        return (float) $totalCommandes - (float) $totalRegle - (float) $totalAvoirs;
    }
}
