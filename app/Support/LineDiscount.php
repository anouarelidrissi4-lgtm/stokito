<?php

namespace App\Support;

/** Réduction d'une ligne de document : en % ou en montant (2026-11-27). Une seule règle pour BL, devis et factures. */
class LineDiscount
{
    public static function amount(float $brut, string $type, float $value): float
    {
        if ($value <= 0 || $brut <= 0) {
            return 0.0;
        }

        return $type === 'fixed' ? min(round($value, 2), $brut) : round($brut * min($value, 100) / 100, 2);
    }

    /** "10%" / "2,5" / "" — text of the single "0 ou 0%" field. */
    public static function text(string $type, float $value): string
    {
        if ($value <= 0) {
            return '';
        }
        $n = rtrim(rtrim(number_format($value, 2, ',', ''), '0'), ',');

        return $type === 'fixed' ? $n : $n.'%';
    }

    /** "10%" → ['percent', 10]; "2,5" → ['fixed', 2.5]; "" → ['percent', 0]. */
    public static function parse(?string $text): array
    {
        $t = str_replace([' ', ','], ['', '.'], trim((string) $text));
        $percent = str_ends_with($t, '%');
        $v = max(0, (float) rtrim($t, '%'));

        return [$percent || $v == 0 ? 'percent' : 'fixed', $v];
    }
}
