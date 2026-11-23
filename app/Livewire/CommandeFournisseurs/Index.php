<?php
namespace App\Livewire\CommandeFournisseurs;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\CommandeFournisseur;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    public function mount()
    {
        $this->requirePermission('gestion_commandes_fournisseurs');
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(CommandeFournisseur::findOrFail($id))) {
            session()->flash('success', 'Commande supprimée.');
        }
    }

    public function markReceived($id)
    {
        $commande = CommandeFournisseur::with('items')->findOrFail($id);
        $commande->markReceived();
        session()->flash('success', 'Commande marquée comme reçue - stock mis à jour.');
    }

    public function render()
    {
        $commandes = CommandeFournisseur::query()
            ->with('fournisseur')
            ->when($this->search, fn ($q) => $q->where('numero', 'like', "%{$this->search}%")
                ->orWhereHas('fournisseur', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->latest('date')
            ->paginate(10);

        return view('livewire.commande-fournisseurs.index', ['commandes' => $commandes])
            ->layout('layouts.app');
    }
}
