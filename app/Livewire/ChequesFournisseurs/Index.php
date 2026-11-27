<?php

namespace App\Livewire\ChequesFournisseurs;

use App\Models\ChequeEffetFournisseur;
use App\Models\Fournisseur;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Chèques / effets fournisseurs (2026-11-27): filters on the due date, totals, list grouped by supplier, PDF. */
class Index extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'du')]
    public string $from = '';

    #[Url(as: 'au')]
    public string $to = '';

    #[Url]
    public string $statut = '';           // '' all, en_attente, encaisse, rejete, retard

    public bool $showModal = false;

    public $fournisseur_id = '';

    public $type = 'cheque';

    public $numero_cheque = '';

    public $banque = '';

    public $montant = 0;

    public $date_emission;

    public $date_echeance;

    public const STATUTS = ['' => 'Tous', 'en_attente' => 'Non payés', 'retard' => 'Échéance passée non payés', 'encaisse' => 'Payés', 'rejete' => 'Rejetés'];

    public function create(): void
    {
        $this->reset(['fournisseur_id', 'numero_cheque', 'banque', 'montant']);
        $this->resetValidation();
        $this->type = 'cheque';
        $this->date_emission = now()->format('Y-m-d');
        $this->date_echeance = now()->addDays(30)->format('Y-m-d');
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'type' => 'required|in:cheque,effet',
            'numero_cheque' => 'required|string|max:255',
            'banque' => 'nullable|string|max:255',
            'montant' => 'required|numeric|min:0.01',
            'date_emission' => 'required|date',
            'date_echeance' => 'required|date',
        ], [], ['fournisseur_id' => 'fournisseur', 'numero_cheque' => 'numéro', 'date_emission' => 'date de dépôt', 'date_echeance' => 'échéance']);
        if (! Fournisseur::find($this->fournisseur_id)) {
            $this->addError('fournisseur_id', 'Fournisseur introuvable.');

            return;
        }
        ChequeEffetFournisseur::create([
            'fournisseur_id' => $this->fournisseur_id, 'type' => $this->type, 'numero_cheque' => $this->numero_cheque, 'banque' => $this->banque ?: null,
            'montant' => $this->montant, 'date_emission' => $this->date_emission, 'date_echeance' => $this->date_echeance, 'status' => 'en_attente',
        ]);
        $this->showModal = false;
        session()->flash('success', ($this->type === 'effet' ? 'Effet' : 'Chèque').' enregistré.');
    }

    public function markStatus($id, $status): void
    {
        abort_unless(in_array($status, ['en_attente', 'encaisse', 'rejete'], true), 422);
        ChequeEffetFournisseur::findOrFail($id)->update(['status' => $status]);
        session()->flash('success', 'Paiement mis à jour : '.ChequeEffetFournisseur::PAIEMENT[$status].'.');
    }

    public function delete($id): void
    {
        if (\App\Support\Tenant::tryDelete(ChequeEffetFournisseur::findOrFail($id))) {
            session()->flash('success', 'Chèque / effet supprimé.');
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'from', 'to', 'statut']);
    }

    /** shared with the PDF route */
    public static function data(string $search, string $from, string $to, string $statut): array
    {
        $d = fn ($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;
        $from = $d($from);
        $to = $d($to);
        $s = trim($search);
        $today = now()->toDateString();

        $cheques = ChequeEffetFournisseur::query()
            ->with(['fournisseur', 'reglementFournisseur.commandeFournisseur', 'reglementFournisseur.tresorerieCompte'])
            ->when($s !== '', fn ($q) => $q->where(fn ($w) => $w->where('numero_cheque', 'like', "%{$s}%")->orWhere('banque', 'like', "%{$s}%")
                ->orWhereHas('fournisseur', fn ($f) => $f->where('name', 'like', "%{$s}%"))))
            ->when($from, fn ($q) => $q->whereDate('date_echeance', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date_echeance', '<=', $to))
            ->when(in_array($statut, ['en_attente', 'encaisse', 'rejete'], true), fn ($q) => $q->where('status', $statut))
            ->when($statut === 'retard', fn ($q) => $q->where('status', 'en_attente')->whereDate('date_echeance', '<', $today))
            ->orderBy('date_echeance')->orderBy('id')
            ->get();

        $paid = $cheques->where('status', 'encaisse');
        $late = $cheques->filter(fn ($c) => $c->status === 'en_attente' && $c->date_echeance->toDateString() < $today);
        $groups = $cheques->groupBy(fn ($c) => $c->fournisseur->name ?? '—')->sortKeys();

        return [
            'groups' => $groups,
            'stats' => [
                'all' => [$cheques->count(), (float) $cheques->sum('montant')],
                'paid' => [$paid->count(), (float) $paid->sum('montant')],
                'late' => [$late->count(), (float) $late->sum('montant')],
            ],
            'from' => $from,
            'to' => $to,
            'statut' => $statut,
            'search' => $s,
        ];
    }

    public function render()
    {
        $data = self::data($this->search, $this->from, $this->to, $this->statut);

        return view('livewire.cheques-fournisseurs.index', $data + [
            'fournisseurs' => Fournisseur::orderBy('name')->get(),
            'pdfUrl' => route('cheques-fournisseurs.pdf', array_filter(['q' => $data['search'], 'du' => $data['from'], 'au' => $data['to'], 'statut' => $this->statut])),
        ])->layout('layouts.app');
    }
}
