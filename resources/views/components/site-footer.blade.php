{{-- shared footer: landing page + blog (industrial style, 2026-11-23). Internal links: product anchors, latest articles, guides, legal. --}}
@php
    $wa = 'https://wa.me/212676035777';
    $home = url('/');
    $latest = \App\Models\BlogPost::published()->latest('published_at')->take(4)->get(['title', 'slug']);
@endphp
<footer class="bg-[#0b0b0b] text-[#9a9a9a] border-t-2 border-[#2a2a2a]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 grid sm:grid-cols-2 lg:grid-cols-5 gap-10 text-sm">
        <div>
            <a href="{{ $home }}" class="flex items-center gap-3 text-white"><span class="grid place-items-center w-9 h-9 bg-[#ffcc00] text-[#111]"><x-sp-icon name="cube" class="w-5 h-5" /></span><span class="ind-h text-2xl tracking-wide">STOKITO</span></a>
            <p class="mt-3">{{ __('Logiciel de gestion de stock, de devis et de facturation en ligne pour les PME.') }}</p>
            <div class="mt-4 ind-bc h-6 w-40 opacity-40"></div>
        </div>
        <div>
            <h3 class="ind-h text-lg uppercase tracking-wider text-white">{{ __('Produit') }}</h3>
            <ul class="mt-3 space-y-2">
                <li><a href="{{ route('seo.logiciel-gratuit') }}" class="hover:text-[#ffcc00]">{{ __('Logiciel de gestion de stock gratuit') }}</a></li>
                <li><a href="{{ $home }}#parcours" class="hover:text-[#ffcc00]">{{ __('Comment ça marche') }}</a></li>
                <li><a href="{{ $home }}#fonctionnalites" class="hover:text-[#ffcc00]">{{ __('Fonctionnalités') }}</a></li>
                <li><a href="{{ $home }}#ecrans" class="hover:text-[#ffcc00]">{{ __('Écrans') }}</a></li>
                <li><a href="{{ route('register') }}" class="hover:text-[#ffcc00]">{{ __('Créer un compte gratuit') }}</a></li>
                <li><a href="{{ route('demo.login') }}" class="hover:text-[#ffcc00]">{{ __('Tester la démo') }}</a></li>
            </ul>
        </div>
        <div class="lg:col-span-2">
            <h3 class="ind-h text-lg uppercase tracking-wider text-white">{{ __('Derniers articles') }}</h3>
            <ul class="mt-3 space-y-2">
                @foreach ($latest as $p)
                    <li><a href="{{ route('blog.show', $p->slug) }}" class="hover:text-[#ffcc00]">{{ $p->title }}</a></li>
                @endforeach
                <li><a href="{{ route('blog.index') }}" class="text-white hover:text-[#ffcc00] underline decoration-[#ffcc00] decoration-2 underline-offset-4">{{ __('Tous les articles du blog') }}</a></li>
            </ul>
        </div>
        <div>
            <h3 class="ind-h text-lg uppercase tracking-wider text-white">{{ __('Ressources') }}</h3>
            <ul class="mt-3 space-y-2">
                <li><a href="/docs/guide-stockpilot-fr.pdf" target="_blank" rel="noopener" class="hover:text-[#ffcc00]">{{ __('Guide en français (PDF)') }}</a></li>
                <li><a href="/docs/guide-stockpilot-ar.pdf" target="_blank" rel="noopener" class="hover:text-[#ffcc00]" dir="rtl">الدليل بالعربية (PDF)</a></li>
                <li><a href="/docs/guide-stockpilot-en.pdf" target="_blank" rel="noopener" class="hover:text-[#ffcc00]">User guide in English (PDF)</a></li>
                <li><a href="{{ $wa }}" target="_blank" rel="noopener" class="hover:text-[#ffcc00] ind-mono" dir="ltr">WhatsApp +212 676 035 777</a></li>
                <li><a href="{{ route('login') }}" class="hover:text-[#ffcc00]">{{ __('Se connecter') }}</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-[#2a2a2a] py-5 px-4 flex flex-wrap justify-center gap-x-6 gap-y-2 ind-mono text-xs">
        <span>&copy; {{ date('Y') }} STOKITO · {{ __('Tous droits réservés.') }}</span>
        <a href="{{ route('legal.terms') }}" class="hover:text-[#ffcc00]">{{ __('Conditions d’utilisation') }}</a>
        <a href="{{ route('legal.privacy') }}" class="hover:text-[#ffcc00]">{{ __('Politique de confidentialité') }}</a>
    </div>
</footer>
