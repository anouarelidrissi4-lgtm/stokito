<?php

namespace App\Livewire\Categories;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Category;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public string $search = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    public function mount()
    {
        $this->requirePermission('gestion_categories');
    }

    public ?int $editingId = null;
    public bool $showModal = false;

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Category::updateOrCreate(['id' => $this->editingId], ['name' => $this->name]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Catégorie enregistrée.');
    }

    public function delete(int $id): void
    {
        if (\App\Support\Tenant::tryDelete(Category::findOrFail($id))) {
            session()->flash('success', 'Catégorie supprimée.');
        }
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.categories.index', [
            'categories' => Category::withCount('products')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
        ])->layout('layouts.app');
    }
}
