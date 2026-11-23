<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class BlogPost extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'content', 'cover_image',
        'category', 'reading_time', 'featured', 'status', 'published_at', 'author',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /** JSON-LD blocks written inside the article (e.g. FAQPage), decoded. They go to <head>, not into the body. */
    public function contentJsonLd(): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $this->content ?? '', $m);

        return array_values(array_filter(array_map(fn ($j) => json_decode(trim($j), true), $m[1])));
    }

    public function contentHtml(): string
    {
        static $converter = null;
        // html_input is 'allow' (not 'strip') because blog content is only ever
        // authored by the trusted superadmin via the blog-admin panel, never by
        // untrusted/public input - this lets us embed hand-styled layout blocks
        // (card grids, tables) directly in the article content.
        $converter ??= new GithubFlavoredMarkdownConverter([ // GFM: tables, autolinks (2026-11-22)
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);

        // JSON-LD is printed in <head> by Blog\Show (GFM would escape <script> and show it as text)
        $md = preg_replace('#<script type="application/ld\+json">.*?</script>#s', '', $this->content ?? '');

        return (string) $converter->convert($md);
    }

    public static function uniqueSlugFrom(string $title, ?int $ignoreId = null): string
    {
        $base = \Illuminate\Support\Str::slug($title) ?: 'article';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
