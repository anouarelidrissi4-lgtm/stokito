<?php
namespace App\Livewire\Devis;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Devis;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;
    use \App\Livewire\Concerns\SendsDocument;

    protected function sendModelClass(): string
    {
        return \App\Models\Devis::class;
    }

    public string $search = '';

    public string $numeroSearch = '';

    public string $dateStart = '';

    public string $dateEnd = '';

    public function updating($name): void
    {
        if (in_array($name, ['dateStart', 'dateEnd'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'numeroSearch', 'dateStart', 'dateEnd']);
        $this->resetPage();
    }

    protected function filters(): array
    {
        return ['search' => $this->search, 'numeroSearch' => $this->numeroSearch, 'dateStart' => $this->dateStart, 'dateEnd' => $this->dateEnd];
    }

    public function mount()
    {
        $this->requirePermission('devis_consulter');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function searchByNumero()
    {
        $this->resetPage();
    }

    public function resetNumeroSearch()
    {
        $this->numeroSearch = '';
        $this->resetPage();
    }

    public function delete($id)
    {
        $this->requirePermission('devis_supprimer');
        if (\App\Support\Tenant::tryDelete(Devis::findOrFail($id))) {
            session()->flash('success', 'Devis supprimé.');
        }
    }

    public function convertToFacture($id)
    {
        $this->requirePermission('devis_valider');
        $devis = Devis::findOrFail($id);

        return $this->redirect(route('factures.create').'?devis='.$devis->id, navigate: true);
    }

    public function convertToBonLivraison($id)
    {
        $this->requirePermission('devis_valider');
        $devis = Devis::with('items')->findOrFail($id);
        if ($devis->bonLivraisons()->exists() || $devis->factures()->exists()) {
            session()->flash('error', 'Ce devis a déjà un bon de livraison ou une facture.');

            return;
        }

        // "Valider en bon de livraison" (2026-11-25): the quote becomes accepted, then the BL is created
        $bl = \Illuminate\Support\Facades\DB::transaction(function () use ($devis) {
            if (in_array($devis->status, ['draft', 'sent'], true)) {
                $devis->update(['status' => 'accepted']);
            }

            return $devis->toBonLivraison();
        });

        session()->flash('success', 'Bon de livraison '.$bl->numero.' cree a partir du devis.');
        return $this->redirect(route('bon-livraisons.index'), navigate: true);
    }

    /** Copy a quote (lines included) as a new draft dated today, then open it (2026-11-25). */
    public function duplicate($id)
    {
        $this->requirePermission('devis_creer');
        $src = Devis::with('items')->findOrFail($id);
        $copy = \Illuminate\Support\Facades\DB::transaction(function () use ($src) {
            $days = $src->date && $src->valid_until ? max(1, $src->date->diffInDays($src->valid_until)) : 30;
            $new = $src->replicate(['numero', 'sent_at']);
            $new->numero = \App\Support\Tenant::nextNumero(Devis::class, 'DEV-');
            $new->date = today();
            $new->valid_until = today()->addDays($days);
            $new->status = 'draft';
            $new->sent_at = null;
            $new->save();
            foreach ($src->items as $item) {
                $line = $item->replicate();
                $line->devis_id = $new->id;
                $line->save();
            }

            return $new;
        });
        session()->flash('success', 'Devis '.$src->numero.' dupliqué : '.$copy->numero.'.');

        return $this->redirect(route('devis.edit', $copy->id), navigate: true);
    }

    public function render()
    {
        $devisList = \App\Support\SalesLists::devis($this->filters())->paginate(10);

        return view('livewire.devis.index', ['devisList' => $devisList, 'totals' => \App\Support\SalesLists::totals('devis', $this->filters()), 'printUrl' => route('devis.print', array_filter($this->filters()))])
            ->layout('layouts.app');
    }
}
