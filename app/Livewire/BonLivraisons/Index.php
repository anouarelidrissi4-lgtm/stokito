<?php
namespace App\Livewire\BonLivraisons;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\BonLivraison;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    public function mount()
    {
        $this->requirePermission('bl_consulter');
    }

    public function delete($id)
    {
        $this->requirePermission('bl_supprimer');
        if (\App\Support\Tenant::tryDelete(BonLivraison::findOrFail($id))) {
            session()->flash('success', 'Bon de livraison supprimé.');
        }
    }

    public function markDelivered($id)
    {
        $this->requirePermission('bl_edition');
        $bl = BonLivraison::findOrFail($id);
        $bl->update(['status' => 'delivered']);
        \App\Support\StockSync::bonLivraison($bl);
        session()->flash('success', 'Bon de livraison marqué comme livré : stock mis à jour.');
    }

    public function render()
    {
        $bonLivraisons = BonLivraison::query()
            ->with(['client', 'facture'])
            ->when($this->search, fn ($q) => $q->where('numero', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->latest('date')
            ->paginate(10);

        return view('livewire.bon-livraisons.index', ['bonLivraisons' => $bonLivraisons])
            ->layout('layouts.app');
    }
}
