<div>
    {{-- Hero --}}
    <section class="bg-[#111] text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14">
            <nav aria-label="Fil d’Ariane" class="ind-mono text-xs text-[#9a9a9a]">
                <a href="{{ url('/') }}" class="hover:text-[#ffcc00]">{{ __('Accueil') }}</a> <span aria-hidden="true">/</span> <span class="text-[#d6d6d6]">{{ __('Blog') }}</span>
            </nav>
            <p class="mt-8 ind-mono text-xs text-[#ffcc00]">BLOG · {{ __('Guides pratiques') }}</p>
            <h1 class="mt-3 ind-h text-5xl sm:text-7xl uppercase leading-[0.9] max-w-4xl">{{ __('Stock, devis, factures :') }} <span class="text-[#ffcc00]">{{ __('les guides') }}</span></h1>
            <p class="mt-5 text-lg text-[#cfcfcf] max-w-2xl leading-relaxed">{{ __('Méthodes de gestion de stock, calculs, inventaire, mentions obligatoires et facturation électronique, expliqués simplement avec des exemples chiffrés.') }}</p>
        </div>
        <div class="ind-hazard h-3"></div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
        @if ($categories->isNotEmpty())
            <div class="flex flex-wrap gap-0 mb-10">
                <button wire:click="$set('category', '')" class="ind-h tracking-wider uppercase px-4 py-2 border-2 border-[#111] -me-0.5 -mb-0.5 {{ $category === '' ? 'bg-[#111] text-[#ffcc00]' : 'bg-white hover:bg-[#ffcc00]' }}">{{ __('Tout') }}</button>
                @foreach ($categories as $cat)
                    <button wire:click="$set('category', @js($cat))" class="ind-h tracking-wider uppercase px-4 py-2 border-2 border-[#111] -me-0.5 -mb-0.5 {{ $category === $cat ? 'bg-[#111] text-[#ffcc00]' : 'bg-white hover:bg-[#ffcc00]' }}">{{ $cat }}</button>
                @endforeach
            </div>
        @endif

        @php $all = $featured->concat($posts->items()); @endphp
        @if ($all->isEmpty())
            <div class="bg-white border-2 border-[#111] p-12 text-center text-[#666]">{{ __('Aucun article pour le moment.') }}</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                @foreach ($all as $i => $post)
                    <a href="{{ route('blog.show', $post->slug) }}" class="group flex flex-col bg-white border-2 border-[#111] shadow-[6px_6px_0_#111] hover:-translate-y-0.5 transition">
                        <div class="flex items-center justify-between px-5 py-3 border-b-2 border-[#111] bg-[#f4f2ec] group-hover:bg-[#ffcc00]">
                            <span class="ind-mono text-[11px] uppercase">{{ $post->category ?: 'Blog' }}</span>
                            <span class="ind-mono text-[11px]">REF · BLOG-{{ sprintf('%02d', $post->id) }}</span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col">
                            <h2 class="ind-h text-3xl uppercase leading-none">{{ $post->title }}</h2>
                            <p class="mt-3 text-[#444] leading-relaxed line-clamp-3">{{ $post->description }}</p>
                            <div class="mt-auto pt-5 flex items-center justify-between">
                                <span class="ind-mono text-xs text-[#666]">{{ optional($post->published_at)->translatedFormat('d M Y') }} · {{ $post->reading_time }} min</span>
                                <span class="ind-h uppercase tracking-wider">{{ __('Lire l’article') }} →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mb-6">{{ $posts->links() }}</div>
        @endif
    </div>

    {{-- CTA --}}
    <section class="bg-[#111] text-white">
        <div class="ind-hazard h-3"></div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 grid md:grid-cols-3 gap-8 items-center">
            <div class="md:col-span-2">
                <h2 class="ind-h text-4xl uppercase leading-none">{{ __('Passez de la théorie à la pratique') }}</h2>
                <p class="mt-3 text-[#cfcfcf] max-w-xl">{{ __('Stock, devis, bons de livraison et factures au même endroit. Créez votre espace en une minute, sans carte bancaire.') }}</p>
            </div>
            <div class="flex flex-col gap-3">
                <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] text-[#111] justify-center">{{ __('Créer un compte gratuit') }}</a>
                <a href="{{ route('demo.login') }}" class="ind-btn bg-white text-[#111] justify-center">{{ __('Tester la démo') }}</a>
            </div>
        </div>
    </section>
</div>
