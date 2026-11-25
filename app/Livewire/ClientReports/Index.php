<?php
namespace App\Livewire\ClientReports;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    public string $mode = 'soldes';

    public string $pageTitle = 'Soldes Clients';

    public function mount($mode = 'soldes')
    {
        $this->requirePermission($mode === 'plafonds' ? 'plafonds_clients' : 'soldes_clients');

        $this->mode = $mode;
        $this->pageTitle = $mode === 'plafonds' ? 'Plafonds Clients' : 'Soldes Clients';
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$this->search}%")->orWhere('prenom', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%")))
            ->when($this->mode === 'plafonds', fn ($q) => $q->whereNotNull('credit_limit')->where('credit_limit', '>', 0))
            ->orderBy('name')
            ->paginate(15);

        $clients->getCollection()->transform(function ($client) {
            $client->solde_calcule = $client->solde();

            return $client;
        });

        return view('livewire.client-reports.index', ['clients' => $clients,
            'totalSoldes' => $this->mode === 'soldes' ? \App\Support\SalesLists::totalSoldes($this->search) : null,
            'printUrl' => $this->mode === 'soldes' ? route('soldes-clients.print', array_filter(['search' => $this->search])) : null])
            ->layout('layouts.app');
    }
}
