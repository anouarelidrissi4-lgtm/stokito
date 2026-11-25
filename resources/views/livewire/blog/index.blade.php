<div>
    {{-- Hero --}}
    <section class="bg-zinc-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14">
            <nav aria-label="Fil d’Ariane" class="ind-mono text-xs text-zinc-400">
                <a href="{{ url('/') }}" class="hover:text-zinc-900">{{ __('Accueil') }}</a> <span aria-hidden="true">/</span> <span class="text-[#d6d6d6]">{{ __('Blog') }}</span>
            </nav>
            <p class="mt-8 ind-mono text-xs text-amber-300">BLOG · {{ __('Guides pratiques') }}</p>
            <h1 class="mt-3 ind-h text-5xl sm:text-5xl leading-[1.05] max-w-4xl">{{ __('Stock, devis, factures :') }} <span class="text-amber-300">{{ __('les guides') }}</span></h1>
            <p class="mt-5 text-lg text-zinc-300 max-w-2xl leading-relaxed">{{ __('Méthodes de gestion de stock, calculs, inventaire, mentions obligatoires et facturation électronique, expliqués simplement avec des exemples chiffrés.') }}</p>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
        @if ($categories->isNotEmpty())
            <div class="flex flex-wrap gap-0 mb-10">
                <button wire:click="$set('category', '')" class="ind-h px-4 py-2 border border-zinc-200 -me-0.5 -mb-0.5 {{ $category === '' ? 'bg-zinc-900 text-amber-300' : 'bg-white hover:bg-zinc-100' }} rounded-lg">{{ __('Tout') }}</button>
                @foreach ($categories as $cat)
                    <button wire:click="$set('category', @js($cat))" class="ind-h px-4 py-2 border border-zinc-200 -me-0.5 -mb-0.5 {{ $category === $cat ? 'bg-zinc-900 text-amber-300' : 'bg-white hover:bg-zinc-100' }} rounded-lg">{{ $cat }}</button>
                @endforeach
            </div>
        @endif

        @php $all = $featured->concat($posts->items()); @endphp
        @if ($all->isEmpty())
            <div class="bg-white border border-zinc-200 p-12 text-center text-zinc-500 rounded-xl">{{ __('Aucun article pour le moment.') }}</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                @foreach ($all as $i => $post)
                    <a href="{{ route('blog.show', $post->slug) }}" class="group flex flex-col bg-white border border-zinc-200 shadow-lg shadow-zinc-900/5 hover:-translate-y-0.5 transition rounded-xl">
                        <div class="flex items-center justify-between px-5 py-3 border-b border-zinc-200 bg-zinc-50 group-hover:bg-[#ffcc00]">
                            <span class="ind-mono text-[11px]">{{ $post->category ?: 'Blog' }}</span>
                            <span class="ind-mono text-[11px]">REF · BLOG-{{ sprintf('%02d', $post->id) }}</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col">
                            <h2 class="ind-h text-xl leading-none">{{ $post->title }}</h2>
                            <p class="mt-3 text-zinc-600 leading-relaxed line-clamp-3">{{ $post->description }}</p>
                            <div class="mt-auto pt-5 flex items-center justify-between">
                                <span class="ind-mono text-xs text-zinc-500">{{ optional($post->published_at)->translatedFormat('d M Y') }} · {{ $post->reading_time }} min</span>
                                <span class="ind-h">{{ __('Lire l’article') }} →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mb-6">{{ $posts->links() }}</div>
        @endif
    </div>

    {{-- CTA --}}
    <section class="bg-zinc-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 grid md:grid-cols-3 gap-8 items-center">
            <div class="md:col-span-2">
                <h2 class="ind-h text-3xl leading-none">{{ __('Passez de la théorie à la pratique') }}</h2>
                <p class="mt-3 text-zinc-300 max-w-xl">{{ __('Stock, devis, bons de livraison et factures au même endroit. Créez votre espace en une minute, sans carte bancaire.') }}</p>
            </div>
            <div class="flex flex-col gap-3">
                <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] text-zinc-900 justify-center">{{ __('Ajouter un compte gratuit') }}</a>
                <a href="{{ route('demo.login') }}" class="ind-btn bg-white text-zinc-900 justify-center">{{ __('Tester la démo') }}</a>
            </div>
        </div>
    </section>
</div>
