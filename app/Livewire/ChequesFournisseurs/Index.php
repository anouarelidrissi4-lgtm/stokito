<?php
namespace App\Livewire\ChequesFournisseurs;

use App\Models\ChequeEffetFournisseur;
use App\Models\Fournisseur;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    #[Validate('required|exists:fournisseurs,id')]
    public $fournisseur_id = '';

    #[Validate('required|string|max:255')]
    public $numero_cheque = '';

    public $banque = '';

    #[Validate('required|numeric|min:0.01')]
    public $montant = 0;

    #[Validate('required|date')]
    public $date_emission;

    #[Validate('required|date')]
    public $date_echeance;

    public function create()
    {
        $this->reset(['fournisseur_id', 'numero_cheque', 'banque', 'montant']);
        $this->date_emission = now()->format('Y-m-d');
        $this->date_echeance = now()->addDays(30)->format('Y-m-d');
        $this->montant = 0;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        ChequeEffetFournisseur::create([
            'fournisseur_id' => $this->fournisseur_id,
            'numero_cheque' => $this->numero_cheque,
            'banque' => $this->banque,
            'montant' => $this->montant,
            'date_emission' => $this->date_emission,
            'date_echeance' => $this->date_echeance,
            'status' => 'en_attente',
        ]);

        $this->showModal = false;
        session()->flash('success', 'Chèque enregistré.');
    }

    public function markStatus($id, $status)
    {
        ChequeEffetFournisseur::findOrFail($id)->update(['status' => $status]);
        session()->flash('success', 'Statut mis à jour.');
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(ChequeEffetFournisseur::findOrFail($id))) {
            session()->flash('success', 'Chèque supprimé.');
        }
    }

    public function render()
    {
        $cheques = ChequeEffetFournisseur::query()
            ->with('fournisseur')
            ->when($this->search, fn ($q) => $q->where('numero_cheque', 'like', "%{$this->search}%")
                ->orWhereHas('fournisseur', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->latest('date_echeance')
            ->paginate(10);

        return view('livewire.cheques-fournisseurs.index', [
            'cheques' => $cheques,
            'fournisseurs' => Fournisseur::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
