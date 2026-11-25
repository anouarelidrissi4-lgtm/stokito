<?php

namespace App\Support;

/**
 * Central catalog of the granular permission checkboxes shown on the
 * "Nouvel utilisateur" form. Keys are what gets stored in users.permissions;
 * labels are the French text shown in the UI.
 */
class Permissions
{
    public const ALL = [
        // --- Colonne 1 ---
        'bons_chargement' => 'Bons de chargement',
        'caisse' => 'Caisse : Encaisser',
        'copilot' => 'Copilot IA',
        'caisse_annuler' => 'Caisse : Annuler un ticket',
        'caisse_cloturer' => 'Caisse : Clôtures (Z)',
        'bl_consulter' => 'Bons Livraison : Consulter',
        'bl_creer' => 'Bons Livraison : Creer',
        'bl_edition' => 'Bons Livraison : Edition',
        'bl_supprimer' => 'Bons Livraison : Supprimer',
        'bl_facturer' => 'Bons Livraisons : Facturer',
        'dashboard' => 'Dashboard',
        'devis_consulter' => 'Devis : Consulter',
        'devis_creer' => 'Devis : Creer',
        'devis_edition' => 'Devis : Edition',
        'devis_supprimer' => 'Devis : Supprimer',
        'devis_valider' => 'Devis : Valider',
        'depenses' => 'Dépenses',
        'documents_externes' => 'Documents externes',
        'factures_consulter' => 'Factures : Consulter',

        // --- Colonne 2 ---
        'gestion_societe' => 'Gestion de la societe',
        'gestion_avoirs_clients' => 'Gestion des avoirs clients',
        'gestion_avoirs_fournisseurs' => 'Gestion des avoirs fournisseurs',
        'gestion_categories' => 'Gestion des categories',
        'gestion_clients' => 'Gestion des clients',
        'gestion_commandes_fournisseurs' => 'Gestion des commandes fournisseurs',
        'gestion_comptes_bancaires' => 'Gestion des comptes bancaires',
        'gestion_fournisseurs' => 'Gestion des fournisseurs',
        'gestion_marques' => 'Gestion des marques',
        'gestion_reglements_clients' => 'Gestion des reglements clients',
        'gestion_reglements_fournisseurs' => 'Gestion des reglements fournisseurs',
        'gestion_utilisateurs' => 'Gestion des utilisateurs',
        'inventaire' => 'Inventaire',
        'mouvements_bancaires' => 'Mouvements Bancaires',

        // --- Colonne 3 ---
        'plafonds_clients' => 'Plafonds Clients',
        'porte_feuilles' => 'Porte-Feuilles',
        'produits_consulter' => 'Produits : Consulter',
        'produits_creer' => 'Produits : Creer',
        'produits_edition' => 'Produits : Edition',
        'produits_supprimer' => 'Produits : Supprimer',
        'produits_prix_achat' => "Produits : Voir Prix d'achat",
        'rappels' => 'Rappels',
        'soldes_fournisseurs' => 'Soldes Fournisseurs',
        'soldes_clients' => "Voir l'etat des soldes clients",
        'dossier_client' => 'Voir le dossier client',
        'dossier_fournisseur' => 'Voir le dossier fournisseur',
        'statistiques' => 'Voir les statistiques',
        'marges' => 'Voir marges',
    ];

    /**
     * @return array<int, array<string, string>> 3 columns of [key => label]
     */
    public static function columns(): array
    {
        return array_chunk(self::ALL, (int) ceil(count(self::ALL) / 3), true);
    }

    public static function keys(): array
    {
        return array_keys(self::ALL);
    }
}
