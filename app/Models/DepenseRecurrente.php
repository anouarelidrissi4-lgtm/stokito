<?php

namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A recurring expense (2026-11-26): rent, Internet, salaries... An expense (Depense) is created on
 * each due date — by the daily command depenses:recurrentes, or right away when the date has passed.
 * Day 29-31 are clamped to the last day of shorter months.
 */
class DepenseRecurrente extends Model
{
    use ScopedToDemoMode;

    protected $table = 'depenses_recurrentes';

    public const FREQUENCES = [
        'mensuelle' => 'Chaque mois',
        'trimestrielle' => 'Tous les 3 mois',
        'annuelle' => 'Chaque année',
    ];

    /** never create more than this many expenses in one run (a template forgotten for years) */
    public const MAX_PAR_PASSAGE = 36;

    protected $fillable = [
        'libelle', 'categorie', 'fournisseur_id', 'montant_ttc', 'montant_tva', 'mode_paiement', 'tresorerie_compte_id',
        'frequence', 'jour', 'date_debut', 'prochaine_date', 'date_fin', 'actif', 'notes', 'user_id',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'prochaine_date' => 'date',
        'date_fin' => 'date',
        'actif' => 'boolean',
        'montant_ttc' => 'decimal:2',
        'montant_tva' => 'decimal:2',
    ];

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function tresorerieCompte(): BelongsTo
    {
        return $this->belongsTo(TresorerieCompte::class, 'tresorerie_compte_id');
    }

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class, 'depense_recurrente_id');
    }

    public function categorieLabel(): string
    {
        return Depense::CATEGORIES[$this->categorie] ?? ucfirst((string) $this->categorie);
    }

    public function frequenceLabel(): string
    {
        return match ($this->frequence) {
            'trimestrielle' => 'Le '.$this->jour.' tous les 3 mois',
            'annuelle' => 'Le '.$this->prochaine_date?->locale('fr')->isoFormat('D MMMM').' chaque année',
            default => 'Le '.$this->jour.' de chaque mois',
        };
    }

    /** the given month at day $jour (clamped to the month's last day) */
    public static function dansLeMois(Carbon $mois, int $jour): Carbon
    {
        $m = $mois->copy()->startOfMonth();

        return $m->day(min($jour, $m->daysInMonth));
    }

    /** first due date on or after $from */
    public static function premiereEcheance(Carbon $from, int $jour): Carbon
    {
        $d = self::dansLeMois($from, $jour);

        return $d->lt($from->copy()->startOfDay()) ? self::dansLeMois($from->copy()->startOfMonth()->addMonthNoOverflow(), $jour) : $d;
    }

    public function suivante(Carbon $d): Carbon
    {
        $months = match ($this->frequence) {
            'trimestrielle' => 3,
            'annuelle' => 12,
            default => 1,
        };

        return self::dansLeMois($d->copy()->startOfMonth()->addMonthsNoOverflow($months), (int) $this->jour);
    }

    /**
     * Creates the expenses whose date has come (up to $today) and moves prochaine_date forward.
     * Must run in the company's context (logged-in user), like any Depense creation.
     */
    public function genererEcheances(?Carbon $today = null): int
    {
        $today = ($today ?? today())->copy()->startOfDay();
        $n = 0;
        while ($this->actif && $this->prochaine_date && $this->prochaine_date->lte($today)
            && (! $this->date_fin || $this->prochaine_date->lte($this->date_fin)) && $n < self::MAX_PAR_PASSAGE) {
            $dep = Depense::create([
                'date' => $this->prochaine_date->toDateString(),
                'categorie' => $this->categorie,
                'libelle' => $this->libelle,
                'fournisseur_id' => $this->fournisseur_id,
                'montant_ttc' => $this->montant_ttc,
                'montant_tva' => $this->montant_tva,
                'mode_paiement' => $this->mode_paiement,
                'tresorerie_compte_id' => $this->tresorerie_compte_id,
                'notes' => 'Dépense récurrente',
                'user_id' => $this->user_id,
                'depense_recurrente_id' => $this->id,
            ]);
            $dep->syncTresorerie();
            $this->prochaine_date = $this->suivante($this->prochaine_date);
            $n++;
        }
        if ($this->date_fin && $this->prochaine_date && $this->prochaine_date->gt($this->date_fin)) {
            $this->actif = false;
        }
        if ($this->isDirty()) {
            $this->save();
        }

        return $n;
    }
}
