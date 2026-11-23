<div>
    {{-- Hero + breadcrumb (BreadcrumbList JSON-LD is in Blog\Show) --}}
    <section class="bg-[#111] text-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-10 pb-14">
            <nav aria-label="Fil d’Ariane" class="ind-mono text-xs text-[#9a9a9a]">
                <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <li><a href="{{ url('/') }}" class="hover:text-[#ffcc00]">{{ __('Accueil') }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-[#ffcc00]">{{ __('Blog') }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-[#d6d6d6] line-clamp-1" aria-current="page">{{ $post->title }}</li>
                </ol>
            </nav>
            <div class="mt-8 flex flex-wrap items-center gap-2 ind-mono text-[11px] uppercase">
                @if ($post->category)
                    <span class="bg-[#ffcc00] text-[#111] px-2 py-0.5">{{ $post->category }}</span>
                @endif
                <span class="border border-[#3a3a3a] px-2 py-0.5 text-[#cfcfcf]">{{ optional($post->published_at)->translatedFormat('d M Y') }}</span>
                <span class="border border-[#3a3a3a] px-2 py-0.5 text-[#cfcfcf]">{{ $post->reading_time }} min</span>
            </div>
            <h1 class="mt-5 ind-h text-4xl sm:text-6xl uppercase leading-[0.95]">{{ $post->title }}</h1>
            @if ($post->description)
                <p class="mt-5 text-lg text-[#cfcfcf] leading-relaxed">{{ $post->description }}</p>
            @endif
        </div>
        <div class="ind-hazard h-3"></div>
    </section>

    @if ($post->cover_image)
        <div class="max-w-4xl mx-auto px-4 sm:px-6 mt-10">
            <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-64 sm:h-80 object-cover border-2 border-[#111] shadow-[8px_8px_0_#111]">
        </div>
    @endif

    {{-- Content --}}
    <article class="max-w-2xl mx-auto px-4 sm:px-6 py-14">
        <div class="text-[17px]
            [&_h2]:font-['Barlow_Condensed','Tajawal',sans-serif] [&_h2]:font-extrabold [&_h2]:text-3xl [&_h2]:uppercase [&_h2]:leading-none [&_h2]:mt-16 [&_h2]:mb-5 [&_h2]:pt-4 [&_h2]:border-t-2 [&_h2]:border-[#111]
            [&_h3]:text-xl [&_h3]:font-bold [&_h3]:mt-10 [&_h3]:mb-3 [&_h3]:leading-snug
            [&_p]:mb-6 [&_p]:text-[#333] [&_p]:leading-[1.85]
            [&_a]:font-semibold [&_a]:text-[#111] [&_a]:underline [&_a]:decoration-[#ffcc00] [&_a]:decoration-[3px] [&_a]:underline-offset-4 hover:[&_a]:bg-[#ffcc00]
            [&_ul]:list-disc [&_ul]:ps-6 [&_ul]:mb-6 [&_ul]:space-y-2.5
            [&_ol]:list-decimal [&_ol]:ps-6 [&_ol]:mb-6 [&_ol]:space-y-2.5
            [&_li]:text-[#333] [&_li]:leading-[1.8]
            [&_strong]:font-semibold [&_strong]:text-[#111]
            [&_blockquote]:border-2 [&_blockquote]:border-[#111] [&_blockquote]:bg-white [&_blockquote]:shadow-[6px_6px_0_#111] [&_blockquote]:border-s-8 [&_blockquote]:border-s-[#ffcc00] [&_blockquote]:px-6 [&_blockquote]:py-5 [&_blockquote]:my-10 [&_blockquote]:leading-[1.8]
            [&_blockquote_p]:mb-0
            [&_hr]:my-14 [&_hr]:border-[#111]
            [&_code]:bg-white [&_code]:border [&_code]:border-[#ccc] [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:text-sm
            [&_table]:block [&_table]:w-full [&_table]:overflow-x-auto [&_table]:my-8 [&_table]:text-[15px] [&_table]:border-2 [&_table]:border-[#111] [&_table]:bg-white
            [&_th]:bg-[#111] [&_th]:text-[#ffcc00] [&_th]:text-start [&_th]:font-semibold [&_th]:px-4 [&_th]:py-3 [&_th]:align-top
            [&_td]:px-4 [&_td]:py-3 [&_td]:border-t [&_td]:border-[#ddd] [&_td]:text-[#333] [&_td]:align-top
            [&>*:first-child]:mt-0 [&>h2:first-child]:border-t-0">
            {!! $contentHtml !!}
        </div>
    </article>

    {{-- CTA --}}
    <section class="px-4 sm:px-6 pb-16">
        <div class="max-w-3xl mx-auto bg-[#111] text-white border-2 border-[#111] shadow-[8px_8px_0_#ffcc00]">
            <div class="ind-hazard h-2"></div>
            <div class="p-8 sm:p-10">
                <p class="ind-mono text-xs text-[#ffcc00]">STOKITO · {{ __('Gratuit') }}</p>
                <h2 class="mt-2 ind-h text-3xl sm:text-4xl uppercase leading-none">{{ __('Passez de la théorie à la pratique') }}</h2>
                <p class="mt-3 text-[#cfcfcf]">{{ __('Stock, devis, bons de livraison et factures au même endroit. Créez votre espace en une minute, sans carte bancaire.') }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="ind-btn bg-[#ffcc00] text-[#111]">{{ __('Créer un compte gratuit') }}</a>
                    <a href="{{ route('demo.login') }}" class="ind-btn bg-white text-[#111]">{{ __('Tester la démo') }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Related --}}
    @if ($related->isNotEmpty())
        <section class="px-4 sm:px-6 pb-20">
            <div class="max-w-4xl mx-auto">
                <h2 class="ind-h text-2xl uppercase border-b-2 border-[#111] pb-3 mb-6">{{ __('À lire aussi') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @foreach ($related as $r)
                        <a href="{{ route('blog.show', $r->slug) }}" class="group block bg-white border-2 border-[#111] shadow-[5px_5px_0_#111] p-5 hover:bg-[#ffcc00]">
                            <span class="ind-mono text-[11px] uppercase text-[#666] group-hover:text-[#111]">{{ $r->category ?: 'Blog' }}</span>
                            <h3 class="mt-2 font-bold leading-snug">{{ $r->title }}</h3>
                            <span class="mt-3 inline-block ind-h uppercase tracking-wider text-sm">{{ __('Lire l’article') }} →</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
