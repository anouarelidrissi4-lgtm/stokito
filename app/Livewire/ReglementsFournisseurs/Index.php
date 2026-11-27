<?php
namespace App\Livewire\ReglementsFournisseurs;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\CommandeFournisseur;
use App\Models\Fournisseur;
use App\Models\ReglementFournisseur;
use App\Models\TresorerieCompte;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    /* filtres (2026-11-27) : période sur la date du règlement + plusieurs fournisseurs */
    public string $periode = '';

    public string $from = '';

    public string $to = '';

    public array $fournisseurIds = [];

    public const PERIODES = ['' => 'Toutes les dates', 'mois' => 'Ce mois-ci', 'mois_precedent' => 'Le mois dernier', 'trimestre' => 'Ce trimestre',
        'annee' => 'Cette année', 'annee_precedente' => 'L’année dernière', 'perso' => 'Période personnalisée'];

    public bool $showModal = false;

    #[Validate('required|exists:fournisseurs,id')]
    public $fournisseur_id = '';

    public $commande_fournisseur_id = null;

    public $tresorerie_compte_id = null;

    #[Validate('required|numeric|min:0.01')]
    public $montant = 0;

    #[Validate('required|date')]
    public $date;

    public $mode = 'especes';

    public $numero_cheque = '';

    public $notes = '';

    public function mount($commandeId = null)
    {
        $this->requirePermission('gestion_reglements_fournisseurs');

        $this->date = now()->format('Y-m-d');

        if ($commandeId) {
            $commande = CommandeFournisseur::findOrFail($commandeId);
            $this->fournisseur_id = $commande->fournisseur_id;
            $this->commande_fournisseur_id = $commande->id;
            $this->showModal = true;
        }
    }

    public function create()
    {
        $this->reset(['fournisseur_id', 'commande_fournisseur_id', 'tresorerie_compte_id', 'montant', 'mode', 'numero_cheque', 'notes']);
        $this->montant = 0;
        $this->mode = 'especes';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $reglement = ReglementFournisseur::create([
            'fournisseur_id' => $this->fournisseur_id,
            'commande_fournisseur_id' => $this->commande_fournisseur_id ?: null,
            'tresorerie_compte_id' => $this->tresorerie_compte_id ?: null,
            'montant' => $this->montant,
            'date' => $this->date,
            'mode' => $this->mode,
            'numero_cheque' => $this->mode === 'cheque' ? $this->numero_cheque : null,
            'notes' => $this->notes,
        ]);

        $reglement->applyEffects();

        if ($this->mode === 'cheque' && $this->numero_cheque) {
            \App\Models\ChequeEffetFournisseur::create([
                'fournisseur_id' => $this->fournisseur_id,
                'reglement_fournisseur_id' => $reglement->id,
                'numero_cheque' => $this->numero_cheque,
                'montant' => $this->montant,
                'date_emission' => $this->date,
                'date_echeance' => $this->date,
                'status' => 'en_attente',
            ]);
        }

        $this->showModal = false;
        session()->flash('success', 'Règlement enregistré.');
    }

    public function updatedPeriode(): void
    {
        $n = now();
        [$from, $to] = match ($this->periode) {
            'mois' => [$n->copy()->startOfMonth(), $n->copy()->endOfMonth()],
            'mois_precedent' => [$n->copy()->subMonthNoOverflow()->startOfMonth(), $n->copy()->subMonthNoOverflow()->endOfMonth()],
            'trimestre' => [$n->copy()->startOfQuarter(), $n->copy()->endOfQuarter()],
            'annee' => [$n->copy()->startOfYear(), $n->copy()->endOfYear()],
            'annee_precedente' => [$n->copy()->subYear()->startOfYear(), $n->copy()->subYear()->endOfYear()],
            default => [null, null],
        };
        if ($this->periode !== 'perso') {
            $this->from = $from ? $from->toDateString() : '';
            $this->to = $to ? $to->toDateString() : '';
        }
        $this->resetPage();
    }

    public function updatedFrom(): void
    {
        $this->periode = 'perso';
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->periode = 'perso';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFournisseurIds(): void
    {
        $this->fournisseurIds = array_values(array_unique(array_filter(array_map('intval', $this->fournisseurIds))));
        $this->resetPage();
    }

    public function removeFournisseur($id): void
    {
        $this->fournisseurIds = array_values(array_filter($this->fournisseurIds, fn ($f) => (int) $f !== (int) $id));
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'periode', 'from', 'to', 'fournisseurIds']);
        $this->resetPage();
    }

    /** Règlements filtrés (liste + totaux utilisent la même requête). */
    public static function filtered(string $search, string $from, string $to, array $fournisseurIds)
    {
        $ids = array_values(array_filter(array_map('intval', $fournisseurIds)));

        return ReglementFournisseur::query()
            ->when($search, fn ($q) => $q->whereHas('fournisseur', fn ($q2) => $q2->where('name', 'like', "%{$search}%")))
            ->when($ids, fn ($q) => $q->whereIn('fournisseur_id', $ids))
            ->when(self::validDate($from), fn ($q) => $q->whereDate('date', '>=', $from))
            ->when(self::validDate($to), fn ($q) => $q->whereDate('date', '<=', $to));
    }

    private static function validDate(string $d): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(ReglementFournisseur::findOrFail($id))) {
            session()->flash('success', 'Règlement supprimé.');
        }
    }

    public function render()
    {
        $base = fn () => self::filtered($this->search, $this->from, $this->to, $this->fournisseurIds);
        $reglements = $base()
            ->with(['fournisseur', 'commandeFournisseur'])
            ->latest('date')->latest('id')
            ->paginate(10);
        $total = (float) $base()->sum('montant');
        $count = $base()->count();
        // sous-totaux par fournisseur quand plusieurs sont choisis
        $parFournisseur = count($this->fournisseurIds) > 1
            ? $base()->selectRaw('fournisseur_id, SUM(montant) as total, COUNT(*) as nb')->groupBy('fournisseur_id')->get()->keyBy('fournisseur_id')
            : collect();

        return view('livewire.reglements-fournisseurs.index', [
            'reglements' => $reglements,
            'total' => $total,
            'count' => $count,
            'parFournisseur' => $parFournisseur,
            'fournisseurs' => Fournisseur::orderBy('name')->get(),
            'comptes' => TresorerieCompte::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
