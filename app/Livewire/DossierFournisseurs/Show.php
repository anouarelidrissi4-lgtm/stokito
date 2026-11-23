<?php
namespace App\Livewire\DossierFournisseurs;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Fournisseur;
use Livewire\Component;

class Show extends Component
{
    use RequiresPermission;

    public Fournisseur $fournisseur;

    public function mount($fournisseurId)
    {
        $this->requirePermission('dossier_fournisseur');
        $this->fournisseur = Fournisseur::findOrFail($fournisseurId);
    }

    public function render()
    {
        return view('livewire.dossier-fournisseurs.show', [
            'commandes' => $this->fournisseur->commandes()->latest('date')->take(10)->get(),
            'reglements' => $this->fournisseur->reglements()->latest('date')->take(10)->get(),
            'avoirs' => $this->fournisseur->avoirs()->latest('date')->take(10)->get(),
            'cheques' => $this->fournisseur->chequesEffets()->latest('date_echeance')->take(10)->get(),
        ])->layout('layouts.app');
    }
}
