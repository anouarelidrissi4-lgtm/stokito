<?php
namespace App\Livewire\AvoirsClients;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\AvoirClient;
use App\Models\Client;
use App\Models\Facture;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|exists:clients,id')]
    public $client_id = '';

    public $facture_id = null;

    #[Validate('required|date')]
    public $date;

    #[Validate('required|numeric|min:0.01')]
    public $montant = 0;

    public $motif = '';

    public $notes = '';

    public function mount()
    {
        $this->requirePermission('gestion_avoirs_clients');
        if (request()->routeIs('avoirs-clients.create')) {
            $this->create();
        }
        $this->date = now()->format('Y-m-d');
    }

    public function create()
    {
        $this->reset(['editingId', 'client_id', 'facture_id', 'montant', 'motif', 'notes']);
        $this->date = now()->format('Y-m-d');
        $this->montant = 0;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $avoir = AvoirClient::findOrFail($id);
        $this->editingId = $avoir->id;
        $this->client_id = $avoir->client_id;
        $this->facture_id = $avoir->facture_id;
        $this->date = $avoir->date->format('Y-m-d');
        $this->montant = $avoir->montant;
        $this->motif = $avoir->motif;
        $this->notes = $avoir->notes;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'client_id' => $this->client_id,
            'facture_id' => $this->facture_id ?: null,
            'date' => $this->date,
            'montant' => $this->montant,
            'motif' => $this->motif,
            'notes' => $this->notes,
        ];

        if ($this->editingId) {
            AvoirClient::findOrFail($this->editingId)->update($data);
        } else {
            $data['numero'] = \App\Support\Tenant::nextNumero(AvoirClient::class, 'AVC-');
            AvoirClient::create($data);
        }

        if ($this->facture_id) {
            Facture::find($this->facture_id)?->refreshStatus();
        }

        $this->showModal = false;
        session()->flash('success', 'Avoir enregistré.');
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(AvoirClient::findOrFail($id))) {
            session()->flash('success', 'Avoir supprimé.');
        }
    }

    public function render()
    {
        $avoirs = AvoirClient::query()
            ->with('client')
            ->when($this->search, fn ($q) => $q->where('numero', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->latest('date')
            ->paginate(10);

        return view('livewire.avoirs-clients.index', [
            'avoirs' => $avoirs,
            'clients' => Client::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
