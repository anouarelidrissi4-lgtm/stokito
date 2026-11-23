<?php
namespace App\Livewire\DossierClients;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Client;
use Livewire\Component;

class Show extends Component
{
    use RequiresPermission;

    public Client $client;

    public function mount($clientId)
    {
        $this->requirePermission('dossier_client');
        $this->client = Client::findOrFail($clientId);
    }

    public function render()
    {
        return view('livewire.dossier-clients.show', [
            'devisList' => $this->client->devis()->latest('date')->take(10)->get(),
            'bonLivraisons' => $this->client->bonLivraisons()->latest('date')->take(10)->get(),
            'factures' => $this->client->factures()->latest('date')->take(10)->get(),
            'reglements' => $this->client->reglements()->latest('date')->take(10)->get(),
            'avoirs' => $this->client->avoirs()->latest('date')->take(10)->get(),
        ])->layout('layouts.app');
    }
}
