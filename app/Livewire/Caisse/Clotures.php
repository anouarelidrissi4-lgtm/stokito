<?php

namespace App\Livewire\Caisse;

use App\Models\CaisseCloture;
use App\Support\Caisse;
use App\Support\CaisseException;
use Livewire\Component;
use Livewire\WithPagination;

/** Z closures (2026-11-25): close the day, list day/month/year closures, integrity check. */
class Clotures extends Component
{
    use WithPagination;

    public string $type = 'jour';

    public ?array $integrity = null;

    public function mount(): void
    {
        Pos::guard('caisse_cloturer');
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function cloturer(): void
    {
        Pos::guard('caisse_cloturer');
        try {
            $c = Caisse::cloturerJour();
        } catch (CaisseException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }
        session()->flash('success', "Clôture Z n° {$c->sequence} enregistrée.");
    }

    public function verify(): void
    {
        Pos::guard('caisse_cloturer');
        $this->integrity = Caisse::verifier();
        Caisse::journal('CONTROLE_INTEGRITE', $this->integrity['ok'] ? 'Contrôle d’intégrité : conforme' : 'Contrôle d’intégrité : anomalies détectées',
            ['ok' => $this->integrity['ok'], 'erreurs' => count($this->integrity['errors'])]);
    }

    public function render()
    {
        $open = Caisse::nonClotures();

        return view('livewire.caisse.clotures', [
            'clotures' => CaisseCloture::where('type', $this->type)->orderByDesc('sequence')->paginate(30),
            'open' => $open,
            'currency' => \App\Models\Societe::current()->currency ?: 'EUR',
        ])->layout('layouts.app');
    }
}
