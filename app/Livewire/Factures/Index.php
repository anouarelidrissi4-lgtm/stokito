<?php
namespace App\Livewire\Factures;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Facture;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

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

    protected function filteredQuery()
    {
        return Facture::query()
            ->with(['client', 'bonLivraison'])
            ->when($this->search, fn ($q) => $q->where('numero', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('bonLivraison', fn ($q2) => $q2->where('numero', 'like', "%{$this->search}%")))
            ->when($this->dateStart, fn ($q) => $q->whereDate('date', '>=', $this->dateStart))
            ->when($this->dateEnd, fn ($q) => $q->whereDate('date', '<=', $this->dateEnd))
            ->latest('date');
    }

    public function render()
    {
        return view('livewire.factures.index', ['factures' => $this->filteredQuery()->paginate(10)])
            ->layout('layouts.app');
    }
}
