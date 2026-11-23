<?php
namespace App\Livewire\Brands;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Brand;
use Livewire\Attributes\Validate;
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
        $this->requirePermission('gestion_marques');
    }

    #[Validate('required|string|max:255')]
    public string $name = '';

    public function create(): void
    {
        $this->reset(['editingId', 'name']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $this->editingId = $brand->id;
        $this->name = $brand->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', \App\Support\Tenant::unique('brands', 'name', $this->editingId)],
        ]);

        Brand::updateOrCreate(['id' => $this->editingId], ['name' => $this->name]);

        $this->showModal = false;
        session()->flash('success', 'Marque enregistrée.');
    }

    public function delete(int $id): void
    {
        if (\App\Support\Tenant::tryDelete(Brand::findOrFail($id))) {
            session()->flash('success', 'Marque supprimée.');
        }
    }

    public function render()
    {
        return view('livewire.brands.index', [
            'brands' => Brand::withCount('products')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
        ])->layout('layouts.app');
    }
}
