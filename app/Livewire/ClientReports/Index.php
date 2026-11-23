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
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->mode === 'plafonds', fn ($q) => $q->whereNotNull('credit_limit')->where('credit_limit', '>', 0))
            ->orderBy('name')
            ->paginate(15);

        $clients->getCollection()->transform(function ($client) {
            $client->solde_calcule = $client->solde();

            return $client;
        });

        return view('livewire.client-reports.index', ['clients' => $clients])
            ->layout('layouts.app');
    }
}
