<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use ScopedToDemoMode;

    protected $fillable = ['code', 'name', 'prenom', 'ice', 'phone', 'email', 'address', 'credit_limit'];

    // client code CL-0001, CL-0002… per company, given automatically when left empty (2026-11-25)
    protected static function booted(): void
    {
        static::creating(function (self $c) {
            if (blank($c->code)) {
                $c->code = static::nextCode($c->project_id, (bool) $c->is_demo);
            }
        });
    }

    public static function nextCode(?int $projectId, bool $isDemo): string
    {
        $max = static::withoutGlobalScopes()->where('project_id', $projectId)->where('is_demo', $isDemo)
            ->where('code', 'like', 'CL-%')->pluck('code')
            ->map(fn ($code) => (int) preg_replace('/\D/', '', substr($code, 3)))->max() ?? 0;

        return 'CL-'.str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    /** "Prénom Nom" when a first name is set, else the name (company name). */
    public function getFullNameAttribute(): string
    {
        return trim(($this->prenom ? $this->prenom.' ' : '').$this->name);
    }

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
