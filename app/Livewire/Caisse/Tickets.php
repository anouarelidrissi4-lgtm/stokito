<?php

namespace App\Livewire\Caisse;

use App\Models\CaisseTicket;
use App\Support\Caisse;
use App\Support\CaisseException;
use Livewire\Component;
use Livewire\WithPagination;

/** Ticket history (2026-11-25): reprint as duplicate, cancel with a negative ticket. */
class Tickets extends Component
{
    use WithPagination;

    public string $date = '';

    public string $numero = '';

    public ?int $cancelId = null;

    public string $motif = '';

    public function mount(): void
    {
        Pos::guard();
        $this->date = now()->toDateString();
    }

    public function updating($name): void
    {
        if (in_array($name, ['date', 'numero'])) {
            $this->resetPage();
        }
    }

    public function askCancel(int $id): void
    {
        Pos::guard('caisse_annuler');
        $this->cancelId = $id;
        $this->motif = '';
        $this->resetErrorBag();
    }

    public function cancel(): void
    {
        Pos::guard('caisse_annuler');
        $t = CaisseTicket::findOrFail($this->cancelId);
        try {
            $a = Caisse::annuler($t, $this->motif);
        } catch (CaisseException $e) {
            $this->addError('motif', $e->getMessage());

            return;
        }
        $this->cancelId = null;
        session()->flash('success', "Ticket {$t->numero} annulé par le ticket {$a->numero} : le stock et les paiements sont repris.");
    }

    public function render()
    {
        $q = CaisseTicket::query()->with(['paiements', 'annulation'])
            ->when($this->numero !== '', fn ($b) => $b->where('numero', 'like', '%'.trim($this->numero).'%'))
            ->when($this->numero === '' && $this->date, fn ($b) => $b->whereDate('created_at', $this->date))
            ->orderByDesc('sequence');

        return view('livewire.caisse.tickets', [
            'tickets' => $q->paginate(30),
            'sum' => (clone $q)->reorder()->sum('total_ttc'),
            'currency' => \App\Models\Societe::current()->currency ?: 'EUR',
        ])->layout('layouts.app');
    }
}
