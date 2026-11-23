<?php
namespace App\Livewire\Rappels;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\Rappel;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, RequiresPermission;

    public bool $showModal = false;

    public function mount()
    {
        $this->requirePermission('rappels');
    }

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public $title = '';

    public $description = '';

    #[Validate('required|date')]
    public $due_date;

    public function create()
    {
        $this->reset(['editingId', 'title', 'description']);
        $this->due_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit($id)
    {
        $rappel = Rappel::findOrFail($id);
        $this->editingId = $rappel->id;
        $this->title = $rappel->title;
        $this->description = $rappel->description;
        $this->due_date = $rappel->due_date->format('Y-m-d');
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => auth()->id(),
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date,
        ];

        if ($this->editingId) {
            Rappel::findOrFail($this->editingId)->update($data);
        } else {
            Rappel::create($data);
        }

        $this->showModal = false;
        session()->flash('success', 'Rappel enregistré.');
    }

    public function toggleDone($id)
    {
        $rappel = Rappel::findOrFail($id);
        $rappel->update(['is_done' => ! $rappel->is_done]);
    }

    public function delete($id)
    {
        if (\App\Support\Tenant::tryDelete(Rappel::findOrFail($id))) {
            session()->flash('success', 'Rappel supprimé.');
        }
    }

    public function render()
    {
        $rappels = Rappel::query()
            ->orderBy('is_done')
            ->orderBy('due_date')
            ->paginate(15);

        return view('livewire.rappels.index', ['rappels' => $rappels])
            ->layout('layouts.app');
    }
}
