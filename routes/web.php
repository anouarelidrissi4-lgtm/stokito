<?php

use App\Models\BonLivraison;
use App\Models\CommandeFournisseur;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('blog', \App\Livewire\Blog\Index::class)->name('blog.index');
Route::get('blog/{slug}', \App\Livewire\Blog\Show::class)->name('blog.show');

Route::view('conditions-utilisation', 'legal.terms')->name('legal.terms');
Route::view('confidentialite', 'legal.privacy')->name('legal.privacy');

// SEO pillar page (FR), 2026-11-23
Route::view('logiciel-gestion-de-stock-gratuit', 'seo.logiciel-gratuit')->name('seo.logiciel-gratuit');
Route::view('logiciel-devis-facture-gratuit', 'seo.logiciel-devis-facture')->name('seo.logiciel-devis-facture');

Route::get('sitemap.xml', function () {
    $urls = collect([
        ['loc' => url('/'), 'lastmod' => now()->format('Y-m-d'), 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['loc' => route('seo.logiciel-gratuit'), 'lastmod' => '2026-11-23', 'changefreq' => 'monthly', 'priority' => '0.9'],
        ['loc' => route('seo.logiciel-devis-facture'), 'lastmod' => '2026-11-23', 'changefreq' => 'monthly', 'priority' => '0.9'],
        ['loc' => route('blog.index'), 'lastmod' => now()->format('Y-m-d'), 'changefreq' => 'daily', 'priority' => '0.8'],
    ]);

    $posts = \App\Models\BlogPost::published()->get();
    foreach ($posts as $post) {
        $urls->push([
            'loc' => route('blog.show', $post->slug),
            'lastmod' => $post->updated_at->format('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.6',
        ]);
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($urls as $entry) {
        $xml .= '    <url>'."\n";
        $xml .= '        <loc>'.e($entry['loc']).'</loc>'."\n";
        $xml .= '        <lastmod>'.e($entry['lastmod']).'</lastmod>'."\n";
        $xml .= '        <changefreq>'.e($entry['changefreq']).'</changefreq>'."\n";
        $xml .= '        <priority>'.e($entry['priority']).'</priority>'."\n";
        $xml .= '    </url>'."\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('locale/{locale}', function (string $locale) {
    if (in_array($locale, \App\Http\Middleware\SetLocale::SUPPORTED)) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('locale.switch');

// One-click free trial: logs the visitor into the sandboxed demo account.
// The demo account only ever sees/creates data flagged is_demo = true
// (see App\Models\Concerns\ScopedToDemoMode), so nothing here can touch
// or expose the real company's data.
Route::get('demo', function () {
    $demo = \App\Models\User::where('email', 'demo@stockpilot.cloud')->where('is_demo', true)->first();

    if (! $demo) {
        return redirect('/')->with('error', 'Demo indisponible pour le moment.');
    }

    auth()->login($demo);
    request()->session()->regenerate();

    return redirect()->route('dashboard');
})->middleware('guest')->name('demo.login');

Route::get('dashboard', function () {
    // Only a "pure" superadmin with no project of their own lands on Projets;
    // a superadmin who is also a project admin keeps their normal dashboard.
    if (auth()->user()->isSuperAdmin() && ! auth()->user()->project_id) {
        return redirect()->route('projects.index');
    }

    // A non-admin staff member without the "Dashboard" permission is sent to
    // the first module they do have access to, instead of a 403 right after login.
    if (! auth()->user()->hasPermission('dashboard')) {
        $fallbacks = [
            'devis_consulter' => 'devis.index',
            'bl_consulter' => 'bon-livraisons.index',
            'factures_consulter' => 'factures.index',
            'produits_consulter' => 'products.index',
            'gestion_clients' => 'clients.index',
            'gestion_fournisseurs' => 'fournisseurs.index',
            'gestion_commandes_fournisseurs' => 'commande-fournisseurs.index',
        ];

        foreach ($fallbacks as $permission => $routeName) {
            if (auth()->user()->hasPermission($permission)) {
                return redirect()->route($routeName);
            }
        }

        abort(403, "Vous n'avez acces a aucune page. Contactez votre administrateur.");
    }

    // A product with its own threshold always uses it; a product with no
    // threshold falls back to the company-wide default from "Ma Societe"
    // (when that default is set above 0).
    $defaultThreshold = (int) (\App\Models\Societe::current()->stock_threshold_default ?? 0);

    $lowStock = Product::query()
        ->where(function ($q) use ($defaultThreshold) {
            $q->whereNotNull('stock_alert_threshold')
                ->whereColumn('stock_qty', '<=', 'stock_alert_threshold');

            if ($defaultThreshold > 0) {
                $q->orWhere(function ($q2) use ($defaultThreshold) {
                    $q2->whereNull('stock_alert_threshold')
                        ->where('stock_qty', '<=', $defaultThreshold);
                });
            }
        })
        ->orderBy('stock_qty')
        ->get();

    // "Pilotage" dashboard (2026-11-22): this month vs last month, cash in,
    // unpaid invoices, low stock and a 6-month sales chart (factures TTC).
    $monthStart = now()->startOfMonth();
    $prevStart = now()->subMonthNoOverflow()->startOfMonth();
    $caMois = (float) \App\Models\Facture::where('date', '>=', $monthStart)->sum('total_ttc');
    $caPrev = (float) \App\Models\Facture::whereBetween('date', [$prevStart, $monthStart->copy()->subDay()])->sum('total_ttc');
    $impayes = \App\Models\Facture::with('client')
        ->where('status', '!=', 'payee')
        ->whereRaw('total_ttc - montant_paye > 0.009')
        ->orderByRaw('COALESCE(due_date, date)')
        ->get();
    $months = collect(range(5, 0))->map(function ($i) {
        $start = now()->subMonthsNoOverflow($i)->startOfMonth();

        return [
            'label' => ucfirst($start->locale(app()->getLocale())->isoFormat('MMM')),
            'total' => (float) \App\Models\Facture::whereBetween('date', [$start, $start->copy()->endOfMonth()])->sum('total_ttc'),
        ];
    });

    return view('dashboard', [
        'caMois' => $caMois,
        'caEvolution' => $caPrev > 0 ? round(($caMois - $caPrev) / $caPrev * 100) : null,
        'encaisseMois' => (float) \App\Models\ReglementClient::where('date', '>=', $monthStart)->sum('montant'),
        'impayes' => $impayes,
        'totalImpayes' => $impayes->sum(fn ($f) => $f->total_ttc - $f->montant_paye),
        'months' => $months,
        'achatsMois' => (float) CommandeFournisseur::where('date', '>=', $monthStart)->sum('total'),
        'lowStock' => $lowStock,
        'devisEnCours' => \App\Models\Devis::whereIn('status', ['draft', 'sent'])->count(),
        'currency' => \App\Models\Societe::current()->currency ?: 'MAD',
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('logout', function () {
    (new \App\Livewire\Actions\Logout)();
    return redirect('/');
})->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('categories', \App\Livewire\Categories\Index::class)->name('categories.index');
    Route::get('inventaire', \App\Livewire\Inventaire\Index::class)->name('inventaire.index');
    Route::get('inventaire/nouveau', \App\Livewire\Inventaire\Form::class)->name('inventaire.create');
    Route::get('inventaire/{inventaireId}', \App\Livewire\Inventaire\Form::class)->whereNumber('inventaireId')->name('inventaire.show');
    Route::get('products', \App\Livewire\Products\Index::class)->name('products.index');
    Route::get('brands', \App\Livewire\Brands\Index::class)->name('brands.index');
    Route::get('clients', \App\Livewire\Clients\Index::class)->name('clients.index');
    Route::get('fournisseurs', \App\Livewire\Fournisseurs\Index::class)->name('fournisseurs.index');

    Route::get('devis', \App\Livewire\Devis\Index::class)->name('devis.index');
    Route::get('devis/create', \App\Livewire\Devis\Form::class)->name('devis.create');
    Route::get('devis/{devisId}/edit', \App\Livewire\Devis\Form::class)->name('devis.edit');
    Route::get('devis/{devisId}/pdf', function ($devisId) {
        abort_unless(auth()->user()->hasPermission('devis_consulter'), 403);
        $devis = \App\Models\Devis::with(['client', 'items.product'])->findOrFail($devisId);
        $societe = \App\Models\Societe::current();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.document', [
            'societe' => $societe,
            'title' => 'Devis',
            'numero' => $devis->numero,
            'date' => $devis->date->format('d/m/Y'),
            'dueDate' => null,
            'validUntil' => optional($devis->valid_until)->format('d/m/Y'),
            'status' => $devis->status,
            'partyLabel' => 'Client',
            'party' => $devis->client,
            'items' => $devis->items->map(fn ($i) => [
                'name' => $i->product->name ?? '-',
                'unit' => $i->product->unit ?? '',
                'quantity' => $i->quantity,
                'unit_price' => $i->unit_price,
                'tva_rate' => $i->tva_rate ?? $devis->tva_rate,
                'total' => $i->total,
            ]),
            'showPrices' => true,
            'totals' => [
                'brut' => $devis->montantBrutHt(),
                'remise' => $devis->montantRemise(),
                'ht' => $devis->total_ht,
                'tva_rate' => $devis->tva_rate,
                'groups' => $devis->vatTotals()['groups'],
                'ttc' => $devis->total_ttc,
            ],
            'notes' => $devis->notes,
        ]);

        return $pdf->download($devis->numero.'.pdf');
    })->name('devis.pdf');

    Route::get('bon-livraisons', \App\Livewire\BonLivraisons\Index::class)->name('bon-livraisons.index');
    Route::get('bon-livraisons/create', \App\Livewire\BonLivraisons\Form::class)->name('bon-livraisons.create');
    Route::get('bon-livraisons/{bonLivraisonId}/edit', \App\Livewire\BonLivraisons\Form::class)->name('bon-livraisons.edit');
    Route::get('bon-livraisons/{bonLivraisonId}/pdf', function ($bonLivraisonId) {
        abort_unless(auth()->user()->hasPermission('bl_consulter'), 403);
        $bl = \App\Models\BonLivraison::with(['client', 'items.product'])->findOrFail($bonLivraisonId);
        $societe = \App\Models\Societe::current();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.document', [
            'societe' => $societe,
            'title' => 'Bon de Livraison',
            'numero' => $bl->numero,
            'date' => $bl->date->format('d/m/Y'),
            'dueDate' => null,
            'status' => $bl->status,
            'partyLabel' => 'Client',
            'party' => $bl->client,
            'items' => $bl->items->map(fn ($i) => [
                'name' => ($i->product->name ?? '-').($societe->show_ref_bl && ($i->product->reference ?? null) ? ' ('.$i->product->reference.')' : ''),
                'unit' => $i->product->unit ?? '',
                'quantity' => $i->quantity,
                'unit_price' => $i->unit_price,
                'tva_rate' => $i->tva_rate ?? $bl->tva_rate,
                'total' => $i->unit_price !== null ? $i->quantity * $i->unit_price : null,
            ]),
            'showPrices' => $bl->show_prices,
            'totals' => $bl->show_prices ? [
                'brut' => $bl->montantBrutHt(),
                'remise' => $bl->montantRemise(),
                'ht' => $bl->total_ht,
                'tva_rate' => $bl->tva_rate,
                'groups' => $bl->vatTotals()['groups'],
                'ttc' => $bl->total,
            ] : null,
            'notes' => null,
        ]);

        return $pdf->download($bl->numero.'.pdf');
    })->name('bon-livraisons.pdf');

    Route::get('commande-fournisseurs', \App\Livewire\CommandeFournisseurs\Index::class)->name('commande-fournisseurs.index');
    Route::get('commande-fournisseurs/create', \App\Livewire\CommandeFournisseurs\Form::class)->name('commande-fournisseurs.create');
    Route::get('commande-fournisseurs/{commandeId}/edit', \App\Livewire\CommandeFournisseurs\Form::class)->name('commande-fournisseurs.edit');
    Route::get('commande-fournisseurs/{commandeId}/pdf', function ($commandeId) {
        abort_unless(auth()->user()->hasPermission('gestion_commandes_fournisseurs'), 403);
        $commande = \App\Models\CommandeFournisseur::with(['fournisseur', 'items.product'])->findOrFail($commandeId);
        $societe = \App\Models\Societe::current();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.document', [
            'societe' => $societe,
            'title' => 'Commande Fournisseur',
            'numero' => $commande->numero,
            'date' => $commande->date->format('d/m/Y'),
            'dueDate' => null,
            'status' => $commande->status,
            'partyLabel' => 'Fournisseur',
            'party' => $commande->fournisseur,
            'items' => $commande->items->map(fn ($i) => [
                'name' => $i->product->name ?? '-',
                'unit' => $i->product->unit ?? '',
                'quantity' => $i->quantity,
                'unit_price' => $i->unit_price,
                'total' => $i->total,
            ]),
            'showPrices' => true,
            'totals' => [
                'brut' => $commande->montantBrutHt(),
                'remise' => $commande->montantRemise(),
                'ht' => $commande->total_ht,
                'tva_rate' => $commande->tva_rate,
                'ttc' => $commande->total,
            ],
            'notes' => null,
        ]);

        return $pdf->download($commande->numero.'.pdf');
    })->name('commande-fournisseurs.pdf');

    Route::get('tresorerie/comptes-bancaires', \App\Livewire\TresorerieComptes\Index::class)->name('tresorerie.comptes-bancaires')->defaults('type', 'banque');
    Route::get('tresorerie/porte-feuilles', \App\Livewire\TresorerieComptes\Index::class)->name('tresorerie.porte-feuilles')->defaults('type', 'caisse');

    Route::get('factures', \App\Livewire\Factures\Index::class)->name('factures.index');
    Route::get('factures/create/{blId?}', \App\Livewire\Factures\Form::class)->name('factures.create');
    Route::get('factures/{factureId}/edit', \App\Livewire\Factures\Form::class)->name('factures.edit');
    Route::get('factures/{factureId}/pdf', function ($factureId) {
        abort_unless(auth()->user()->hasPermission('factures_consulter'), 403);
        $facture = \App\Models\Facture::with(['client', 'items.product'])->findOrFail($factureId);
        $societe = \App\Models\Societe::current();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.document', [
            'societe' => $societe,
            'title' => 'Facture',
            'numero' => $facture->numero,
            'date' => $facture->date->format('d/m/Y'),
            'dueDate' => optional($facture->due_date)->format('d/m/Y'),
            'status' => str_replace('_', ' ', $facture->status),
            'showHeader' => $societe->facture_type !== 'sans_entete',
            'partyLabel' => 'Client',
            'party' => $facture->client,
            'items' => $facture->items->map(fn ($i) => [
                'name' => ($i->product->name ?? '-').($societe->show_ref_facture && ($i->product->reference ?? null) ? ' ('.$i->product->reference.')' : ''),
                'unit' => $i->product->unit ?? '',
                'quantity' => $i->quantity,
                'unit_price' => $i->unit_price,
                'tva_rate' => $i->tva_rate ?? $facture->tva_rate,
                'total' => $i->total,
            ]),
            'showPrices' => true,
            'totals' => [
                'brut' => $facture->montantBrutHt(),
                'remise' => $facture->montantRemise(),
                'ht' => $facture->total_ht,
                'tva_rate' => $facture->tva_rate,
                'groups' => $facture->vatTotals()['groups'],
                'ttc' => $facture->total_ttc,
            ],
            'notes' => $facture->notes,
            'facture' => $facture,
            'devisRef' => $facture->devis?->numero,
        ]);

        return $pdf->download($facture->numero.'.pdf');
    })->name('factures.pdf');

    Route::get('reglements-clients', \App\Livewire\ReglementsClients\Index::class)->name('reglements-clients.index');
    Route::get('reglements-clients/create/{factureId?}', \App\Livewire\ReglementsClients\Index::class)->name('reglements-clients.create');

    Route::get('avoirs-clients', \App\Livewire\AvoirsClients\Index::class)->name('avoirs-clients.index');

    Route::get('reglements-fournisseurs', \App\Livewire\ReglementsFournisseurs\Index::class)->name('reglements-fournisseurs.index');
    Route::get('reglements-fournisseurs/create/{commandeId?}', \App\Livewire\ReglementsFournisseurs\Index::class)->name('reglements-fournisseurs.create');

    Route::get('avoirs-fournisseurs', \App\Livewire\AvoirsFournisseurs\Index::class)->name('avoirs-fournisseurs.index');

    Route::get('cheques-fournisseurs', \App\Livewire\ChequesFournisseurs\Index::class)->name('cheques-fournisseurs.index');

    Route::get('tresorerie/mouvements-bancaires', \App\Livewire\MouvementsBancaires\Index::class)->name('tresorerie.mouvements-bancaires');

    Route::get('soldes-clients', \App\Livewire\ClientReports\Index::class)->name('soldes-clients.index')->defaults('mode', 'soldes');
    Route::get('plafonds-clients', \App\Livewire\ClientReports\Index::class)->name('plafonds-clients.index')->defaults('mode', 'plafonds');
    Route::get('dossier-clients/{clientId}', \App\Livewire\DossierClients\Show::class)->name('dossier-clients.show');

    Route::get('soldes-fournisseurs', \App\Livewire\FournisseurReports\Index::class)->name('soldes-fournisseurs.index');
    Route::get('dossier-fournisseurs/{fournisseurId}', \App\Livewire\DossierFournisseurs\Show::class)->name('dossier-fournisseurs.show');

    Route::get('bons-de-chargement', \App\Livewire\BonsDeChargement\Index::class)->name('bons-de-chargement.index');

    Route::get('rappels', \App\Livewire\Rappels\Index::class)->name('rappels.index');
    Route::get('documents-externes', \App\Livewire\DocumentsExternes\Index::class)->name('documents-externes.index');
    Route::get('utilisateurs', \App\Livewire\Utilisateurs\Index::class)->name('utilisateurs.index');
    Route::get('utilisateurs/create', \App\Livewire\Utilisateurs\Form::class)->name('utilisateurs.create');
    Route::get('utilisateurs/{userId}/edit', \App\Livewire\Utilisateurs\Form::class)->name('utilisateurs.edit');
    Route::get('projets', \App\Livewire\Projects\Index::class)->name('projects.index');

    Route::get('blog-admin', \App\Livewire\BlogAdmin\Index::class)->name('blog-admin.index');
    Route::get('blog-admin/create', \App\Livewire\BlogAdmin\Form::class)->name('blog-admin.create');
    Route::get('blog-admin/{postId}/edit', \App\Livewire\BlogAdmin\Form::class)->name('blog-admin.edit');
    Route::get('ma-societe', \App\Livewire\Societe\Edit::class)->name('societe.edit');

    Route::get('statistiques', function () {
        abort_unless(auth()->user()->hasPermission('statistiques'), 403);

        $comptes = \App\Models\TresorerieCompte::all();

        $factures = \App\Models\Facture::whereIn('status', ['envoyee', 'partiellement_payee', 'en_retard'])->get();

        $topClients = \App\Models\Client::all()->map(function ($client) {
            $client->solde_calcule = $client->solde();

            return $client;
        })->sortByDesc('solde_calcule')->take(5)->values();

        $topProduits = \App\Models\FactureItem::selectRaw('product_id, SUM(quantity) as qte_vendue')
            ->groupBy('product_id')
            ->orderByDesc('qte_vendue')
            ->with('product')
            ->take(5)
            ->get()
            ->map(function ($row) {
                return (object) [
                    'name' => $row->product->name ?? '-',
                    'qte_vendue' => $row->qte_vendue,
                ];
            });

        return view('statistiques', [
            'caEncaisse' => \App\Models\ReglementClient::sum('montant'),
            'creancesEnCours' => $factures->sum(fn ($f) => $f->resteAPayer()),
            'totalAchats' => \App\Models\CommandeFournisseur::sum('total'),
            'soldeTresorerie' => $comptes->sum(fn ($c) => $c->currentBalance()),
            'topClients' => $topClients,
            'topProduits' => $topProduits,
            'chequesEnRetard' => \App\Models\ChequeEffetFournisseur::with('fournisseur')
                ->where('status', 'en_attente')
                ->where('date_echeance', '<', now())
                ->get(),
        ]);
    })->name('statistiques.index');
});

require __DIR__.'/auth.php';
