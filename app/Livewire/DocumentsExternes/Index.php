<?php
namespace App\Livewire\DocumentsExternes;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\DocumentExterne;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination, RequiresPermission;

    public bool $showModal = false;

    public function mount()
    {
        $this->requirePermission('documents_externes');
    }

    #[Validate('required|string|max:255')]
    public $title = '';

    #[Validate('required|file|max:10240')]
    public $file;

    public $notes = '';

    public function create()
    {
        $this->reset(['title', 'file', 'notes']);
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $path = $this->file->store('documents', 'public');

        DocumentExterne::create([
            'title' => $this->title,
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
            'notes' => $this->notes,
        ]);

        $this->showModal = false;
        session()->flash('success', 'Document televerse.');
    }

    public function delete($id)
    {
        $doc = DocumentExterne::findOrFail($id);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
        session()->flash('success', 'Document supprimé.');
    }

    public function render()
    {
        return view('livewire.documents-externes.index', [
            'documents' => DocumentExterne::with('uploader')->latest()->paginate(15),
        ])->layout('layouts.app');
    }
}
