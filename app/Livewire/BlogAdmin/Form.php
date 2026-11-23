<?php

namespace App\Livewire\BlogAdmin;

use App\Models\BlogPost;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Form extends Component
{
    public ?BlogPost $post = null;

    #[Validate('required|string|max:255')]
    public $title = '';

    public $slug = '';

    #[Validate('nullable|string|max:320')]
    public $description = '';

    #[Validate('required|string')]
    public $content = '';

    public $cover_image = '';

    public $category = '';

    #[Validate('required|integer|min:1|max:120')]
    public $reading_time = 5;

    public $featured = false;

    #[Validate('required|in:draft,published')]
    public $status = 'draft';

    public $published_at = '';

    public $author = 'Stokito';

    public function mount($postId = null)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        if ($postId) {
            $this->post = BlogPost::findOrFail($postId);
            $this->title = $this->post->title;
            $this->slug = $this->post->slug;
            $this->description = $this->post->description ?? '';
            $this->content = $this->post->content;
            $this->cover_image = $this->post->cover_image ?? '';
            $this->category = $this->post->category ?? '';
            $this->reading_time = $this->post->reading_time;
            $this->featured = $this->post->featured;
            $this->status = $this->post->status;
            $this->published_at = optional($this->post->published_at)->format('Y-m-d');
            $this->author = $this->post->author;
        } else {
            $this->published_at = now()->format('Y-m-d');
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:320',
            'content' => 'required|string',
            'reading_time' => 'required|integer|min:1|max:120',
            'status' => 'required|in:draft,published',
        ]);

        $slug = $this->slug ?: BlogPost::uniqueSlugFrom($this->title, $this->post?->id);

        $data = [
            'title' => $this->title,
            'slug' => $slug,
            'description' => $this->description ?: null,
            'content' => $this->content,
            'cover_image' => $this->cover_image ?: null,
            'category' => $this->category ?: null,
            'reading_time' => $this->reading_time,
            'featured' => (bool) $this->featured,
            'status' => $this->status,
            'published_at' => $this->published_at ?: ($this->status === 'published' ? now() : null),
            'author' => $this->author ?: 'Stokito',
        ];

        if ($this->post) {
            $this->post->update($data);
        } else {
            $this->post = BlogPost::create($data);
        }

        session()->flash('success', 'Article enregistré.');

        return $this->redirect(route('blog-admin.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.blog-admin.form', [
            'categories' => BlogPost::whereNotNull('category')->distinct()->pluck('category'),
        ])->layout('layouts.app');
    }
}
