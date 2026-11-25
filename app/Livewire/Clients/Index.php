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

    public string $code = '';
    public string $name = '';
    public string $prenom = '';
    public string $ice = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public $credit_limit = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        // shown in the form, can be changed; given again at save time if left empty
        $u = auth()->user();
        $this->code = Client::nextCode($u->project_id, (bool) $u->is_demo);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $c = Client::findOrFail($id);
        $this->editingId = $c->id;
        $this->code = (string) $c->code;
        $this->name = $c->name;
        $this->prenom = (string) $c->prenom;
        $this->ice = (string) $c->ice;
        $this->phone = (string) $c->phone;
        $this->email = (string) $c->email;
        $this->address = (string) $c->address;
        $this->credit_limit = $c->credit_limit;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->code = trim($this->code);
        $this->validate([
            'code' => ['nullable', 'string', 'max:50', \App\Support\Tenant::unique('clients', 'code', $this->editingId)],
            'name' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:100',
            'ice' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ], [], ['code' => 'code client', 'name' => 'nom', 'prenom' => 'prénom']);

        Client::updateOrCreate(['id' => $this->editingId], [
            'code' => $this->code !== '' ? $this->code : ($this->editingId ? Client::find($this->editingId)?->code : null),
            'name' => $this->name,
            'prenom' => trim($this->prenom) ?: null,
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
        $this->code = '';
        $this->name = '';
        $this->prenom = '';
        $this->ice = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->credit_limit = null;
        $this->resetValidation();
    }

    /** search on name, first name, code, phone, email and company id (also used by the print page) */
    public static function searchQuery(string $search)
    {
        return Client::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")
                ->orWhere('prenom', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('ice', 'like', "%{$search}%")))
            ->orderBy('name')->orderBy('prenom');
    }

    public function render()
    {
        return view('livewire.clients.index', [
            'clients' => static::searchQuery(trim($this->search))->paginate(10),
        ])->layout('layouts.app');
    }
}
