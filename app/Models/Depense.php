<?php

namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A running cost (rent, energy, fuel, salaries...). Paid from a treasury account
 * when tresorerie_compte_id is set: one MouvementBancaire 'exit' (reference_type
 * 'depense') mirrors it and is rebuilt by syncTresorerie() after every save.
 * Receipts live on the PRIVATE local disk (depenses/{project_id}/...), served by
 * the depenses.justificatif route after a permission + tenant check.
 */
class Depense extends Model
{
    use ScopedToDemoMode;

    protected $table = 'depenses';

    public const CATEGORIES = [
        'loyer' => 'Loyer',
        'salaires' => 'Salaires et charges sociales',
        'energie' => 'Électricité, eau, gaz',
        'telecom' => 'Téléphone et Internet',
        'transport' => 'Carburant et transport',
        'fournitures' => 'Fournitures et petit matériel',
        'entretien' => 'Entretien et réparations',
        'assurance' => 'Assurances',
        'impots' => 'Impôts et taxes',
        'banque' => 'Frais bancaires',
        'marketing' => 'Publicité et marketing',
        'honoraires' => 'Comptable et honoraires',
        'autre' => 'Autre',
    ];

    public const MODES = [
        'especes' => 'Espèces',
        'carte' => 'Carte',
        'virement' => 'Virement',
        'cheque' => 'Chèque',
        'prelevement' => 'Prélèvement',
    ];

    protected $fillable = [
        'date', 'categorie', 'libelle', 'fournisseur_id', 'montant_ttc', 'montant_tva',
        'mode_paiement', 'tresorerie_compte_id', 'reference', 'justificatif_path', 'notes', 'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'montant_ttc' => 'decimal:2',
        'montant_tva' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Depense $d) {
            MouvementBancaire::where('reference_type', 'depense')->where('reference_id', $d->id)->delete();
            if ($d->justificatif_path) {
                Storage::disk('local')->delete($d->justificatif_path);
            }
        });
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function tresorerieCompte(): BelongsTo
    {
        return $this->belongsTo(TresorerieCompte::class, 'tresorerie_compte_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMontantHtAttribute(): float
    {
        return round((float) $this->montant_ttc - (float) $this->montant_tva, 2);
    }

    public function categorieLabel(): string
    {
        return self::CATEGORIES[$this->categorie] ?? ucfirst((string) $this->categorie);
    }

    /** VAT included in a TTC amount at the given rate (%). */
    public static function tvaFromTtc(float $ttc, float $rate): float
    {
        return $rate > 0 ? round($ttc - $ttc / (1 + $rate / 100), 2) : 0.0;
    }

    /** Rebuilds the treasury movement mirroring this expense (none when no account is chosen). */
    public function syncTresorerie(): void
    {
        MouvementBancaire::where('reference_type', 'depense')->where('reference_id', $this->id)->delete();
        if ($this->tresorerie_compte_id) {
            MouvementBancaire::create([
                'tresorerie_compte_id' => $this->tresorerie_compte_id,
                'type' => 'exit',
                'montant' => $this->montant_ttc,
                'date' => $this->date,
                'reference_type' => 'depense',
                'reference_id' => $this->id,
                'description' => 'Dépense : '.$this->libelle,
            ]);
        }
    }
}
