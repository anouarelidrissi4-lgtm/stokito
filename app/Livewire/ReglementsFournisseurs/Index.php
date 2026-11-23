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

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(ReglementFournisseur::findOrFail($id))) {
            session()->flash('success', 'Règlement supprimé.');
        }
    }

    public function render()
    {
        $reglements = ReglementFournisseur::query()
            ->with(['fournisseur', 'commandeFournisseur'])
            ->when($this->search, fn ($q) => $q->whereHas('fournisseur', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->latest('date')
            ->paginate(10);

        return view('livewire.reglements-fournisseurs.index', [
            'reglements' => $reglements,
            'fournisseurs' => Fournisseur::orderBy('name')->get(),
            'comptes' => TresorerieCompte::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
