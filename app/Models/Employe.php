<?php

namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Module Personnel (2026-11-28): a worker paid by the day, the week or the month. */
class Employe extends Model
{
    use ScopedToDemoMode;

    public const TYPES = ['jour' => 'Par jour', 'semaine' => 'Par semaine', 'mois' => 'Par mois'];

    public const UNITES = ['jour' => '/ jour', 'semaine' => '/ semaine', 'mois' => '/ mois'];

    protected $fillable = ['nom', 'poste', 'telephone', 'cin', 'date_entree', 'type_paie', 'taux', 'actif', 'notes'];

    protected $casts = ['date_entree' => 'date', 'taux' => 'decimal:2', 'actif' => 'boolean'];

    public function presences(): HasMany
    {
        return $this->hasMany(EmployePresence::class);
    }

    public function avances(): HasMany
    {
        return $this->hasMany(EmployeAvance::class);
    }

    public function credits(): HasMany
    {
        return $this->hasMany(EmployeCredit::class);
    }

    public function paies(): HasMany
    {
        return $this->hasMany(EmployePaie::class);
    }

    /** Advances given and not yet taken from a pay. */
    public function avancesEnCours(): float
    {
        return (float) $this->avances()->whereNull('paie_id')->sum('montant');
    }

    /** What is still owed on the loans. */
    public function creditRestant(): float
    {
        return (float) $this->credits()->get()->sum(fn ($c) => $c->restant());
    }

    public function tauxLabel(): string
    {
        return \App\Support\Fmt::money($this->taux).' '.(self::UNITES[$this->type_paie] ?? '');
    }
}
