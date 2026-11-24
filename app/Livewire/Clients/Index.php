<?php

namespace App\Livewire\Clients;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    #[\Livewire\Attributes\Url(as: 'q', except: '')]
    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public function mount()
    {
        $this->requirePermission('gestion_clients');
    }

    public string $name = '';
    public string $ice = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public $credit_limit = null;

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $c = Client::findOrFail($id);
        $this->editingId = $c->id;
        $this->name = $c->name;
        $this->ice = (string) $c->ice;
        $this->phone = (string) $c->phone;
        $this->email = (string) $c->email;
        $this->address = (string) $c->address;
        $this->credit_limit = $c->credit_limit;
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
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        Client::updateOrCreate(['id' => $this->editingId], [
            'name' => $this->name,
            'ice' => $this->ice ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'address' => $this->address ?: null,
            'credit_limit' => $this->credit_limit ?: null,
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Client enregistré.');
    }

    public function delete(int $id): void
    {
        if (\App\Support\Tenant::tryDelete(Client::findOrFail($id))) {
            session()->flash('success', 'Client supprimé.');
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
        $this->credit_limit = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.clients.index', [
            'clients' => Client::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
        ])->layout('layouts.app');
    }
}
