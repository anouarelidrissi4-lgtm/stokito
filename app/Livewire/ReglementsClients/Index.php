<?php
namespace App\Livewire\ReglementsClients;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Client;
use App\Models\Facture;
use App\Models\ReglementClient;
use App\Models\TresorerieCompte;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    public bool $showModal = false;

    #[Validate('required|exists:clients,id')]
    public $client_id = '';

    public $facture_id = null;

    public $tresorerie_compte_id = null;

    #[Validate('required|numeric|min:0.01')]
    public $montant = 0;

    #[Validate('required|date')]
    public $date;

    public $mode = 'especes';

    public $numero_cheque = '';

    public $notes = '';

    public function mount($factureId = null)
    {
        $this->requirePermission('gestion_reglements_clients');

        $this->date = now()->format('Y-m-d');

        if ($factureId) {
            $facture = Facture::findOrFail($factureId);
            $this->client_id = $facture->client_id;
            $this->facture_id = $facture->id;
            $this->montant = $facture->resteAPayer();
            $this->showModal = true;
        }
    }

    public function create()
    {
        $this->reset(['client_id', 'facture_id', 'tresorerie_compte_id', 'montant', 'mode', 'numero_cheque', 'notes']);
        $this->montant = 0;
        $this->mode = 'especes';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $reglement = ReglementClient::create([
            'client_id' => $this->client_id,
            'facture_id' => $this->facture_id ?: null,
            'tresorerie_compte_id' => $this->tresorerie_compte_id ?: null,
            'montant' => $this->montant,
            'date' => $this->date,
            'mode' => $this->mode,
            'numero_cheque' => $this->mode === 'cheque' ? $this->numero_cheque : null,
            'notes' => $this->notes,
        ]);

        $reglement->applyEffects();

        $this->showModal = false;
        session()->flash('success', 'Règlement enregistré.');
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(ReglementClient::findOrFail($id))) {
            session()->flash('success', 'Règlement supprimé.');
        }
    }

    public function render()
    {
        $reglements = ReglementClient::query()
            ->with(['client', 'facture'])
            ->when($this->search, fn ($q) => $q->whereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->latest('date')
            ->paginate(10);

        return view('livewire.reglements-clients.index', [
            'reglements' => $reglements,
            'clients' => Client::orderBy('name')->get(),
            'comptes' => TresorerieCompte::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
