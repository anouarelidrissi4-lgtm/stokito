<?php

namespace App\Livewire\BlogAdmin;

use App\Models\BlogPost;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount()
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
    }

    public function delete($id)
    {
        BlogPost::findOrFail($id)->delete();
        session()->flash('success', 'Article supprimé.');
    }

    public function render()
    {
        $posts = BlogPost::query()
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->latest('updated_at')
            ->paginate(15);

        return view('livewire.blog-admin.index', ['posts' => $posts])->layout('layouts.app');
    }
}
