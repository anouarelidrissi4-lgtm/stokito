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

    public string $dateStart = '';

    public string $dateEnd = '';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'dateStart', 'dateEnd'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'dateStart', 'dateEnd']);
        $this->resetPage();
    }

    protected function filters(): array
    {
        return ['search' => $this->search, 'dateStart' => $this->dateStart, 'dateEnd' => $this->dateEnd];
    }

    /** opens "Ajouter un bon de livraison" already filled with this BL's lines (nothing saved yet) */
    public function duplicate($id)
    {
        $this->requirePermission('bl_creer');
        $bl = BonLivraison::with('items')->findOrFail($id);
        $url = \App\Support\SalesLists::prefillUrl(\App\Livewire\BonLivraisons\Form::class, 'bon-livraisons.create', $bl,
            ['show_prices' => (bool) $bl->show_prices, 'tva_rate' => $bl->tva_rate, 'remise_type' => $bl->remise_type ?? 'percent', 'remise_value' => $bl->remise_value ?? 0],
            'Copie de '.$bl->numero.' : vérifiez puis enregistrez (nouveau numéro).');

        return $this->redirect($url, navigate: true);
    }

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
        $bonLivraisons = \App\Support\SalesLists::bonLivraisons($this->filters())->paginate(10);

        return view('livewire.bon-livraisons.index', ['bonLivraisons' => $bonLivraisons, 'totals' => \App\Support\SalesLists::totals('bl', $this->filters()), 'printUrl' => route('bon-livraisons.print', array_filter($this->filters()))])
            ->layout('layouts.app');
    }
}
