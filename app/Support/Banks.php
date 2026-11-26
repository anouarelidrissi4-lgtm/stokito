<?php

namespace App\Support;

use App\Models\Societe;

/**
 * Banks offered in payment forms, by the company's country (2026-11-26). "Autre banque" lets people type any name,
 * so a missing or renamed bank never blocks a payment.
 */
class Banks
{
    public const LIST = [
        'MA' => ['Attijariwafa bank', 'Banque Populaire (BCP)', 'Bank of Africa (BMCE)', 'CIH Bank', 'Crédit Agricole du Maroc',
            'Saham Bank (ex-Société Générale Maroc)', 'BMCI', 'Crédit du Maroc', 'CFG Bank', 'Al Barid Bank', 'Arab Bank',
            'Citibank Maghreb', 'Bank Assafa', 'Umnia Bank', 'Al Akhdar Bank', 'Bank Al Yousr', 'BTI Bank', 'Dar Al Amane'],
        'FR' => ['BNP Paribas', 'Société Générale', 'Crédit Agricole', 'LCL', 'Banque Populaire', 'Caisse d’Épargne', 'Crédit Mutuel',
            'CIC', 'La Banque Postale', 'BoursoBank', 'Hello bank!', 'Fortuneo', 'Monabanq', 'BforBank', 'CCF', 'Crédit Coopératif',
            'Qonto', 'Shine', 'Revolut', 'N26'],
        'BE' => ['BNP Paribas Fortis', 'KBC', 'CBC', 'Belfius', 'ING Belgique', 'Argenta', 'Crelan', 'Beobank', 'Keytrade Bank', 'Triodos'],
        'LU' => ['Spuerkeess (BCEE)', 'BGL BNP Paribas', 'Banque Internationale à Luxembourg (BIL)', 'ING Luxembourg', 'Banque Raiffeisen', 'POST Luxembourg'],
        'CH' => ['UBS', 'PostFinance', 'Raiffeisen', 'Banque Cantonale Vaudoise (BCV)', 'Banque Cantonale de Genève (BCGE)',
            'Zürcher Kantonalbank', 'Banque Migros', 'Banque Cler', 'Valiant'],
        'TN' => ['BIAT', 'STB', 'BNA', 'Attijari bank', 'Amen Bank', 'BH Bank', 'UIB', 'Banque de Tunisie', 'UBCI', 'ATB',
            'Banque Zitouna', 'Al Baraka Bank', 'QNB Tunisia', 'La Poste Tunisienne'],
        'DZ' => ['BNA', 'CPA', 'BEA', 'BADR', 'BDL', 'CNEP-Banque', 'AGB', 'Société Générale Algérie', 'BNP Paribas El Djazaïr',
            'Al Baraka Banque', 'Al Salam Bank', 'Algérie Poste (CCP)'],
        'SN' => ['CBAO Groupe Attijariwafa bank', 'Ecobank Sénégal', 'Bank of Africa Sénégal', 'Banque Islamique du Sénégal',
            'Orabank Sénégal', 'UBA Sénégal', 'Banque Atlantique Sénégal', 'La Banque Agricole', 'Coris Bank Sénégal'],
        'CI' => ['Société Générale Côte d’Ivoire', 'SIB', 'NSIA Banque', 'Ecobank Côte d’Ivoire', 'Banque Atlantique',
            'Bank of Africa Côte d’Ivoire', 'Coris Bank', 'Orabank', 'UBA Côte d’Ivoire', 'Bridge Bank'],
        'CM' => ['Afriland First Bank', 'SCB Cameroun', 'BICEC', 'Ecobank Cameroun', 'UBA Cameroun', 'CCA Bank', 'Banque Atlantique Cameroun'],
    ];

    /** @return string[] */
    public static function for(?Societe $s = null): array
    {
        $s ??= Societe::current();

        return self::LIST[strtoupper((string) ($s?->country_code ?? ''))] ?? [];
    }
}
