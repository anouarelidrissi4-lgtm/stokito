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

    public string $dateStart = '';

    public string $dateEnd = '';

    public ?int $editId = null;

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

    public function edit($id)
    {
        $r = ReglementClient::findOrFail($id);
        $this->editId = $r->id;
        $this->client_id = $r->client_id;
        $this->facture_id = $r->facture_id;
        $this->tresorerie_compte_id = $r->tresorerie_compte_id;
        $this->montant = (float) $r->montant;
        $this->date = $r->date?->format('Y-m-d');
        $this->mode = $r->mode;
        $this->numero_cheque = (string) $r->numero_cheque;
        $this->notes = (string) $r->notes;
        $this->resetValidation();
        $this->showModal = true;
    }

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
        $this->reset(['editId', 'client_id', 'facture_id', 'tresorerie_compte_id', 'montant', 'mode', 'numero_cheque', 'notes']);
        $this->date = now()->format('Y-m-d');
        $this->montant = 0;
        $this->mode = 'especes';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // ids must belong to this company (global scope)
        if (! \App\Models\Client::find($this->client_id)) {
            $this->addError('client_id', 'Client introuvable.');

            return;
        }
        if ($this->facture_id && ! Facture::find($this->facture_id)) {
            $this->addError('facture_id', 'Facture introuvable.');

            return;
        }
        if ($this->tresorerie_compte_id && ! TresorerieCompte::find($this->tresorerie_compte_id)) {
            $this->addError('tresorerie_compte_id', 'Compte introuvable.');

            return;
        }
        $data = [
            'client_id' => $this->client_id,
            'facture_id' => $this->facture_id ?: null,
            'tresorerie_compte_id' => $this->tresorerie_compte_id ?: null,
            'montant' => $this->montant,
            'date' => $this->date,
            'mode' => $this->mode,
            'numero_cheque' => $this->mode === 'cheque' ? $this->numero_cheque : null,
            'notes' => $this->notes,
        ];
        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            if ($this->editId) {
                $reglement = ReglementClient::findOrFail($this->editId);
                $oldFacture = $reglement->facture_id;
                $reglement->removeEffects();
                $reglement->update($data);
                $reglement->refresh()->applyEffects();
                if ($oldFacture && $oldFacture !== $reglement->facture_id && ($f = Facture::find($oldFacture))) {
                    $f->refreshStatus();
                }
            } else {
                $reglement = ReglementClient::create($data);
                $reglement->applyEffects();
            }
        });

        $this->showModal = false;
        session()->flash('success', $this->editId ? 'Règlement modifié.' : 'Règlement enregistré.');
        $this->editId = null;
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(ReglementClient::findOrFail($id))) {
            session()->flash('success', 'Règlement supprimé.');
        }
    }

    public function render()
    {
        $reglements = \App\Support\SalesLists::reglements($this->filters())->paginate(10);

        return view('livewire.reglements-clients.index', [
            'reglements' => $reglements,
            'totals' => \App\Support\SalesLists::totals('reglements', $this->filters()),
            'printUrl' => route('reglements-clients.print', array_filter($this->filters())),
            'clients' => Client::orderBy('name')->get(),
            'comptes' => TresorerieCompte::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
