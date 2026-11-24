<?php

namespace App\Support;

use App\Models\Societe;

/**
 * Country presets (2026-11-23, francophone markets first). A company picks its country at
 * sign-up; currency, default VAT, decimals and the names of the legal identifiers follow.
 * Identifier columns stay the same in the database (ice, identifiant_fiscal, registre_commerce,
 * patente, cnss) — only their labels change; a null label hides the field.
 * Client label = the identifier printed for the customer on documents.
 */
class Countries
{
    public const ALL = [
        'MA' => ['dial' => '212', 'name' => 'Maroc', 'currency' => 'MAD', 'vat' => 20, 'rates' => [20, 14, 10, 7, 0], 'decimals' => 2,
                 'labels' => ['ice' => 'ICE', 'identifiant_fiscal' => 'Identifiant fiscal (IF)', 'registre_commerce' => 'Registre de commerce (RC)', 'patente' => 'Taxe professionnelle (Patente)', 'cnss' => 'CNSS'],
                 'short' => ['ice' => 'ICE', 'identifiant_fiscal' => 'IF', 'registre_commerce' => 'RC', 'patente' => 'TP', 'cnss' => 'CNSS'], 'client' => 'ICE'],
        'FR' => ['dial' => '33', 'name' => 'France', 'currency' => 'EUR', 'vat' => 20, 'rates' => [20, 10, 5.5, 2.1, 0], 'decimals' => 2,
                 'labels' => ['ice' => 'SIRET', 'identifiant_fiscal' => 'N° TVA intracommunautaire', 'registre_commerce' => 'RCS', 'patente' => null, 'cnss' => null],
                 'short' => ['ice' => 'SIRET', 'identifiant_fiscal' => 'TVA', 'registre_commerce' => 'RCS'], 'client' => 'SIRET / N° TVA'],
        'BE' => ['dial' => '32', 'name' => 'Belgique', 'currency' => 'EUR', 'vat' => 21, 'rates' => [21, 12, 6, 0], 'decimals' => 2,
                 'labels' => ['ice' => "N° d'entreprise (BCE)", 'identifiant_fiscal' => 'N° TVA', 'registre_commerce' => 'RPM', 'patente' => null, 'cnss' => null],
                 'short' => ['ice' => 'BCE', 'identifiant_fiscal' => 'TVA', 'registre_commerce' => 'RPM'], 'client' => 'N° TVA'],
        'CH' => ['dial' => '41', 'name' => 'Suisse', 'currency' => 'CHF', 'vat' => 8.1, 'rates' => [8.1, 3.8, 2.6, 0], 'decimals' => 2,
                 'labels' => ['ice' => 'IDE (CHE-…)', 'identifiant_fiscal' => 'N° TVA', 'registre_commerce' => 'Registre du commerce', 'patente' => null, 'cnss' => null],
                 'short' => ['ice' => 'IDE', 'identifiant_fiscal' => 'TVA', 'registre_commerce' => 'RC'], 'client' => 'IDE'],
        'LU' => ['dial' => '352', 'name' => 'Luxembourg', 'currency' => 'EUR', 'vat' => 17, 'rates' => [17, 14, 8, 3, 0], 'decimals' => 2,
                 'labels' => ['ice' => 'Matricule', 'identifiant_fiscal' => 'N° TVA', 'registre_commerce' => 'RCS', 'patente' => null, 'cnss' => null],
                 'short' => ['ice' => 'Matricule', 'identifiant_fiscal' => 'TVA', 'registre_commerce' => 'RCS'], 'client' => 'N° TVA'],
        'SN' => ['dial' => '221', 'name' => 'Sénégal', 'currency' => 'XOF', 'vat' => 18, 'rates' => [18, 10, 0], 'decimals' => 0,
                 'labels' => ['ice' => 'NINEA', 'identifiant_fiscal' => null, 'registre_commerce' => 'RCCM', 'patente' => null, 'cnss' => null],
                 'short' => ['ice' => 'NINEA', 'registre_commerce' => 'RCCM'], 'client' => 'NINEA'],
        'CI' => ['dial' => '225', 'name' => "Côte d'Ivoire", 'currency' => 'XOF', 'vat' => 18, 'rates' => [18, 9, 0], 'decimals' => 0,
                 'labels' => ['ice' => 'NCC', 'identifiant_fiscal' => null, 'registre_commerce' => 'RCCM', 'patente' => null, 'cnss' => null],
                 'short' => ['ice' => 'NCC', 'registre_commerce' => 'RCCM'], 'client' => 'NCC'],
        'CM' => ['dial' => '237', 'name' => 'Cameroun', 'currency' => 'XAF', 'vat' => 19.25, 'rates' => [19.25, 0], 'decimals' => 0,
                 'labels' => ['ice' => 'NIU', 'identifiant_fiscal' => null, 'registre_commerce' => 'RCCM', 'patente' => null, 'cnss' => null],
                 'short' => ['ice' => 'NIU', 'registre_commerce' => 'RCCM'], 'client' => 'NIU'],
        'TN' => ['dial' => '216', 'name' => 'Tunisie', 'currency' => 'TND', 'vat' => 19, 'rates' => [19, 13, 7, 0], 'decimals' => 3,
                 'labels' => ['ice' => 'Matricule fiscal', 'identifiant_fiscal' => null, 'registre_commerce' => 'RNE', 'patente' => null, 'cnss' => 'CNSS'],
                 'short' => ['ice' => 'MF', 'registre_commerce' => 'RNE', 'cnss' => 'CNSS'], 'client' => 'Matricule fiscal'],
        'DZ' => ['dial' => '213', 'name' => 'Algérie', 'currency' => 'DZD', 'vat' => 19, 'rates' => [19, 9, 0], 'decimals' => 2,
                 'labels' => ['ice' => 'NIF', 'identifiant_fiscal' => 'NIS', 'registre_commerce' => 'RC', 'patente' => "Article d'imposition (AI)", 'cnss' => null],
                 'short' => ['ice' => 'NIF', 'identifiant_fiscal' => 'NIS', 'registre_commerce' => 'RC', 'patente' => 'AI'], 'client' => 'NIF'],
        'XX' => ['dial' => null, 'name' => 'Autre pays', 'currency' => 'EUR', 'vat' => 20, 'rates' => [20, 10, 5, 0], 'decimals' => 2,
                 'labels' => ['ice' => "N° d'identification de l'entreprise", 'identifiant_fiscal' => 'N° TVA', 'registre_commerce' => 'Registre du commerce', 'patente' => null, 'cnss' => null],
                 'short' => ['ice' => 'ID', 'identifiant_fiscal' => 'TVA', 'registre_commerce' => 'RC'], 'client' => "N° d'identification"],
    ];

    /** Currency words for the amount in words on invoices: [singular, plural, sub-unit plural or null]. */
    public const CURRENCY_WORDS = [
        'MAD' => ['dirham', 'dirhams', 'centimes'], 'EUR' => ['euro', 'euros', 'centimes'], 'CHF' => ['franc suisse', 'francs suisses', 'centimes'],
        'XOF' => ['franc CFA', 'francs CFA', null], 'XAF' => ['franc CFA', 'francs CFA', null], 'TND' => ['dinar', 'dinars', 'millimes'],
        'DZD' => ['dinar', 'dinars', 'centimes'],
    ];

    public static function get(?string $code): array
    {
        return self::ALL[$code] ?? self::ALL['MA'];
    }

    public static function options(): array
    {
        return collect(self::ALL)->map(fn ($c) => $c['name'])->all();
    }

    public static function for(Societe $s): array
    {
        return self::get($s->country_code ?: 'MA');
    }

    /** "ICE : 0012… · IF : …" pieces for the PDF legal lines, using this country's short labels. */
    public static function legalParts(Societe $s): array
    {
        $c = self::for($s);
        $out = [];
        foreach (['ice', 'identifiant_fiscal', 'registre_commerce', 'patente', 'cnss'] as $f) {
            if ($s->{$f} && ($c['labels'][$f] ?? null)) {
                $out[] = ($c['short'][$f] ?? $c['labels'][$f]).' : '.$s->{$f};
            }
        }

        return $out;
    }

    public static function words(?string $currency): array
    {
        return self::CURRENCY_WORDS[strtoupper((string) $currency)] ?? [$currency, $currency, 'centimes'];
    }
}
