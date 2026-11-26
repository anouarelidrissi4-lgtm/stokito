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
        $this->banque = (string) $r->banque;
        $this->tireur = (string) $r->tireur;
        $this->date_echeance = $r->date_echeance?->format('Y-m-d');
        $this->bon_livraison_id = $r->bon_livraison_id;
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

    public $banque = '';

    public $tireur = '';

    public $date_echeance = null;

    public $bon_livraison_id = null;

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
        $this->reset(['editId', 'client_id', 'facture_id', 'bon_livraison_id', 'tresorerie_compte_id', 'montant', 'mode', 'numero_cheque', 'banque', 'tireur', 'date_echeance', 'notes']);
        $this->date = now()->format('Y-m-d');
        $this->montant = 0;
        $this->mode = 'especes';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();
        $this->validate(['mode' => 'required|in:'.implode(',', array_keys(\App\Support\SalesLists::MODES)), 'banque' => 'nullable|string|max:100', 'tireur' => 'nullable|string|max:120', 'notes' => 'nullable|string|max:500', 'date_echeance' => 'nullable|date']);

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
            'numero_cheque' => in_array($this->mode, ['cheque', 'effet', 'virement', 'versement', 'transfert'], true) ? (trim((string) $this->numero_cheque) ?: null) : null,
            'banque' => in_array($this->mode, ['cheque', 'effet', 'virement', 'versement'], true) ? (trim((string) $this->banque) ?: null) : null,
            'date_echeance' => in_array($this->mode, \App\Support\SalesLists::MODES_PAPIER, true) ? ($this->date_echeance ?: null) : null,
            'tireur' => in_array($this->mode, \App\Support\SalesLists::MODES_PAPIER, true) ? (trim((string) $this->tireur) ?: null) : null,
            'bon_livraison_id' => $this->bon_livraison_id ?: null,
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
            'banks' => \App\Support\Banks::for(),
        ])->layout('layouts.app');
    }
}
