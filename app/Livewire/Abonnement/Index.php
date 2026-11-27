<?php

namespace App\Livewire\Abonnement;

use App\Support\Plans;
use Livewire\Component;

/** Administration > Abonnement: current plan, usage this month, plans and how to pay (2026-11-27). */
class Index extends Component
{
    public function mount()
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->hasPermission('gestion_societe'), 403);
    }

    public function render()
    {
        $u = auth()->user();
        $project = $u->project;
        $plan = Plans::current();

        return view('livewire.abonnement.index', [
            'plan' => $plan,
            'project' => $project,
            'limits' => Plans::limits($plan),
            'usage' => Plans::usage(),
            'currency' => Plans::currency(),
            'societe' => \App\Models\Societe::current(),
        ])->layout('layouts.app');
    }
}
