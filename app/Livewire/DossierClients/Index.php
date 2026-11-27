<?php

namespace App\Livewire\DossierClients;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

/** Dossier clients (2026-11-27): pick a customer, then open the grand livre. */
class Index extends Component
{
    use RequiresPermission, WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->requirePermission('dossier_client');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $s = trim($this->search);
        $clients = Client::query()
            ->when($s !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('prenom', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")))
            ->orderBy('name')->paginate(20);
        $clients->getCollection()->transform(function ($c) {
            $c->solde_calcule = $c->solde();

            return $c;
        });

        return view('livewire.dossier-clients.index', ['clients' => $clients])->layout('layouts.app');
    }
}
