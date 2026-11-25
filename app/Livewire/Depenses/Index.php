<?php

namespace App\Livewire\Depenses;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Depense;
use App\Models\Fournisseur;
use App\Models\Societe;
use App\Models\TresorerieCompte;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithFileUploads, RequiresPermission;

    /** 'YYYY-MM' or 'tout'. */
    #[Url]
    public string $mois = '';

    #[Url]
    public string $filtreCategorie = '';

    public string $search = '';

    public bool $showModal = false;

    public ?int $editId = null;

    public $date;
    public string $categorie = 'autre';
    public string $libelle = '';
    public $fournisseur_id = '';
    public $montant_ttc = '';
    public $taux_tva = '';
    public $montant_tva = '';
    public string $mode_paiement = 'especes';
    public $tresorerie_compte_id = '';
    public string $reference = '';
    public string $notes = '';

    public $justificatif;
    public ?string $justificatifPath = null;
    public bool $removeJustificatif = false;

    public function mount(): void
    {
        $this->requirePermission('depenses');
        if ($this->mois === '' || ($this->mois !== 'tout' && ! preg_match('/^\d{4}-\d{2}$/', $this->mois))) {
            $this->mois = now()->format('Y-m');
        }
    }

    public function updating($name): void
    {
        if (in_array($name, ['mois', 'filtreCategorie', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function updatedMontantTtc(): void
    {
        $this->recalcTva();
    }

    public function updatedTauxTva(): void
    {
        $this->recalcTva();
    }

    protected function recalcTva(): void
    {
        if (is_numeric($this->montant_ttc) && is_numeric($this->taux_tva)) {
            $this->montant_tva = Depense::tvaFromTtc((float) $this->montant_ttc, (float) $this->taux_tva);
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $d = Depense::findOrFail($id);
        $this->resetForm();
        $this->editId = $d->id;
        $this->date = $d->date->format('Y-m-d');
        $this->categorie = $d->categorie;
        $this->libelle = $d->libelle;
        $this->fournisseur_id = $d->fournisseur_id ?? '';
        $this->montant_ttc = (string) $d->montant_ttc;
        $this->montant_tva = (string) $d->montant_tva;
        $this->taux_tva = '';
        $this->mode_paiement = $d->mode_paiement;
        $this->tresorerie_compte_id = $d->tresorerie_compte_id ?? '';
        $this->reference = (string) $d->reference;
        $this->notes = (string) $d->notes;
        $this->justificatifPath = $d->justificatif_path;
        $this->showModal = true;
    }

    protected function resetForm(): void
    {
        $this->resetErrorBag();
        $this->reset(['editId', 'libelle', 'fournisseur_id', 'montant_ttc', 'montant_tva', 'tresorerie_compte_id', 'reference', 'notes', 'justificatif', 'justificatifPath', 'removeJustificatif']);
        $this->date = now()->format('Y-m-d');
        $this->categorie = 'autre';
        $this->mode_paiement = 'especes';
        $this->taux_tva = (string) (float) (Societe::current()->tva_rate_default ?? 20);
    }

    public function save(): void
    {
        $this->validate([
            'date' => 'required|date',
            'categorie' => 'required|in:'.implode(',', array_keys(Depense::CATEGORIES)),
            'libelle' => 'required|string|max:255',
            'fournisseur_id' => 'nullable|integer',
            'montant_ttc' => 'required|numeric|min:0.01|max:99999999',
            'montant_tva' => 'nullable|numeric|min:0|lte:montant_ttc',
            'mode_paiement' => 'required|in:'.implode(',', array_keys(Depense::MODES)),
            'tresorerie_compte_id' => 'nullable|integer',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'justificatif' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ], [], [
            'montant_ttc' => 'montant TTC', 'montant_tva' => 'TVA', 'libelle' => 'libellé', 'justificatif' => 'justificatif',
        ]);

        // ids must belong to this company (global scope): never trust the posted value
        if ($this->fournisseur_id && ! Fournisseur::find($this->fournisseur_id)) {
            $this->addError('fournisseur_id', 'Fournisseur introuvable.');

            return;
        }
        if ($this->tresorerie_compte_id && ! TresorerieCompte::find($this->tresorerie_compte_id)) {
            $this->addError('tresorerie_compte_id', 'Compte introuvable.');

            return;
        }

        $d = $this->editId ? Depense::findOrFail($this->editId) : new Depense(['user_id' => auth()->id()]);
        $old = $d->justificatif_path;
        $newPath = $old;
        if ($this->justificatif) {
            $newPath = $this->justificatif->store('depenses/'.auth()->user()->project_id, 'local');
        } elseif ($this->removeJustificatif) {
            $newPath = null;
        }

        DB::transaction(function () use ($d, $newPath) {
            $d->fill([
                'date' => $this->date,
                'categorie' => $this->categorie,
                'libelle' => trim($this->libelle),
                'fournisseur_id' => $this->fournisseur_id ?: null,
                'montant_ttc' => round((float) $this->montant_ttc, 2),
                'montant_tva' => round((float) ($this->montant_tva ?: 0), 2),
                'mode_paiement' => $this->mode_paiement,
                'tresorerie_compte_id' => $this->tresorerie_compte_id ?: null,
                'reference' => trim($this->reference) ?: null,
                'notes' => trim($this->notes) ?: null,
                'justificatif_path' => $newPath,
            ])->save();
            $d->syncTresorerie();
        });

        if ($old && $old !== $newPath) {
            Storage::disk('local')->delete($old);
        }

        $this->showModal = false;
        session()->flash('success', $this->editId ? 'Dépense modifiée.' : 'Dépense enregistrée.');
        $this->editId = null;
    }

    public function delete(int $id): void
    {
        Depense::findOrFail($id)->delete();
        session()->flash('success', 'Dépense supprimée.');
    }

    protected function filtered()
    {
        return Depense::query()
            ->when($this->mois !== 'tout', function ($q) {
                $start = Carbon::createFromFormat('Y-m-d', $this->mois.'-01')->startOfMonth();
                $q->whereBetween('date', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()]);
            })
            ->when($this->filtreCategorie, fn ($q) => $q->where('categorie', $this->filtreCategorie))
            ->when(trim($this->search) !== '', function ($q) {
                $s = '%'.trim($this->search).'%';
                $q->where(fn ($w) => $w->where('libelle', 'like', $s)->orWhere('reference', 'like', $s));
            });
    }

    public function export()
    {
        $rows = $this->filtered()->with(['fournisseur', 'tresorerieCompte'])->orderBy('date')->orderBy('id')->get();
        $name = 'depenses-'.($this->mois === 'tout' ? 'tout' : $this->mois).'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Catégorie', 'Libellé', 'Fournisseur', 'HT', 'TVA', 'TTC', 'Paiement', 'Compte', 'Référence', 'Notes'], ';');
            foreach ($rows as $d) {
                fputcsv($out, [
                    $d->date->format('d/m/Y'), $d->categorieLabel(), $d->libelle, $d->fournisseur->name ?? '',
                    number_format($d->montant_ht, 2, ',', ''), number_format((float) $d->montant_tva, 2, ',', ''),
                    number_format((float) $d->montant_ttc, 2, ',', ''), Depense::MODES[$d->mode_paiement] ?? $d->mode_paiement,
                    $d->tresorerieCompte->name ?? '', $d->reference ?? '', $d->notes ?? '',
                ], ';');
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function render()
    {
        $base = $this->filtered();
        $totTtc = (float) (clone $base)->sum('montant_ttc');
        $totTva = (float) (clone $base)->sum('montant_tva');
        $parCategorie = (clone $base)->selectRaw('categorie, SUM(montant_ttc) as total')->groupBy('categorie')->orderByDesc('total')->get();

        $moisOptions = collect(range(0, 23))->mapWithKeys(function ($i) {
            $m = now()->startOfMonth()->subMonths($i);

            return [$m->format('Y-m') => ucfirst($m->locale('fr')->translatedFormat('F Y'))];
        });

        return view('livewire.depenses.index', [
            'depenses' => (clone $base)->with(['fournisseur', 'tresorerieCompte'])->latest('date')->latest('id')->paginate(20),
            'totTtc' => $totTtc,
            'totTva' => $totTva,
            'parCategorie' => $parCategorie,
            'moisOptions' => $moisOptions,
            'fournisseurs' => Fournisseur::orderBy('name')->get(['id', 'name']),
            'comptes' => TresorerieCompte::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'currency' => Societe::current()->currency ?? '',
        ])->layout('layouts.app');
    }
}
