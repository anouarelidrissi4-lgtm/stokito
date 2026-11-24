<?php

namespace App\Support;

use App\Models\Societe;

class Fmt
{
    /** No-break space between thousands: '3 129,62' stays one number on Arabic (RTL) pages and never wraps (2026-11-24). */
    public const THOUSANDS = "\u{00A0}";

    /**
     * Format a quantity value using the company's configured qty_decimals
     * (falls back to 2 if not set).
     */
    public static function qty(int|float|string|null $value): string
    {
        $decimals = Societe::current()->qty_decimals ?? 2;

        return number_format((float) $value, $decimals, ',', self::THOUSANDS);
    }

    /**
     * Format a money/amount value using the company's configured price_decimals
     * (falls back to 2 if not set).
     */
    /** Human label for a document status stored as a code (draft, payee, en_retard...). */
    public static function status(?string $value): string
    {
        $labels = [
            'draft' => 'Brouillon', 'sent' => 'Envoyé', 'accepted' => 'Accepté', 'rejected' => 'Refusé',
            'delivered' => 'Livré', 'ordered' => 'Commandé', 'received' => 'Reçu',
            'brouillon' => 'Brouillon', 'envoyee' => 'Envoyée', 'partiellement_payee' => 'Partiellement payée',
            'payee' => 'Payée', 'en_retard' => 'En retard',
            'en_attente' => 'En attente', 'encaisse' => 'Encaissé', 'rejete' => 'Rejeté',
            'preparation' => 'En préparation', 'charge' => 'Chargé', 'livre' => 'Livré',
            'active' => 'Actif', 'inactive' => 'Inactif', 'published' => 'Publié',
        ];
        $label = $labels[$value] ?? str_replace('_', ' ', (string) $value);

        return __($label);
    }

    public static function money(int|float|string|null $value): string
    {
        $decimals = Societe::current()->price_decimals ?? 2;

        return number_format((float) $value, $decimals, ',', self::THOUSANDS);
    }
}
