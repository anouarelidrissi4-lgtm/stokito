<?php
namespace App\Livewire\AvoirsFournisseurs;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\AvoirFournisseur;
use App\Models\Fournisseur;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|exists:fournisseurs,id')]
    public $fournisseur_id = '';

    public $commande_fournisseur_id = null;

    #[Validate('required|date')]
    public $date;

    #[Validate('required|numeric|min:0.01')]
    public $montant = 0;

    public $motif = '';

    public $notes = '';

    public function mount()
    {
        $this->requirePermission('gestion_avoirs_fournisseurs');
        if (request()->routeIs('avoirs-fournisseurs.create')) {
            $this->create();
        }
        $this->date = now()->format('Y-m-d');
    }

    public function create()
    {
        $this->reset(['editingId', 'fournisseur_id', 'commande_fournisseur_id', 'montant', 'motif', 'notes']);
        $this->date = now()->format('Y-m-d');
        $this->montant = 0;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $avoir = AvoirFournisseur::findOrFail($id);
        $this->editingId = $avoir->id;
        $this->fournisseur_id = $avoir->fournisseur_id;
        $this->commande_fournisseur_id = $avoir->commande_fournisseur_id;
        $this->date = $avoir->date->format('Y-m-d');
        $this->montant = $avoir->montant;
        $this->motif = $avoir->motif;
        $this->notes = $avoir->notes;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'fournisseur_id' => $this->fournisseur_id,
            'commande_fournisseur_id' => $this->commande_fournisseur_id ?: null,
            'date' => $this->date,
            'montant' => $this->montant,
            'motif' => $this->motif,
            'notes' => $this->notes,
        ];

        if ($this->editingId) {
            AvoirFournisseur::findOrFail($this->editingId)->update($data);
        } else {
            $data['numero'] = \App\Support\Tenant::nextNumero(AvoirFournisseur::class, 'AVF-');
            AvoirFournisseur::create($data);
        }

        $this->showModal = false;
        session()->flash('success', 'Avoir enregistré.');
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(AvoirFournisseur::findOrFail($id))) {
            session()->flash('success', 'Avoir supprimé.');
        }
    }

    public function render()
    {
        $avoirs = AvoirFournisseur::query()
            ->with('fournisseur')
            ->when($this->search, fn ($q) => $q->where('numero', 'like', "%{$this->search}%")
                ->orWhereHas('fournisseur', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->latest('date')
            ->paginate(10);

        return view('livewire.avoirs-fournisseurs.index', [
            'avoirs' => $avoirs,
            'fournisseurs' => Fournisseur::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
