<?php
namespace App\Livewire\Factures;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Facture;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;
    use \App\Livewire\Concerns\SendsDocument;

    protected function sendModelClass(): string
    {
        return \App\Models\Facture::class;
    }

    public string $search = '';
    public string $dateStart = '';
    public string $dateEnd = '';

    public array $selected = [];
    public bool $selectAll = false;

    public function mount()
    {
        $this->requirePermission('factures_consulter');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'dateStart', 'dateEnd', 'selected', 'selectAll']);
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->filteredQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(Facture::findOrFail($id))) {
            session()->flash('success', 'Facture supprimée.');
        }
    }

    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        Facture::whereIn('id', $this->selected)->delete();
        session()->flash('success', count($this->selected).' facture(s) supprimee(s).');
        $this->selected = [];
        $this->selectAll = false;
    }

    protected function filters(): array
    {
        return ['search' => $this->search, 'dateStart' => $this->dateStart, 'dateEnd' => $this->dateEnd];
    }

    protected function filteredQuery()
    {
        return \App\Support\SalesLists::factures($this->filters());
    }

    public function updating($name): void
    {
        if (in_array($name, ['dateStart', 'dateEnd'], true)) {
            $this->resetPage();
        }
    }

    /** opens "Ajouter une facture" already filled with this invoice's lines (nothing saved, no number used) */
    public function duplicate($id)
    {
        $f = Facture::with('items')->findOrFail($id);
        $url = \App\Support\SalesLists::prefillUrl(\App\Livewire\Factures\Form::class, 'factures.create', $f,
            ['tva_rate' => $f->tva_rate, 'remise_type' => $f->remise_type ?? 'percent', 'remise_value' => $f->remise_value ?? 0,
             'adresse_livraison' => (string) $f->adresse_livraison, 'nature_operation' => (string) $f->nature_operation],
            'Copie de '.$f->numero.' : vérifiez puis enregistrez (nouveau numéro).');

        return $this->redirect($url, navigate: true);
    }

    public function render()
    {
        return view('livewire.factures.index', ['factures' => $this->filteredQuery()->paginate(10), 'totals' => \App\Support\SalesLists::totals('factures', $this->filters()), 'printUrl' => route('factures.print', array_filter($this->filters()))])
            ->layout('layouts.app');
    }
}
