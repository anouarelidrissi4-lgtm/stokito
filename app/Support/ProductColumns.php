<?php

namespace App\Support;

use App\Models\User;

/**
 * Columns of the products list (2026-11-25): each user picks which ones show and
 * in which order (saved in users.ui_prefs['products_columns']). Used by the list
 * and by the printable page.
 */
class ProductColumns
{
    /** key => [label, align, visible by default, permission or null] */
    public const ALL = [
        'ref' => ['Réf.', 'left', true, null],
        'name' => ['Désignation', 'left', true, null],
        'barcode' => ['Code-barres', 'left', false, null],
        'type' => ['Type', 'left', false, null],
        'categorie' => ['Catégorie', 'left', true, null],
        'marque' => ['Marque', 'left', true, null],
        'fournisseur' => ['Fournisseur', 'left', true, null],
        'unit' => ['Unité', 'left', false, null],
        'conditionnement' => ['Conditionnement', 'left', false, null],
        'poids' => ['Poids KG/ML', 'right', false, null],
        'achat_kg' => ['Prix achat KG', 'right', false, 'produits_prix_achat'],
        'achat' => ['Prix achat', 'right', true, 'produits_prix_achat'],
        'vente' => ['Prix vente TTC', 'right', true, null],
        'gros' => ['Prix vente gros', 'right', true, null],
        'tva' => ['TVA', 'right', false, null],
        'stock' => ['Stock', 'right', true, null],
        'seuil' => ["Seuil d'alerte", 'right', false, null],
    ];

    /** @return list<array{key:string,label:string,align:string,visible:bool}> in the user's order */
    public static function for(User $user): array
    {
        // the demo account is shared by every visitor: keep its choice in the session only
        $saved = $user->isDemo() ? session('products_columns') : ($user->ui_prefs['products_columns'] ?? null);

        return self::normalize(is_array($saved) ? $saved : [], $user);
    }

    /**
     * Turns a list of ['key' => .., 'visible' => ..] (saved or posted) into the full,
     * clean list: unknown keys dropped, forbidden columns removed, missing ones
     * appended with their default visibility.
     */
    public static function normalize(array $list, User $user): array
    {
        $out = [];
        foreach ($list as $c) {
            $key = is_array($c) ? ($c['key'] ?? null) : null;
            if (! is_string($key) || ! isset(self::ALL[$key]) || isset($out[$key]) || ! self::allowed($key, $user)) {
                continue;
            }
            $out[$key] = (bool) ($c['visible'] ?? true);
        }
        foreach (self::ALL as $key => [, , $default]) {
            if (! isset($out[$key]) && self::allowed($key, $user)) {
                $out[$key] = $default;
            }
        }
        // a list without its name column is useless
        $out['name'] = $out['name'] ?? true;
        if (! array_filter($out)) {
            $out['name'] = true;
        }

        $res = [];
        foreach ($out as $key => $visible) {
            $res[] = ['key' => $key, 'label' => self::ALL[$key][0], 'align' => self::ALL[$key][1], 'visible' => $visible];
        }

        return $res;
    }

    public static function save(User $user, array $list): array
    {
        $clean = self::normalize($list, $user);
        if ($user->isDemo()) {
            session(['products_columns' => array_map(fn ($c) => ['key' => $c['key'], 'visible' => $c['visible']], $clean)]);

            return $clean;
        }
        $prefs = $user->ui_prefs ?? [];
        $prefs['products_columns'] = array_map(fn ($c) => ['key' => $c['key'], 'visible' => $c['visible']], $clean);
        $user->forceFill(['ui_prefs' => $prefs])->save();

        return $clean;
    }

    private static function allowed(string $key, User $user): bool
    {
        $perm = self::ALL[$key][3];

        return $perm === null || $user->hasPermission($perm);
    }
}
