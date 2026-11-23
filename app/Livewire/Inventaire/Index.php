<?php

namespace App\Livewire\Inventaire;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Inventaire;
use Livewire\Component;
use Livewire\WithPagination;

/** History of stock counts (inventaires). */
class Index extends Component
{
    use WithPagination, RequiresPermission;

    public function mount()
    {
        $this->requirePermission('inventaire');
    }

    public function render()
    {
        return view('livewire.inventaire.index', [
            'inventaires' => Inventaire::with('user')->latest('date')->latest('id')->paginate(15),
        ])->layout('layouts.app');
    }
}
