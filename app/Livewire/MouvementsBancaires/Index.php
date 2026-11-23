<?php
namespace App\Livewire\MouvementsBancaires;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\MouvementBancaire;
use App\Models\TresorerieCompte;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $compteFilter = '';

    public bool $showModal = false;

    public function mount()
    {
        $this->requirePermission('mouvements_bancaires');
    }

    #[Validate('required|exists:tresorerie_comptes,id')]
    public $tresorerie_compte_id = '';

    #[Validate('required|in:entry,exit')]
    public $type = 'entry';

    #[Validate('required|numeric|min:0.01')]
    public $montant = 0;

    #[Validate('required|date')]
    public $date;

    public $description = '';

    public function create()
    {
        $this->reset(['tresorerie_compte_id', 'montant', 'description']);
        $this->type = 'entry';
        $this->date = now()->format('Y-m-d');
        $this->montant = 0;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        MouvementBancaire::create([
            'tresorerie_compte_id' => $this->tresorerie_compte_id,
            'type' => $this->type,
            'montant' => $this->montant,
            'date' => $this->date,
            'reference_type' => 'manuel',
            'description' => $this->description,
        ]);

        $this->showModal = false;
        session()->flash('success', 'Mouvement enregistré.');
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(MouvementBancaire::findOrFail($id))) {
            session()->flash('success', 'Mouvement supprimé.');
        }
    }

    public function render()
    {
        $mouvements = MouvementBancaire::query()
            ->with('tresorerieCompte')
            ->when($this->compteFilter, fn ($q) => $q->where('tresorerie_compte_id', $this->compteFilter))
            ->latest('date')
            ->latest('id')
            ->paginate(15);

        return view('livewire.mouvements-bancaires.index', [
            'mouvements' => $mouvements,
            'comptes' => TresorerieCompte::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
