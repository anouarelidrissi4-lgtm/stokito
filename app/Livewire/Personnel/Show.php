<?php

namespace App\Livewire\Personnel;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Employe;
use App\Models\EmployeAvance;
use App\Models\EmployeCredit;
use App\Models\EmployePaie;
use App\Models\TresorerieCompte;
use App\Support\Paie;
use App\Support\Plans;
use Illuminate\Support\Carbon;
use Livewire\Component;

/** A worker's file (2026-11-28): this period, advances, loans, pays; compute and record the pay. */
class Show extends Component
{
    use RequiresPermission;

    public Employe $employe;

    public bool $locked = false;

    /** '' closed, 'avance', 'credit', 'paie' */
    public string $panel = '';

    public $montant = null;

    public ?string $date = null;

    public string $mode = 'especes';

    public $compte = null;

    public string $notes = '';

    public $mensualite = null;

    /* pay */
    public ?string $debut = null;

    public ?string $fin = null;

    public $jours = 0;

    public $base = 0;

    public $prime = 0;

    public $retenue = 0;

    public float $avancesPeriode = 0;

    public array $compteur = [];

    public function mount(int $employeId): void
    {
        $this->requirePermission('personnel');
        $this->locked = Plans::current() === 'free';
        $this->employe = Employe::findOrFail($employeId);
    }

    public function open(string $panel): void
    {
        abort_if($this->locked, 403);
        $this->resetValidation();
        $this->reset(['montant', 'notes', 'compte', 'mensualite', 'prime']);
        $this->mode = 'especes';
        $this->date = now()->toDateString();
        $this->panel = in_array($panel, ['avance', 'credit', 'paie'], true) ? $panel : '';
        if ($this->panel === 'paie') {
            [$d, $f] = Paie::periodeParDefaut($this->employe);
            $this->debut = $d->toDateString();
            $this->fin = $f->toDateString();
            $this->recalculer();
        }
    }

    /** Fills days, base, advances and loan repayment for the chosen period. */
    public function recalculer(): void
    {
        abort_if($this->locked, 403);
        $this->validate(['debut' => 'required|date', 'fin' => 'required|date|after_or_equal:debut'], [], ['debut' => 'début', 'fin' => 'fin']);
        $c = Paie::calcul($this->employe, Carbon::parse($this->debut)->startOfDay(), Carbon::parse($this->fin)->startOfDay());
        $this->compteur = $c['compteur'];
        $this->jours = $c['jours'];
        $this->base = $c['base'];
        $this->avancesPeriode = $c['avances'];
        $this->retenue = $c['retenue_credit'];
    }

    public function updatedDebut(): void
    {
        $this->recalculer();
    }

    public function updatedFin(): void
    {
        $this->recalculer();
    }

    public function getNetProperty(): float
    {
        return round((float) $this->base + (float) $this->prime - $this->avancesPeriode - (float) $this->retenue, 2);
    }

    private function checkCompte(): bool
    {
        if ($this->compte && ! TresorerieCompte::find($this->compte)) {
            $this->addError('compte', 'Compte introuvable.');

            return false;
        }

        return true;
    }

    public function saveAvance(): void
    {
        abort_if($this->locked, 403);
        $this->validate(['montant' => 'required|numeric|min:0.01', 'date' => 'required|date', 'mode' => 'required|in:especes,virement,cheque', 'notes' => 'nullable|string|max:255']);
        if (! $this->checkCompte()) {
            return;
        }
        Paie::avance($this->employe, ['date' => $this->date, 'montant' => $this->montant, 'mode_paiement' => $this->mode, 'tresorerie_compte_id' => $this->compte, 'notes' => trim($this->notes) ?: null]);
        $this->panel = '';
        session()->flash('success', 'Avance enregistrée.');
    }

    public function saveCredit(): void
    {
        abort_if($this->locked, 403);
        $this->validate(['montant' => 'required|numeric|min:0.01', 'mensualite' => 'required|numeric|min:0.01|lte:montant', 'date' => 'required|date',
            'mode' => 'required|in:especes,virement,cheque', 'notes' => 'nullable|string|max:255'], [], ['mensualite' => 'retenue par paie']);
        if (! $this->checkCompte()) {
            return;
        }
        $c = EmployeCredit::create(['employe_id' => $this->employe->id, 'date' => $this->date, 'montant' => round((float) $this->montant, 2),
            'mensualite' => round((float) $this->mensualite, 2), 'mode_paiement' => $this->mode, 'tresorerie_compte_id' => $this->compte ?: null, 'notes' => trim($this->notes) ?: null]);
        $c->syncTresorerie();
        $this->panel = '';
        session()->flash('success', 'Crédit enregistré.');
    }

    public function payer(): void
    {
        abort_if($this->locked, 403);
        $this->validate([
            'debut' => 'required|date', 'fin' => 'required|date|after_or_equal:debut', 'jours' => 'nullable|numeric|min:0',
            'base' => 'required|numeric|min:0', 'prime' => 'nullable|numeric|min:0', 'retenue' => 'nullable|numeric|min:0',
            'date' => 'required|date', 'mode' => 'required|in:especes,virement,cheque', 'notes' => 'nullable|string|max:500',
        ], [], ['base' => 'salaire de base', 'retenue' => 'retenue crédit', 'date' => 'date de paiement']);
        if (! $this->checkCompte()) {
            return;
        }
        $this->recalculerAvancesSeulement();
        if ($this->net < 0) {
            $this->addError('base', 'Les avances et la retenue dépassent le salaire : le net serait négatif.');

            return;
        }
        Paie::payer($this->employe, ['debut' => $this->debut, 'fin' => $this->fin, 'jours' => $this->jours, 'base' => $this->base, 'prime' => $this->prime ?: 0,
            'retenue_credit' => $this->retenue ?: 0, 'date_paiement' => $this->date, 'mode_paiement' => $this->mode, 'tresorerie_compte_id' => $this->compte,
            'notes' => trim($this->notes) ?: null]);
        $this->panel = '';
        session()->flash('success', 'Paie enregistrée pour '.$this->employe->nom.'.');
    }

    private function recalculerAvancesSeulement(): void
    {
        $this->avancesPeriode = (float) EmployeAvance::where('employe_id', $this->employe->id)->whereNull('paie_id')->whereDate('date', '<=', $this->fin)->sum('montant');
    }

    public function deleteAvance(int $id): void
    {
        abort_if($this->locked, 403);
        $a = EmployeAvance::where('employe_id', $this->employe->id)->findOrFail($id);
        if ($a->paie_id) {
            session()->flash('error', 'Cette avance est déjà retirée d’une paie : supprimez d’abord la paie.');

            return;
        }
        $a->delete();
        session()->flash('success', 'Avance supprimée.');
    }

    public function deleteCredit(int $id): void
    {
        abort_if($this->locked, 403);
        $c = EmployeCredit::where('employe_id', $this->employe->id)->findOrFail($id);
        if ((float) $c->rembourse > 0) {
            session()->flash('error', 'Ce crédit a déjà des remboursements : supprimez d’abord les paies concernées.');

            return;
        }
        $c->delete();
        session()->flash('success', 'Crédit supprimé.');
    }

    public function deletePaie(int $id): void
    {
        abort_if($this->locked, 403);
        EmployePaie::where('employe_id', $this->employe->id)->findOrFail($id)->delete();
        session()->flash('success', 'Paie supprimée : les avances et la retenue du crédit sont rendues.');
    }

    public function render()
    {
        $e = $this->employe;
        [$d, $f] = Paie::periodeParDefaut($e);
        $c = $this->locked ? null : Paie::calcul($e, $d, $f);

        return view('livewire.personnel.show', [
            'periode' => [$d, $f],
            'calc' => $c,
            'avances' => $this->locked ? collect() : $e->avances()->with('paie')->latest('date')->latest('id')->limit(50)->get(),
            'credits' => $this->locked ? collect() : $e->credits()->latest('date')->get(),
            'paies' => $this->locked ? collect() : $e->paies()->latest('periode_fin')->latest('id')->limit(36)->get(),
            'comptes' => TresorerieCompte::where('is_active', true)->orderBy('name')->get(),
            'currency' => \App\Models\Societe::current()->currency ?? '',
        ])->layout('layouts.app');
    }
}
