<?php

namespace App\Livewire\Blog;

use App\Models\BlogPost;
use Livewire\Component;

class Show extends Component
{
    public BlogPost $post;

    public function mount($slug)
    {
        $this->post = BlogPost::published()->where('slug', $slug)->firstOrFail();
    }

    public function render()
    {
        $related = BlogPost::published()
            ->where('id', '!=', $this->post->id)
            ->when($this->post->category, fn ($q) => $q->where('category', $this->post->category))
            ->latest('published_at')
            ->take(3)
            ->get();
        // few articles yet: fill with the latest posts of any category so every article links to the others
        if ($related->count() < 3) {
            $related = $related->concat(BlogPost::published()
                ->whereNotIn('id', $related->pluck('id')->push($this->post->id))
                ->latest('published_at')->take(3 - $related->count())->get());
        }

        $graph = [[
            '@type' => 'BlogPosting',
            'headline' => $this->post->title,
            'description' => $this->post->description,
            'datePublished' => optional($this->post->published_at)->toAtomString(),
            'dateModified' => $this->post->updated_at->toAtomString(),
            'author' => ['@type' => 'Organization', 'name' => $this->post->author ?: 'Stokito', 'url' => url('/')],
            'publisher' => ['@type' => 'Organization', 'name' => 'Stokito', 'url' => url('/')],
            'mainEntityOfPage' => url()->current(),
            'inLanguage' => 'fr',
        ], [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => route('blog.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $this->post->title, 'item' => url()->current()],
            ],
        ]];
        foreach ($this->post->contentJsonLd() as $block) {
            unset($block['@context']);
            $graph[] = $block;
        }
        $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('livewire.blog.show', [
            'related' => $related,
            'contentHtml' => $this->post->contentHtml(),
        ])->layout('layouts.public', [
            'title' => $this->post->title.' - Blog Stokito',
            'description' => $this->post->description,
            'ogImage' => $this->post->cover_image,
            'ogType' => 'article',
            'jsonLd' => $jsonLd,
        ]);
    }
}
