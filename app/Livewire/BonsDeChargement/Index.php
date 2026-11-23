<?php
namespace App\Livewire\BonsDeChargement;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\BonDeChargement;
use App\Models\BonLivraison;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    public bool $showModal = false;

    public function mount()
    {
        $this->requirePermission('bons_chargement');
        abort_unless(\App\Models\Societe::current()->bons_chargement_enabled, 403, "Le module Bons de chargement est désactivé. Activez-le depuis Ma Société.");
    }

    #[Validate('required|date')]
    public $date;

    public $vehicule = '';

    public $chauffeur = '';

    public $status = 'preparation';

    public $notes = '';

    public array $selectedBonLivraisons = [];

    public function create()
    {
        $this->reset(['vehicule', 'chauffeur', 'notes', 'selectedBonLivraisons']);
        $this->date = now()->format('Y-m-d');
        $this->status = 'preparation';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if (empty($this->selectedBonLivraisons)) {
            $this->addError('selectedBonLivraisons', 'Selectionnez au moins un bon de livraison.');

            return;
        }

        $chargement = BonDeChargement::create([
            'numero' => \App\Support\Tenant::nextNumero(BonDeChargement::class, 'BC-'),
            'date' => $this->date,
            'vehicule' => $this->vehicule,
            'chauffeur' => $this->chauffeur,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        $chargement->bonLivraisons()->sync($this->selectedBonLivraisons);

        $this->showModal = false;
        session()->flash('success', 'Bon de chargement créé.');
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(BonDeChargement::findOrFail($id))) {
            session()->flash('success', 'Bon de chargement supprimé.');
        }
    }

    public function render()
    {
        $chargements = BonDeChargement::query()
            ->withCount('bonLivraisons')
            ->when($this->search, fn ($q) => $q->where('numero', 'like', "%{$this->search}%"))
            ->latest('date')
            ->paginate(10);

        return view('livewire.bons-de-chargement.index', [
            'chargements' => $chargements,
            'availableBonLivraisons' => BonLivraison::with('client')->whereDoesntHave('bonsDeChargement')->orderBy('date', 'desc')->get(),
        ])->layout('layouts.app');
    }
}
