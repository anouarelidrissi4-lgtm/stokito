<?php
namespace App\Livewire\TresorerieComptes;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\TresorerieCompte;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|in:banque,caisse')]
    public $type = 'caisse';

    public $bank_name = '';

    public $rib = '';

    #[Validate('required|numeric')]
    public $initial_balance = 0;

    public $is_active = true;

    public ?string $typeFilter = null;

    public string $pageTitle = 'Comptes Tresorerie';

    public function mount($type = null)
    {
        $this->requirePermission($type === 'banque' ? 'gestion_comptes_bancaires' : ($type === 'caisse' ? 'porte_feuilles' : 'gestion_comptes_bancaires'));

        $this->typeFilter = $type;
        $this->pageTitle = $type === 'banque' ? 'Comptes Bancaires' : ($type === 'caisse' ? 'Porte-Feuilles' : 'Comptes Tresorerie');
    }

    public function create()
    {
        $this->reset(['editingId', 'name', 'bank_name', 'rib', 'initial_balance', 'is_active']);
        $this->type = $this->typeFilter ?? 'caisse';
        $this->initial_balance = 0;
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $compte = TresorerieCompte::findOrFail($id);
        $this->editingId = $compte->id;
        $this->name = $compte->name;
        $this->type = $compte->type;
        $this->bank_name = $compte->bank_name;
        $this->rib = $compte->rib;
        $this->initial_balance = $compte->initial_balance;
        $this->is_active = $compte->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'bank_name' => $this->bank_name ?: null,
            'rib' => $this->rib ?: null,
            'initial_balance' => $this->initial_balance,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            TresorerieCompte::findOrFail($this->editingId)->update($data);
        } else {
            TresorerieCompte::create($data);
        }

        $this->showModal = false;
        session()->flash('success', 'Compte enregistré.');
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(TresorerieCompte::findOrFail($id))) {
            session()->flash('success', 'Compte supprimé.');
        }
    }

    public function render()
    {
        $comptes = TresorerieCompte::query()
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);

        $comptes->getCollection()->transform(function ($compte) {
            $compte->solde_actuel = $compte->currentBalance();

            return $compte;
        });

        return view('livewire.tresorerie-comptes.index', ['comptes' => $comptes])
            ->layout('layouts.app');
    }
}
