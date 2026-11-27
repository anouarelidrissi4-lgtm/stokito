<?php

namespace App\Livewire\BonsDeChargement;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\BonDeChargement;
use Livewire\Component;

/** One loading slip: the storekeeper types the loaded quantities (2026-11-27). */
class Show extends Component
{
    use RequiresPermission;

    public BonDeChargement $bc;

    public array $charge = [];

    public $vehicule = '';

    public $chauffeur = '';

    public $commercial = '';

    public $notes = '';

    public function mount($id)
    {
        $this->requirePermission('bons_chargement');
        abort_unless(\App\Support\Chargement::enabled(), 403, 'Le module Bons de chargement est désactivé. Activez-le depuis Ma Société.');
        $this->bc = BonDeChargement::with(['lignes', 'bonLivraison.client'])->findOrFail($id);
        $this->fillForm();
    }

    private function fillForm(): void
    {
        $this->vehicule = (string) $this->bc->vehicule;
        $this->chauffeur = (string) $this->bc->chauffeur;
        $this->commercial = (string) $this->bc->commercial;
        $this->notes = (string) $this->bc->notes;
        $this->charge = $this->bc->lignes->mapWithKeys(fn ($l) => [$l->id => $l->quantite_chargee === null ? '' : (string) (float) $l->quantite_chargee])->all();
    }

    public function toutCharger(): void
    {
        foreach ($this->bc->lignes as $l) {
            $this->charge[$l->id] = (string) (float) $l->quantite_commandee;
        }
    }

    public function save(): void
    {
        $this->validate(['charge.*' => 'nullable|numeric|min:0', 'vehicule' => 'nullable|string|max:120', 'chauffeur' => 'nullable|string|max:120', 'commercial' => 'nullable|string|max:120', 'notes' => 'nullable|string|max:1000'],
            [], ['charge.*' => 'quantité chargée']);
        foreach ($this->bc->lignes as $l) {                       // only this slip's lines
            $v = $this->charge[$l->id] ?? '';
            $l->update(['quantite_chargee' => $v === '' || $v === null ? null : (float) $v]);
        }
        $this->bc->update(['vehicule' => trim((string) $this->vehicule) ?: null, 'chauffeur' => trim((string) $this->chauffeur) ?: null, 'commercial' => trim((string) $this->commercial) ?: null, 'notes' => trim((string) $this->notes) ?: null]);
        $this->bc->refresh()->refreshStatus();
        $this->bc->load('lignes');
        session()->flash('success', 'Bon de chargement enregistré.');
    }

    public function marquerLivre(bool $livre = true): void
    {
        $this->bc->update(['status' => $livre ? 'livre' : 'preparation']);
        if (! $livre) {
            $this->bc->refreshStatus();
        }
    }

    public function render()
    {
        return view('livewire.bons-de-chargement.show')->layout('layouts.app');
    }
}
