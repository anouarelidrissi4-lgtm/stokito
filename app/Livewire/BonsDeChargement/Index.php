<?php
namespace App\Livewire\BonsDeChargement;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\BonDeChargement;
use App\Models\BonLivraison;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    public bool $showModal = false;

    public function mount()
    {
        $this->requirePermission('bons_chargement');
        abort_unless(\App\Models\Societe::current()->bons_chargement_enabled, 403, "Le module Bons de chargement est désactivé. Activez-le depuis Ma Société.");
        \App\Support\Chargement::ensureForAll();
    }

    public $date;

    public $vehicule = '';

    public $chauffeur = '';

    public $commercial = '';

    public $notes = '';

    public array $selectedBonLivraisons = [];

    public function create(): void
    {
        $this->reset(['vehicule', 'chauffeur', 'notes', 'selectedBonLivraisons']);
        $this->resetValidation();
        $this->date = now()->format('Y-m-d');
        $this->commercial = (string) auth()->user()?->name;
        $this->showModal = true;
    }

    /** a slip for several delivery notes (one truck): the products are merged line by line */
    public function save()
    {
        $this->validate([
            'date' => 'required|date',
            'vehicule' => 'nullable|string|max:120',
            'chauffeur' => 'nullable|string|max:120',
            'commercial' => 'nullable|string|max:120',
            'notes' => 'nullable|string|max:1000',
            'selectedBonLivraisons' => 'required|array|min:1',
        ], ['selectedBonLivraisons.required' => 'Sélectionnez au moins un bon de livraison.', 'selectedBonLivraisons.min' => 'Sélectionnez au moins un bon de livraison.']);

        $bls = BonLivraison::with('items.product')->whereIn('id', array_map('intval', $this->selectedBonLivraisons))->orderBy('date')->orderBy('id')->get();
        if ($bls->isEmpty()) {
            $this->addError('selectedBonLivraisons', 'Sélectionnez au moins un bon de livraison.');

            return;
        }

        $bc = \Illuminate\Support\Facades\DB::transaction(function () use ($bls) {
            $bc = BonDeChargement::create([
                'numero' => \App\Support\Tenant::nextNumero(BonDeChargement::class, 'BC-'),
                'date' => $this->date,
                'vehicule' => trim((string) $this->vehicule) ?: null,
                'chauffeur' => trim((string) $this->chauffeur) ?: null,
                'commercial' => trim((string) $this->commercial) ?: null,
                'notes' => trim((string) $this->notes) ?: null,
                'status' => 'preparation',
            ]);
            $bc->bonLivraisons()->sync($bls->pluck('id')->all());
            $lines = [];
            foreach ($bls as $bl) {
                foreach ($bl->items as $item) {
                    $k = $item->product_id ?: 'x'.$item->id;
                    $lines[$k] ??= ['product_id' => $item->product_id, 'designation' => $item->product->name ?? '-', 'unite' => $item->product->unit ?? null, 'quantite_commandee' => 0];
                    $lines[$k]['quantite_commandee'] += (float) $item->quantity;
                }
            }
            foreach (array_values($lines) as $i => $l) {
                $bc->lignes()->create($l + ['position' => $i]);
            }
            $bc->refresh()->refreshStatus();

            return $bc;
        });

        $this->showModal = false;
        session()->flash('success', 'Bon de chargement '.$bc->numero.' créé : saisissez les quantités chargées.');

        return $this->redirect(route('bons-de-chargement.show', $bc->id), navigate: true);
    }

    public function delete($id)
    {
        $bc = BonDeChargement::findOrFail($id);
        if ($bc->bon_livraison_id) {
            session()->flash('error', 'Ce bon de chargement suit son bon de livraison : il est supprimé avec le BL.');

            return;
        }
        if (\App\Support\Tenant::tryDelete($bc)) {
            session()->flash('success', 'Bon de chargement supprimé.');
        }
    }

    public function render()
    {
        $s = trim($this->search);
        $chargements = BonDeChargement::query()
            ->with(['bonLivraison.client', 'lignes', 'bonLivraisons.client'])
            ->when($s !== '', fn ($q) => $q->where(fn ($w) => $w->where('numero', 'like', "%{$s}%")
                ->orWhereHas('bonLivraison', fn ($b) => $b->where('numero', 'like', "%{$s}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$s}%")->orWhere('prenom', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")))))
            ->latest('date')->latest('id')
            ->paginate(20);

        return view('livewire.bons-de-chargement.index', [
            'chargements' => $chargements,
            'availableBonLivraisons' => $this->showModal ? BonLivraison::with('client')->orderByDesc('date')->orderByDesc('id')->limit(300)->get() : collect(),
        ])->layout('layouts.app');
    }
}
