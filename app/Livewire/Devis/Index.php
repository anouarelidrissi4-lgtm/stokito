<?php
namespace App\Livewire\Devis;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Devis;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    public string $numeroSearch = '';

    public function mount()
    {
        $this->requirePermission('devis_consulter');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function searchByNumero()
    {
        $this->resetPage();
    }

    public function resetNumeroSearch()
    {
        $this->numeroSearch = '';
        $this->resetPage();
    }

    public function delete($id)
    {
        $this->requirePermission('devis_supprimer');
        if (\App\Support\Tenant::tryDelete(Devis::findOrFail($id))) {
            session()->flash('success', 'Devis supprimé.');
        }
    }

    public function convertToFacture($id)
    {
        $this->requirePermission('devis_valider');
        $devis = Devis::findOrFail($id);

        return $this->redirect(route('factures.create').'?devis='.$devis->id, navigate: true);
    }

    public function convertToBonLivraison($id)
    {
        $this->requirePermission('devis_valider');
        $devis = Devis::with('items')->findOrFail($id);

        $bl = $devis->toBonLivraison();

        session()->flash('success', 'Bon de livraison '.$bl->numero.' cree a partir du devis.');
        return $this->redirect(route('bon-livraisons.index'), navigate: true);
    }

    public function render()
    {
        $devisList = Devis::query()
            ->with('client')
            ->when($this->search, fn ($q) => $q->where('numero', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->when($this->numeroSearch, fn ($q) => $q->where('numero', 'like', "%{$this->numeroSearch}%"))
            ->latest('date')
            ->paginate(10);

        return view('livewire.devis.index', ['devisList' => $devisList])
            ->layout('layouts.app');
    }
}
