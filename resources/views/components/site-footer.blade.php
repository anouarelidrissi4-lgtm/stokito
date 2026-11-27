{{-- shared footer: landing page + blog (industrial style, 2026-11-23). Internal links: product anchors, latest articles, guides, legal. --}}
@php
    $wa = 'https://wa.me/212676035777';
    $home = url('/');
    $latest = \App\Models\BlogPost::published()->latest('published_at')->take(4)->get(['title', 'slug']);
@endphp
<footer class="bg-[#0b0b0b] text-zinc-400 border-t border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 grid sm:grid-cols-2 lg:grid-cols-5 gap-10 text-sm">
        <div>
            <a href="{{ $home }}" class="flex items-center gap-3 text-white"><span class="grid place-items-center w-9 h-9 rounded-lg bg-[#ffcc00] text-zinc-900"><x-sp-icon name="cube" class="w-5 h-5" /></span><span class="ind-h text-lg">Stokito</span></a>
            <p class="mt-3">{{ __('Logiciel de gestion de stock, de devis et de facturation en ligne pour les PME.') }}</p>
        </div>
        <div>
            <h3 class="ind-h text-lg text-white">{{ __('Produit') }}</h3>
            <ul class="mt-3 space-y-2">
                <li><a href="{{ route('seo.logiciel-gratuit') }}" class="hover:text-white">{{ __('Logiciel de gestion de stock gratuit') }}</a></li>
                <li><a href="{{ route('seo.logiciel-devis-facture') }}" class="hover:text-white">{{ __('Logiciel devis facture gratuit') }}</a></li>
                <li><a href="{{ route('seo.scanner-facture') }}" class="hover:text-white">{{ __('Scanner de factures fournisseurs') }}</a></li>
                <li><a href="{{ route('seo.caisse') }}" class="hover:text-white">{{ __('Logiciel de caisse gratuit') }}</a></li>
                <li><a href="{{ route('seo.bon-de-commande') }}" class="hover:text-white">{{ __('Modèle de bon de commande') }}</a></li>
                <li><a href="{{ route('seo.code-barre') }}" class="hover:text-white">{{ __('Générateur de code barre gratuit') }}</a></li>
                <li><a href="{{ route('seo.code-barre-pays') }}" class="hover:text-white">{{ __('Code barre pays') }}</a></li>
                <li><a href="{{ route('seo.scanner-code-barre') }}" class="hover:text-white">{{ __('Scanner code barre en ligne') }}</a></li>
                <li><a href="{{ route('seo.etiquette-code-barre') }}" class="hover:text-white">{{ __('Étiquette code barre') }}</a></li>
                <li><a href="{{ route('seo.stock-excel') }}" class="hover:text-white">{{ __('Gestion de stock Excel') }}</a></li>
                <li><a href="{{ route('seo.factur-x') }}" class="hover:text-white">{{ __('Facture électronique Factur-X') }}</a></li>
                <li><a href="{{ $home }}#parcours" class="hover:text-white">{{ __('Comment ça marche') }}</a></li>
                <li><a href="{{ $home }}#fonctionnalites" class="hover:text-white">{{ __('Fonctionnalités') }}</a></li>
                <li><a href="{{ $home }}#ecrans" class="hover:text-white">{{ __('Écrans') }}</a></li>
                <li><a href="{{ route('register') }}" class="hover:text-white">{{ __('Créer un compte gratuit') }}</a></li>
                <li><a href="{{ route('demo.login') }}" class="hover:text-white">{{ __('Tester la démo') }}</a></li>
            </ul>
        </div>
        <div class="lg:col-span-2">
            <h3 class="ind-h text-lg text-white">{{ __('Derniers articles') }}</h3>
            <ul class="mt-3 space-y-2">
                @foreach ($latest as $p)
                    <li><a href="{{ route('blog.show', $p->slug) }}" class="hover:text-white">{{ $p->title }}</a></li>
                @endforeach
                <li><a href="{{ route('blog.index') }}" class="text-white hover:text-white underline decoration-amber-400 decoration-2 underline-offset-4">{{ __('Tous les articles du blog') }}</a></li>
            </ul>
        </div>
        <div>
            <h3 class="ind-h text-lg text-white">{{ __('Ressources') }}</h3>
            <ul class="mt-3 space-y-2">
                <li><a href="/docs/guide-stokito-fr.pdf" target="_blank" rel="noopener" class="hover:text-white">{{ __('Guide en français (PDF)') }}</a></li>
                <li><a href="/docs/guide-stokito-ar.pdf" target="_blank" rel="noopener" class="hover:text-white" dir="rtl">الدليل بالعربية (PDF)</a></li>
                <li><a href="/docs/guide-stokito-en.pdf" target="_blank" rel="noopener" class="hover:text-white">User guide in English (PDF)</a></li>
                <li><a href="{{ $wa }}" target="_blank" rel="noopener" class="hover:text-white ind-mono" dir="ltr">WhatsApp +212 676 035 777</a></li>
                <li><a href="{{ route('login') }}" class="hover:text-white">{{ __('Se connecter') }}</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-zinc-800 py-5 px-4 flex flex-wrap justify-center gap-x-6 gap-y-2 ind-mono text-xs">
        <span>&copy; {{ date('Y') }} Stokito · {{ __('Tous droits réservés.') }}</span>
        <a href="{{ route('legal.terms') }}" class="hover:text-white">{{ __('Conditions d’utilisation') }}</a>
        <a href="{{ route('legal.privacy') }}" class="hover:text-white">{{ __('Politique de confidentialité') }}</a>
        <a href="{{ route('legal.mentions') }}" class="hover:text-white">{{ __('Mentions légales') }}</a>
        <button type="button" onclick="window.stkConsent && stkConsent.open()" class="hover:text-white">{{ __('Gérer les cookies') }}</button>
    </div>
</footer>
