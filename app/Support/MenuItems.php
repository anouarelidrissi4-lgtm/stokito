<?php

namespace App\Support;

use App\Models\Societe;
use App\Models\User;

/**
 * Canonical catalog of the customizable sidebar links + the default
 * section layout. The "Menu lateral" editor on the Societe page lets the
 * admin override label/order/section per item (and add/reorder/rename/
 * delete sections) without ever being able to point a link anywhere other
 * than one of these real, permission-gated routes.
 *
 * Superadmin-only links (Projets, Blog) and the "Profil" footer link are
 * intentionally NOT part of this catalog - they are platform-level, not
 * tenant-level, and stay hardcoded in the navigation view.
 */
class MenuItems
{
    public const DEFAULT_SECTIONS = [
        'tableau_de_bord' => ['label' => 'Tableau de bord', 'icon' => '🏠', 'order' => 10],
        'catalogue' => ['label' => 'Catalogue', 'icon' => '📦', 'order' => 20],
        'ventes' => ['label' => 'Ventes', 'icon' => '👥', 'order' => 30],
        'achats' => ['label' => 'Achats', 'icon' => '🚚', 'order' => 40],
        'tresorerie' => ['label' => 'Tresorerie', 'icon' => '💰', 'order' => 50],
        'administration' => ['label' => 'Administration', 'icon' => '⚙️', 'order' => 60],
    ];

    /**
     * key => [label, route, active (routeIs pattern, null = never highlighted),
     * permission (null = always visible once authed), module (Societe boolean
     * column that must also be true), admin_override (isAdmin() also grants
     * access regardless of permission), section, order]
     */
    public const ALL = [
        'dashboard' => ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'permission' => 'dashboard', 'module' => null, 'admin_override' => false, 'section' => 'tableau_de_bord', 'order' => 10],

        'products.index' => ['label' => 'Produits', 'route' => 'products.index', 'active' => 'products.index', 'permission' => 'produits_consulter', 'module' => null, 'admin_override' => false, 'section' => 'catalogue', 'order' => 10],
        'categories.index' => ['label' => 'Categories', 'route' => 'categories.index', 'active' => 'categories.index', 'permission' => 'gestion_categories', 'module' => null, 'admin_override' => false, 'section' => 'catalogue', 'order' => 20],
        'brands.index' => ['label' => 'Marques', 'route' => 'brands.index', 'active' => 'brands.index', 'permission' => 'gestion_marques', 'module' => null, 'admin_override' => false, 'section' => 'catalogue', 'order' => 30],

        'inventaire.index' => ['label' => 'Inventaire', 'route' => 'inventaire.index', 'active' => 'inventaire.*', 'permission' => 'inventaire', 'module' => null, 'admin_override' => false, 'section' => 'catalogue', 'order' => 40],

        'caisse.pos' => ['label' => 'Caisse', 'route' => 'caisse.pos', 'active' => 'caisse.*', 'permission' => 'caisse', 'module' => 'caisse_enabled', 'admin_override' => false, 'section' => 'ventes', 'order' => 5],
        'clients.index' => ['label' => 'Clients', 'route' => 'clients.index', 'active' => 'clients.index', 'permission' => 'gestion_clients', 'module' => null, 'admin_override' => false, 'section' => 'ventes', 'order' => 10],
        'devis.index' => ['label' => 'Devis Clients', 'route' => 'devis.index', 'active' => ['devis.index', 'devis.edit'], 'permission' => 'devis_consulter', 'module' => null, 'admin_override' => false, 'section' => 'ventes', 'order' => 20],
        'devis.create' => ['label' => 'Nouveau Devis', 'route' => 'devis.create', 'active' => 'devis.create', 'permission' => 'devis_creer', 'module' => null, 'admin_override' => false, 'section' => 'ventes', 'order' => 30],
        'bon-livraisons.index' => ['label' => 'Bons de Livraison', 'route' => 'bon-livraisons.index', 'active' => ['bon-livraisons.index', 'bon-livraisons.edit'], 'permission' => 'bl_consulter', 'module' => null, 'admin_override' => false, 'section' => 'ventes', 'order' => 40],
        'bon-livraisons.create' => ['label' => 'Nouveau Bon Livraison', 'route' => 'bon-livraisons.create', 'active' => 'bon-livraisons.create', 'permission' => 'bl_creer', 'module' => null, 'admin_override' => false, 'section' => 'ventes', 'order' => 50],
        'factures.index' => ['label' => 'Factures', 'route' => 'factures.index', 'active' => ['factures.index', 'factures.edit'], 'permission' => 'factures_consulter', 'module' => null, 'admin_override' => false, 'section' => 'ventes', 'order' => 60],
        'factures.create' => ['label' => 'Nouvelle Facture', 'route' => 'factures.create', 'active' => 'factures.create', 'permission' => 'factures_consulter', 'module' => null, 'admin_override' => false, 'section' => 'ventes', 'order' => 61],
        'bons-de-chargement.index' => ['label' => 'Bons de Chargement', 'route' => 'bons-de-chargement.index', 'active' => 'bons-de-chargement.*', 'permission' => 'bons_chargement', 'module' => 'bons_chargement_enabled', 'admin_override' => false, 'section' => 'ventes', 'order' => 70],
        'reglements-clients.index' => ['label' => 'Reglements Clients', 'route' => 'reglements-clients.index', 'active' => 'reglements-clients.*', 'permission' => 'gestion_reglements_clients', 'module' => null, 'admin_override' => false, 'section' => 'ventes', 'order' => 80],
        'avoirs-clients.index' => ['label' => 'Avoirs Clients', 'route' => 'avoirs-clients.index', 'active' => 'avoirs-clients.*', 'permission' => 'gestion_avoirs_clients', 'module' => null, 'admin_override' => false, 'section' => 'ventes', 'order' => 90],
        'soldes-clients.index' => ['label' => 'Soldes Clients', 'route' => 'soldes-clients.index', 'active' => 'soldes-clients.index', 'permission' => 'soldes_clients', 'module' => null, 'admin_override' => false, 'section' => 'ventes', 'order' => 100],
        'plafonds-clients.index' => ['label' => 'Plafonds Clients', 'route' => 'plafonds-clients.index', 'active' => 'plafonds-clients.index', 'permission' => 'plafonds_clients', 'module' => null, 'admin_override' => false, 'section' => 'ventes', 'order' => 110],

        'fournisseurs.index' => ['label' => 'Fournisseurs', 'route' => 'fournisseurs.index', 'active' => 'fournisseurs.index', 'permission' => 'gestion_fournisseurs', 'module' => null, 'admin_override' => false, 'section' => 'achats', 'order' => 10],
        'commande-fournisseurs.index' => ['label' => 'Bons de commande', 'route' => 'commande-fournisseurs.index', 'active' => ['commande-fournisseurs.index', 'commande-fournisseurs.edit'], 'permission' => 'gestion_commandes_fournisseurs', 'module' => null, 'admin_override' => false, 'section' => 'achats', 'order' => 20],
        'commande-fournisseurs.create' => ['label' => 'Nouveau bon de commande', 'route' => 'commande-fournisseurs.create', 'active' => 'commande-fournisseurs.create', 'permission' => 'gestion_commandes_fournisseurs', 'module' => null, 'admin_override' => false, 'section' => 'achats', 'order' => 30],
        'reglements-fournisseurs.index' => ['label' => 'Reglements Fournisseurs', 'route' => 'reglements-fournisseurs.index', 'active' => 'reglements-fournisseurs.*', 'permission' => 'gestion_reglements_fournisseurs', 'module' => null, 'admin_override' => false, 'section' => 'achats', 'order' => 40],
        'avoirs-fournisseurs.index' => ['label' => 'Avoirs Fournisseurs', 'route' => 'avoirs-fournisseurs.index', 'active' => 'avoirs-fournisseurs.*', 'permission' => 'gestion_avoirs_fournisseurs', 'module' => null, 'admin_override' => false, 'section' => 'achats', 'order' => 50],
        'soldes-fournisseurs.index' => ['label' => 'Soldes Fournisseurs', 'route' => 'soldes-fournisseurs.index', 'active' => 'soldes-fournisseurs.index', 'permission' => 'soldes_fournisseurs', 'module' => null, 'admin_override' => false, 'section' => 'achats', 'order' => 60],

        'tresorerie.comptes-bancaires' => ['label' => 'Comptes Bancaires', 'route' => 'tresorerie.comptes-bancaires', 'active' => 'tresorerie.comptes-bancaires', 'permission' => 'gestion_comptes_bancaires', 'module' => null, 'admin_override' => false, 'section' => 'tresorerie', 'order' => 10],
        'tresorerie.porte-feuilles' => ['label' => 'Porte-Feuilles', 'route' => 'tresorerie.porte-feuilles', 'active' => 'tresorerie.porte-feuilles', 'permission' => 'porte_feuilles', 'module' => null, 'admin_override' => false, 'section' => 'tresorerie', 'order' => 20],
        'cheques-fournisseurs.index' => ['label' => 'Cheques / Effets Fournisseurs', 'route' => 'cheques-fournisseurs.index', 'active' => 'cheques-fournisseurs.*', 'permission' => null, 'module' => null, 'admin_override' => false, 'section' => 'tresorerie', 'order' => 30],
        'tresorerie.mouvements-bancaires' => ['label' => 'Mouvements Bancaires', 'route' => 'tresorerie.mouvements-bancaires', 'active' => 'tresorerie.mouvements-bancaires', 'permission' => 'mouvements_bancaires', 'module' => null, 'admin_override' => false, 'section' => 'tresorerie', 'order' => 40],

        'statistiques.index' => ['label' => 'Statistiques', 'route' => 'statistiques.index', 'active' => 'statistiques.index', 'permission' => 'statistiques', 'module' => null, 'admin_override' => false, 'section' => 'administration', 'order' => 10],
        'documents-externes.index' => ['label' => 'Documents externes', 'route' => 'documents-externes.index', 'active' => 'documents-externes.index', 'permission' => 'documents_externes', 'module' => null, 'admin_override' => false, 'section' => 'administration', 'order' => 20],
        'rappels.index' => ['label' => 'Rappels', 'route' => 'rappels.index', 'active' => 'rappels.index', 'permission' => 'rappels', 'module' => null, 'admin_override' => false, 'section' => 'administration', 'order' => 30],
        'utilisateurs.index' => ['label' => 'Utilisateurs', 'route' => 'utilisateurs.index', 'active' => 'utilisateurs.index', 'permission' => 'gestion_utilisateurs', 'module' => null, 'admin_override' => true, 'section' => 'administration', 'order' => 40],
        'societe.edit' => ['label' => 'Ma Societe', 'route' => 'societe.edit', 'active' => 'societe.edit', 'permission' => 'gestion_societe', 'module' => null, 'admin_override' => true, 'emphasis' => true, 'section' => 'administration', 'order' => 50],
    ];

    /**
     * Merge the catalog defaults with the tenant's stored overrides
     * (Societe::menu_config) into the final structure to render, already
     * ordered. Permission/module visibility is NOT applied here - the
     * caller still filters per-item with the current user.
     *
     * An item whose resolved section is '' (empty string) is a "lien sans
     * section": the admin explicitly pulled it out of any section via the
     * Menu lateral editor, so it renders as a plain top-level link with no
     * section header above it.
     *
     * @param  array|null  $config  Societe::menu_config, shape:
     *     ['sections' => [key => ['label'=>.., 'order'=>.., 'icon'=>..]],
     *      'items' => [key => ['label'=>.., 'section'=>.., 'order'=>..]]]
     * @return array{unsectioned: array, sections: array<string, array{key:string, label:string, icon:string, order:int, items:array}>}
     */
    public static function resolve(?array $config): array
    {
        $sectionOverrides = $config['sections'] ?? [];
        $itemOverrides = $config['items'] ?? [];

        $sections = [];
        foreach (self::DEFAULT_SECTIONS as $key => $section) {
            $sections[$key] = array_merge($section, $sectionOverrides[$key] ?? []);
        }
        // Custom sections the admin added that aren't in the defaults.
        foreach ($sectionOverrides as $key => $section) {
            if (! isset($sections[$key])) {
                $sections[$key] = array_merge(['label' => $key, 'icon' => '📎', 'order' => 999], $section);
            }
        }

        foreach ($sections as $key => &$section) {
            $section['key'] = $key;
            $section['items'] = [];
        }
        unset($section);

        $unsectioned = [];

        foreach (self::ALL as $key => $item) {
            $override = $itemOverrides[$key] ?? [];
            $resolved = array_merge($item, $override);
            $resolved['key'] = $key;
            $sectionKey = $resolved['section'];

            if ($sectionKey === '' || $sectionKey === null) {
                $unsectioned[] = $resolved;

                continue;
            }

            if (! isset($sections[$sectionKey])) {
                // The item was moved to a section that no longer exists - fall back.
                $sectionKey = $item['section'];
            }

            $sections[$sectionKey]['items'][] = $resolved;
        }

        usort($unsectioned, fn ($a, $b) => $a['order'] <=> $b['order']);

        $sections = collect($sections)->sortBy('order')->all();
        foreach ($sections as &$section) {
            usort($section['items'], fn ($a, $b) => $a['order'] <=> $b['order']);
        }
        unset($section);

        return ['unsectioned' => $unsectioned, 'sections' => $sections];
    }

    /**
     * Whether the given item (as returned inside resolve()'s items arrays)
     * should be shown to this user, matching the exact gating logic that
     * used to be hardcoded per-link in navigation.blade.php.
     */
    public static function visibleFor(array $item, User $user, Societe $societe): bool
    {
        $allowed = $item['admin_override'] && $user->isAdmin();

        if (! $allowed) {
            $allowed = $item['permission'] === null || $user->hasPermission($item['permission']);
        }

        if ($allowed && $item['module'] && ! $societe->{$item['module']}) {
            $allowed = false;
        }

        return $allowed;
    }
}
