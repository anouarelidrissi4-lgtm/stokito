<?php

namespace App\Support;

/**
 * VAT totals for a sales document whose lines may carry different VAT rates (2026-11-24).
 * The document-level discount is spread over the rates in proportion to each rate's share
 * of the net amount, and the VAT is rounded once per rate (one line per rate on the invoice).
 */
class DocTotals
{
    /**
     * @param  array<int, array{0: float, 1: float}>  $lines  [net amount HT after the line's own discount, VAT rate in %]
     * @param  float  $globalRemise  document-level discount amount, applied after the lines' discounts
     * @return array{ht: float, tva: float, ttc: float, groups: array<int, array{rate: float, base: float, tva: float}>}
     */
    public static function compute(array $lines, float $globalRemise): array
    {
        $sum = 0.0;
        $byRate = [];
        foreach ($lines as [$net, $rate]) {
            $sum += $net;
            $k = self::key($rate);
            $byRate[$k] = ($byRate[$k] ?? 0) + $net;
        }

        $ht = $sum - $globalRemise;
        $factor = $sum > 0 ? $ht / $sum : 0;
        krsort($byRate, SORT_NUMERIC);

        $groups = [];
        $tva = 0.0;
        foreach ($byRate as $k => $base) {
            $b = round($base * $factor, 2);
            $t = round($b * (float) $k / 100, 2);
            $groups[] = ['rate' => (float) $k, 'base' => $b, 'tva' => $t];
            $tva += $t;
        }

        return ['ht' => round($ht, 2), 'tva' => round($tva, 2), 'ttc' => round($ht + $tva, 2), 'groups' => $groups];
    }

    /** "5.5", "20", "0" — used as the grouping key. */
    public static function key($rate): string
    {
        return rtrim(rtrim(number_format((float) $rate, 2, '.', ''), '0'), '.');
    }

    /** "5,5" — French display. */
    public static function label($rate): string
    {
        return str_replace('.', ',', self::key($rate));
    }
}
