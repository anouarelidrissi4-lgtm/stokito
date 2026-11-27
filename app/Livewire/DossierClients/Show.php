<?php

namespace App\Livewire\DossierClients;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Client;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Grand livre client (2026-11-27), same layout as the user's former software. */
class Show extends Component
{
    use RequiresPermission;

    public Client $client;

    #[Url(as: 'du')]
    public string $from = '';

    #[Url(as: 'au')]
    public string $to = '';

    #[Url(as: 'payes')]
    public bool $hidePaid = false;

    public function mount($clientId)
    {
        $this->requirePermission('dossier_client');
        $this->client = Client::findOrFail($clientId);
    }

    public function resetFilters(): void
    {
        $this->from = '';
        $this->to = '';
        $this->hidePaid = false;
    }

    public function render()
    {
        $from = $this->validDate($this->from);
        $to = $this->validDate($this->to);

        return view('livewire.dossier-clients.show', [
            'ledger' => \App\Support\GrandLivre::client($this->client, $from, $to, $this->hidePaid),
            'pdfUrl' => route('dossier-clients.pdf', array_filter(['clientId' => $this->client->id, 'du' => $from, 'au' => $to, 'payes' => $this->hidePaid ? 1 : null])),
        ])->layout('layouts.app');
    }

    private function validDate(string $d): ?string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : null;
    }
}
