<?php

namespace App\Livewire\DepensesRecurrentes;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Depense;
use App\Models\DepenseRecurrente;
use App\Models\Fournisseur;
use App\Models\Societe;
use App\Models\TresorerieCompte;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/** Recurring expenses (2026-11-26): rent, Internet, salaries... entered once, created on each due date. */
class Index extends Component
{
    use RequiresPermission;

    public bool $showModal = false;

    public ?int $editId = null;

    public string $libelle = '';
    public string $categorie = 'loyer';
    public $fournisseur_id = '';
    public $montant_ttc = '';
    public $taux_tva = '';
    public $montant_tva = '';
    public string $mode_paiement = 'virement';
    public $tresorerie_compte_id = '';
    public string $frequence = 'mensuelle';
    public $jour = 1;
    public $date_debut;
    public $date_fin = '';
    public string $notes = '';

    public function mount(): void
    {
        $this->requirePermission('depenses');
        if (request()->routeIs('depenses.recurrentes.create')) {
            $this->create();
        }
    }

    public function updatedMontantTtc(): void
    {
        $this->recalcTva();
    }

    public function updatedTauxTva(): void
    {
        $this->recalcTva();
    }

    public function updatedDateDebut(): void
    {
        if (! $this->editId && $this->date_debut) {
            $this->jour = (int) Carbon::parse($this->date_debut)->day;
        }
    }

    protected function recalcTva(): void
    {
        if (is_numeric($this->montant_ttc) && is_numeric($this->taux_tva)) {
            $this->montant_tva = Depense::tvaFromTtc((float) $this->montant_ttc, (float) $this->taux_tva);
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $r = DepenseRecurrente::findOrFail($id);
        $this->resetForm();
        $this->editId = $r->id;
        $this->fill([
            'libelle' => $r->libelle, 'categorie' => $r->categorie, 'fournisseur_id' => $r->fournisseur_id ?? '',
            'montant_ttc' => (string) $r->montant_ttc, 'montant_tva' => (string) $r->montant_tva, 'taux_tva' => '',
            'mode_paiement' => $r->mode_paiement, 'tresorerie_compte_id' => $r->tresorerie_compte_id ?? '',
            'frequence' => $r->frequence, 'jour' => $r->jour, 'date_debut' => $r->prochaine_date->format('Y-m-d'),
            'date_fin' => $r->date_fin?->format('Y-m-d') ?? '', 'notes' => (string) $r->notes,
        ]);
        $this->showModal = true;
    }

    protected function resetForm(): void
    {
        $this->resetErrorBag();
        $this->reset(['editId', 'libelle', 'fournisseur_id', 'montant_ttc', 'montant_tva', 'tresorerie_compte_id', 'date_fin', 'notes']);
        $this->categorie = 'loyer';
        $this->mode_paiement = 'virement';
        $this->frequence = 'mensuelle';
        $this->date_debut = today()->format('Y-m-d');
        $this->jour = (int) today()->day;
        $this->taux_tva = (string) (float) (Societe::current()->tva_rate_default ?? 20);
    }

    public function save(): void
    {
        $this->validate([
            'libelle' => 'required|string|max:255',
            'categorie' => 'required|in:'.implode(',', array_keys(Depense::CATEGORIES)),
            'fournisseur_id' => 'nullable|integer',
            'montant_ttc' => 'required|numeric|min:0.01|max:99999999',
            'montant_tva' => 'nullable|numeric|min:0|lte:montant_ttc',
            'mode_paiement' => 'required|in:'.implode(',', array_keys(Depense::MODES)),
            'tresorerie_compte_id' => 'nullable|integer',
            'frequence' => 'required|in:'.implode(',', array_keys(DepenseRecurrente::FREQUENCES)),
            'jour' => 'required|integer|min:1|max:31',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'notes' => 'nullable|string|max:2000',
        ], [], ['libelle' => 'libellé', 'montant_ttc' => 'montant TTC', 'montant_tva' => 'TVA', 'date_debut' => 'première date', 'date_fin' => 'date de fin']);

        if ($this->fournisseur_id && ! Fournisseur::find($this->fournisseur_id)) {
            $this->addError('fournisseur_id', 'Fournisseur introuvable.');

            return;
        }
        if ($this->tresorerie_compte_id && ! TresorerieCompte::find($this->tresorerie_compte_id)) {
            $this->addError('tresorerie_compte_id', 'Compte introuvable.');

            return;
        }

        $first = DepenseRecurrente::premiereEcheance(Carbon::parse($this->date_debut), (int) $this->jour);
        $data = [
            'libelle' => trim($this->libelle), 'categorie' => $this->categorie, 'fournisseur_id' => $this->fournisseur_id ?: null,
            'montant_ttc' => $this->montant_ttc, 'montant_tva' => $this->montant_tva === '' ? 0 : $this->montant_tva,
            'mode_paiement' => $this->mode_paiement, 'tresorerie_compte_id' => $this->tresorerie_compte_id ?: null,
            'frequence' => $this->frequence, 'jour' => (int) $this->jour,
            'date_fin' => $this->date_fin ?: null, 'notes' => $this->notes ?: null,
        ];

        $created = DB::transaction(function () use ($data, $first) {
            if ($this->editId) {
                $r = DepenseRecurrente::findOrFail($this->editId);
                $r->fill($data + ['prochaine_date' => $first]);
                $r->save();
            } else {
                $r = DepenseRecurrente::create($data + ['date_debut' => $first, 'prochaine_date' => $first, 'actif' => true, 'user_id' => auth()->id()]);
            }

            return $r->genererEcheances();
        });

        $this->showModal = false;
        session()->flash('success', 'Dépense récurrente enregistrée.'.($created ? ' '.$created.' dépense(s) déjà échue(s) créée(s).' : ''));
    }

    public function toggle(int $id): void
    {
        $r = DepenseRecurrente::findOrFail($id);
        if (! $r->actif) {
            // resuming: skip the dates missed while paused
            if ($r->prochaine_date->lt(today())) {
                $r->prochaine_date = DepenseRecurrente::premiereEcheance(today(), (int) $r->jour);
            }
            if ($r->date_fin && $r->prochaine_date->gt($r->date_fin)) {
                session()->flash('error', 'La date de fin est passée : modifiez-la pour reprendre.');

                return;
            }
        }
        $r->actif = ! $r->actif;
        $r->save();
        if ($r->actif) {
            $r->genererEcheances();
        }
    }

    public function delete(int $id): void
    {
        DepenseRecurrente::findOrFail($id)->delete();
        session()->flash('success', 'Dépense récurrente supprimée. Les dépenses déjà créées sont gardées.');
    }

    public function render()
    {
        $items = DepenseRecurrente::with(['fournisseur', 'tresorerieCompte'])->orderByDesc('actif')->orderBy('prochaine_date')->get();
        $parMois = $items->where('actif', true)->sum(fn ($r) => (float) $r->montant_ttc / match ($r->frequence) {
            'trimestrielle' => 3, 'annuelle' => 12, default => 1,
        });

        return view('livewire.depenses-recurrentes.index', [
            'items' => $items,
            'parMois' => round($parMois, 2),
            'fournisseurs' => Fournisseur::orderBy('name')->get(['id', 'name']),
            'comptes' => TresorerieCompte::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'currency' => Societe::current()->currency ?? '',
        ])->layout('layouts.app');
    }
}
