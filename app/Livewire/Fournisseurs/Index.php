<?php

namespace App\Livewire\Fournisseurs;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Fournisseur;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public function mount()
    {
        $this->requirePermission('gestion_fournisseurs');
    }

    public string $name = '';
    public string $ice = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $f = Fournisseur::findOrFail($id);
        $this->editingId = $f->id;
        $this->name = $f->name;
        $this->ice = (string) $f->ice;
        $this->phone = (string) $f->phone;
        $this->email = (string) $f->email;
        $this->address = (string) $f->address;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'ice' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        Fournisseur::updateOrCreate(['id' => $this->editingId], [
            'name' => $this->name,
            'ice' => $this->ice ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'address' => $this->address ?: null,
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Fournisseur enregistré.');
    }

    public function delete(int $id): void
    {
        if (\App\Support\Tenant::tryDelete(Fournisseur::findOrFail($id))) {
            session()->flash('success', 'Fournisseur supprimé.');
        }
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->ice = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.fournisseurs.index', [
            'fournisseurs' => Fournisseur::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
        ])->layout('layouts.app');
    }
}
