<?php
namespace App\Livewire\FournisseurReports;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Fournisseur;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    public function mount()
    {
        $this->requirePermission('soldes_fournisseurs');
    }

    public function render()
    {
        $fournisseurs = Fournisseur::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        $fournisseurs->getCollection()->transform(function ($fournisseur) {
            $fournisseur->solde_calcule = $fournisseur->solde();

            return $fournisseur;
        });

        return view('livewire.fournisseur-reports.index', ['fournisseurs' => $fournisseurs])
            ->layout('layouts.app');
    }
}
