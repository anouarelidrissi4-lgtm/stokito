<?php

namespace App\Livewire\Blog;

use App\Models\BlogPost;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'categorie', history: true)]
    public string $category = '';

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $featured = BlogPost::published()
            ->where('featured', true)
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->latest('published_at')
            ->take(2)
            ->get();

        $featuredIds = $featured->pluck('id');

        $posts = BlogPost::published()
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($featuredIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $featuredIds))
            ->latest('published_at')
            ->paginate(9);

        $categories = BlogPost::published()
            ->whereNotNull('category')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('livewire.blog.index', [
            'featured' => $featured,
            'posts' => $posts,
            'categories' => $categories,
        ])->layout('layouts.public', [
            'title' => 'Blog Stokito : guides de gestion de stock et de facturation',
            'description' => 'Guides pratiques : méthodes de gestion de stock, inventaire, stock de sécurité, mentions obligatoires sur les factures et facturation électronique.',
        ]);
    }
}
