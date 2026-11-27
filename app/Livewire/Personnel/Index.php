<?php

namespace App\Livewire\Personnel;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Employe;
use App\Models\TresorerieCompte;
use App\Support\Paie;
use App\Support\Plans;
use Livewire\Component;

/** Module Personnel (2026-11-28): the workers, what they earned this month, advances and loans, quick advance. Pro / Premium. */
class Index extends Component
{
    use RequiresPermission;

    public bool $locked = false;

    public string $search = '';

    public bool $inactifs = false;

    /* employee form */
    public bool $showForm = false;

    public ?int $editId = null;

    public string $nom = '';

    public string $poste = '';

    public string $telephone = '';

    public string $cin = '';

    public ?string $date_entree = null;

    public string $type_paie = 'jour';

    public $taux = null;

    public string $notes = '';

    /* quick advance */
    public ?int $avanceFor = null;

    public $avMontant = null;

    public ?string $avDate = null;

    public string $avMode = 'especes';

    public $avCompte = null;

    public string $avNotes = '';

    public function mount(): void
    {
        $this->requirePermission('personnel');
        $this->locked = Plans::current() === 'free';
    }

    public function create(): void
    {
        abort_if($this->locked, 403);
        $this->reset(['editId', 'nom', 'poste', 'telephone', 'cin', 'date_entree', 'taux', 'notes']);
        $this->type_paie = 'jour';
        $this->date_entree = now()->toDateString();
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        abort_if($this->locked, 403);
        $e = Employe::findOrFail($id);
        $this->editId = $e->id;
        $this->fill(['nom' => $e->nom, 'poste' => (string) $e->poste, 'telephone' => (string) $e->telephone, 'cin' => (string) $e->cin,
            'date_entree' => $e->date_entree?->toDateString(), 'type_paie' => $e->type_paie, 'taux' => (float) $e->taux, 'notes' => (string) $e->notes]);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        abort_if($this->locked, 403);
        $this->nom = trim($this->nom);
        $data = $this->validate([
            'nom' => 'required|string|max:255',
            'poste' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:40',
            'cin' => 'nullable|string|max:40',
            'date_entree' => 'nullable|date',
            'type_paie' => 'required|in:jour,semaine,mois',
            'taux' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ], [], ['taux' => 'salaire', 'type_paie' => 'type de paie']);
        $data = array_map(fn ($v) => $v === '' ? null : $v, $data);
        $this->editId ? Employe::findOrFail($this->editId)->update($data) : Employe::create($data + ['actif' => true]);
        $this->showForm = false;
        session()->flash('success', $this->editId ? 'Employé modifié.' : 'Employé ajouté.');
    }

    public function toggleActif(int $id): void
    {
        abort_if($this->locked, 403);
        $e = Employe::findOrFail($id);
        $e->update(['actif' => ! $e->actif]);
    }

    public function openAvance(int $id): void
    {
        abort_if($this->locked, 403);
        Employe::findOrFail($id);
        $this->avanceFor = $id;
        $this->reset(['avMontant', 'avNotes', 'avCompte']);
        $this->avDate = now()->toDateString();
        $this->avMode = 'especes';
        $this->resetValidation();
    }

    public function saveAvance(): void
    {
        abort_if($this->locked, 403);
        $this->validate(['avMontant' => 'required|numeric|min:0.01', 'avDate' => 'required|date', 'avMode' => 'required|in:especes,virement,cheque',
            'avNotes' => 'nullable|string|max:255'], [], ['avMontant' => 'montant', 'avDate' => 'date']);
        if ($this->avCompte && ! TresorerieCompte::find($this->avCompte)) {
            $this->addError('avCompte', 'Compte introuvable.');

            return;
        }
        $e = Employe::findOrFail($this->avanceFor);
        Paie::avance($e, ['date' => $this->avDate, 'montant' => $this->avMontant, 'mode_paiement' => $this->avMode,
            'tresorerie_compte_id' => $this->avCompte, 'notes' => trim($this->avNotes) ?: null]);
        $this->avanceFor = null;
        session()->flash('success', 'Avance enregistrée pour '.$e->nom.'.');
    }

    public function render()
    {
        $employes = collect();
        $totaux = ['apayer' => 0.0, 'avances' => 0.0, 'credits' => 0.0];
        if (! $this->locked) {
            $employes = Employe::query()
                ->when(! $this->inactifs, fn ($q) => $q->where('actif', true))
                ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($w) => $w->where('nom', 'like', '%'.trim($this->search).'%')
                    ->orWhere('poste', 'like', '%'.trim($this->search).'%')->orWhere('telephone', 'like', '%'.trim($this->search).'%')))
                ->orderByDesc('actif')->orderBy('nom')->get()
                ->map(function (Employe $e) use (&$totaux) {
                    [$d, $f] = Paie::periodeParDefaut($e);
                    $c = Paie::calcul($e, $d, $f);
                    $e->setAttribute('_periode', $d->format('d/m').' → '.$f->format('d/m'));
                    $e->setAttribute('_jours', $c['jours']);
                    $e->setAttribute('_avances', $e->avancesEnCours());
                    $e->setAttribute('_credit', $e->creditRestant());
                    $e->setAttribute('_apayer', round($c['base'] - $c['avances'] - $c['retenue_credit'], 2));
                    if ($e->actif) {
                        $totaux['apayer'] += max(0, $e->_apayer);
                        $totaux['avances'] += $e->_avances;
                        $totaux['credits'] += $e->_credit;
                    }

                    return $e;
                });
        }

        return view('livewire.personnel.index', [
            'employes' => $employes,
            'totaux' => $totaux,
            'comptes' => TresorerieCompte::where('is_active', true)->orderBy('name')->get(),
            'currency' => \App\Models\Societe::current()->currency ?? '',
        ])->layout('layouts.app');
    }
}
