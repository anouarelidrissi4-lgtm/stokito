<?php

namespace App\Livewire\Personnel;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Employe;
use App\Models\EmployePresence;
use App\Support\Paie;
use App\Support\Plans;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Attendance (2026-11-28): a month grid (click a day to cycle ✓ → ½ → ✗ → C → F → empty) and a "today" panel for the phone,
 * with "Tout le monde présent" in one click.
 */
class Pointage extends Component
{
    use RequiresPermission;

    public bool $locked = false;

    #[Url]
    public string $mois = '';

    public string $jour = '';

    public function mount(): void
    {
        $this->requirePermission('personnel');
        $this->locked = Plans::current() === 'free';
        if (! preg_match('/^\d{4}-\d{2}$/', $this->mois)) {
            $this->mois = now()->format('Y-m');
        }
        $this->jour = now()->toDateString();
    }

    private function validDate(string $d): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
    }

    /** Click on a cell of the grid: next status in the cycle. */
    public function cycle(int $employeId, string $date): void
    {
        abort_if($this->locked || ! $this->validDate($date), 403);
        Employe::findOrFail($employeId);
        $codes = array_keys(EmployePresence::STATUTS);
        $p = EmployePresence::where('employe_id', $employeId)->whereDate('date', $date)->first();
        $i = $p ? array_search($p->statut, $codes, true) : -1;
        $next = $codes[$i + 1] ?? null;
        $this->write($employeId, $date, $next);
    }

    /** Status chosen in the "today" panel ('' = clear). */
    public function marquer(int $employeId, string $statut): void
    {
        abort_if($this->locked || ! $this->validDate($this->jour), 403);
        Employe::findOrFail($employeId);
        $this->write($employeId, $this->jour, array_key_exists($statut, EmployePresence::STATUTS) ? $statut : null);
    }

    /** Everyone active and not marked yet on the chosen day becomes "présent". */
    public function toutPresent(): void
    {
        abort_if($this->locked || ! $this->validDate($this->jour), 403);
        $done = EmployePresence::whereDate('date', $this->jour)->pluck('employe_id')->all();
        $n = 0;
        foreach (Employe::where('actif', true)->whereNotIn('id', $done)->pluck('id') as $id) {
            $this->write($id, $this->jour, 'P');
            $n++;
        }
        session()->flash('success', $n ? $n.' employé'.($n > 1 ? 's' : '').' marqué'.($n > 1 ? 's' : '').' présent'.($n > 1 ? 's' : '').'.' : 'Tout le monde est déjà pointé ce jour-là.');
    }

    private function write(int $employeId, string $date, ?string $statut): void
    {
        $p = EmployePresence::where('employe_id', $employeId)->whereDate('date', $date)->first();
        if (! $statut) {
            $p?->delete();
        } elseif ($p) {
            $p->update(['statut' => $statut]);
        } else {
            EmployePresence::create(['employe_id' => $employeId, 'date' => $date, 'statut' => $statut]);
        }
    }

    public function moisPrecedent(): void
    {
        $this->mois = Carbon::createFromFormat('Y-m-d', $this->mois.'-01')->subMonthNoOverflow()->format('Y-m');
    }

    public function moisSuivant(): void
    {
        $this->mois = Carbon::createFromFormat('Y-m-d', $this->mois.'-01')->addMonthNoOverflow()->format('Y-m');
    }

    public function render()
    {
        $debut = Carbon::createFromFormat('Y-m-d', $this->mois.'-01')->startOfDay();
        $fin = $debut->copy()->endOfMonth()->startOfDay();
        $employes = $this->locked ? collect() : Employe::where('actif', true)->orderBy('nom')->get();
        $grid = [];
        $totaux = [];
        if ($employes->isNotEmpty()) {
            foreach (EmployePresence::whereIn('employe_id', $employes->pluck('id'))->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])->get() as $p) {
                $grid[$p->employe_id][$p->date->day] = $p->statut;
            }
            foreach ($employes as $e) {
                $n = array_fill_keys(array_keys(EmployePresence::STATUTS), 0);
                foreach ($grid[$e->id] ?? [] as $s) {
                    $n[$s] = ($n[$s] ?? 0) + 1;
                }
                $totaux[$e->id] = ['jours' => Paie::joursTravailles($n), 'absences' => $n['A'] + $n['D'] * 0.5];
            }
        }
        $jourStatuts = $employes->isEmpty() ? [] : EmployePresence::whereDate('date', $this->validDate($this->jour) ? $this->jour : now()->toDateString())
            ->pluck('statut', 'employe_id')->all();

        return view('livewire.personnel.pointage', [
            'employes' => $employes, 'grid' => $grid, 'totaux' => $totaux, 'debut' => $debut, 'nbJours' => $debut->daysInMonth,
            'jourStatuts' => $jourStatuts,
        ])->layout('layouts.app');
    }
}
