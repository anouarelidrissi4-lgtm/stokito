<?php

namespace App\Support;

use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ODS\Reader as OdsReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

/**
 * Products <-> spreadsheet (2026-11-25). Real .xlsx export (the old export was a
 * comma CSV that French/Moroccan Excel opens in one column with broken accents),
 * and an import that reads .xlsx / .ods / .csv (comma, semicolon or tab, UTF-8 or
 * Windows-1252) and finds the columns by their header name.
 */
class ProductSheet
{
    /** field => header written on export (also accepted on import) */
    public const HEADERS = [
        'reference' => 'Référence',
        'barcode' => 'Code-barres',
        'name' => 'Désignation',
        'category' => 'Catégorie',
        'brand' => 'Marque',
        'fournisseur' => 'Fournisseur',
        'unit' => 'Unité',
        'purchase_price' => "Prix d'achat",
        'sale_price' => 'Prix de vente TTC',
        'wholesale_price' => 'Prix de vente gros',
        'stock_qty' => 'Stock',
        'stock_alert_threshold' => "Seuil d'alerte",
        'type' => 'Type',
        'conditionnement' => 'Conditionnement',
        'poids' => 'Poids KG/ML',
        'prix_achat_kg' => 'Prix achat KG',
    ];

    /** extra header spellings accepted on import (normalised, see norm()) */
    private const ALIASES = [
        'reference' => ['ref', 'reference', 'code', 'sku', 'article'],
        'barcode' => ['code barres', 'codebarres', 'code barre', 'barcode', 'ean', 'gencod'],
        'name' => ['designation', 'nom', 'libelle', 'produit', 'name', 'description'],
        'category' => ['categorie', 'category', 'famille'],
        'brand' => ['marque', 'brand'],
        'fournisseur' => ['fournisseur', 'supplier'],
        'unit' => ['unite', 'unit'],
        'purchase_price' => ['prix achat', 'prix d achat', 'pa', 'cout', 'purchase price'],
        'sale_price' => ['prix vente ttc', 'prix de vente ttc', 'prix vente', 'prix de vente', 'pv', 'prix', 'sale price'],
        'wholesale_price' => ['prix vente gros', 'prix de vente gros', 'prix gros', 'wholesale price'],
        'stock_qty' => ['stock', 'quantite', 'qte', 'qty', 'quantity'],
        'stock_alert_threshold' => ['seuil alerte', 'seuil d alerte', 'seuil', 'alerte', 'stock minimum', 'min'],
        'type' => ['type', 'famille produit'],
        'conditionnement' => ['conditionnement', 'colisage', 'emballage'],
        'poids' => ['poids', 'poids kg', 'poids kg ml', 'poids ml', 'kg ml', 'weight'],
        'prix_achat_kg' => ['prix achat kg', 'prix d achat kg', 'prix kg', 'prix achat au kg', 'prix d achat au kg'],
    ];

    /** Writes the products to a temporary .xlsx file and returns its path. */
    public static function export(Collection $products, bool $withPurchase): string
    {
        $path = tempnam(sys_get_temp_dir(), 'stk').'.xlsx';
        $writer = new XlsxWriter();
        $writer->openToFile($path);
        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Produits');
        foreach ([1 => 16, 2 => 16, 3 => 42, 4 => 18, 5 => 16, 6 => 22, 7 => 10, 8 => 13, 9 => 16, 10 => 17, 11 => 10, 12 => 13, 13 => 16, 14 => 20, 15 => 13, 16 => 14] as $col => $w) {
            $sheet->setColumnWidth($w, $col);
        }
        $writer->addRow(Row::fromValues(array_values(self::HEADERS), (new Style())->setFontBold()->setFontColor('FFFFFF')->setBackgroundColor('107C41')));
        foreach ($products as $p) {
            $writer->addRow(Row::fromValues([
                (string) $p->reference,
                (string) ($p->barcode ?? ''),
                (string) $p->name,
                (string) ($p->category?->name ?? ''),
                (string) ($p->brand?->name ?? ''),
                (string) ($p->fournisseur?->name ?? ''),
                (string) ($p->unit ?? ''),
                $withPurchase ? (float) $p->purchase_price : '',
                (float) $p->sale_price,
                $p->wholesale_price !== null ? (float) $p->wholesale_price : '',
                (float) $p->stock_qty,
                $p->stock_alert_threshold !== null ? (float) $p->stock_alert_threshold : '',
                (string) ($p->type ?? ''),
                (string) ($p->conditionnement ?? ''),
                $p->poids !== null ? (float) $p->poids : '',
                $withPurchase && $p->prix_achat_kg !== null ? (float) $p->prix_achat_kg : '',
            ]));
        }
        $writer->close();

        return $path;
    }

    /**
     * Reads the first sheet. Returns ['rows' => list<array<field, mixed>>, 'unknown' => list<string>]
     * where each row also has '_line' (spreadsheet line number).
     */
    public static function read(string $path, string $ext): array
    {
        $ext = strtolower($ext);
        $raw = in_array($ext, ['xlsx', 'ods'], true) ? self::readSpreadsheet($path, $ext) : self::readCsv($path);
        if (! $raw) {
            return ['rows' => [], 'unknown' => []];
        }

        $header = array_shift($raw);
        $map = [];
        $unknown = [];
        foreach ($header as $i => $h) {
            $field = self::fieldFor((string) $h);
            if ($field && ! in_array($field, $map, true)) {
                $map[$i] = $field;
            } elseif (trim((string) $h) !== '') {
                $unknown[] = trim((string) $h);
            }
        }
        $offset = 0;
        // no recognisable header: assume the column order of our own export
        if (! in_array('reference', $map, true) && ! in_array('name', $map, true)) {
            $map = array_keys(self::HEADERS);
            array_unshift($raw, $header);
            $unknown = [];
            $offset = 1;
        }

        $rows = [];
        foreach ($raw as $n => $cells) {
            $row = ['_line' => $n + 2 - $offset];
            $empty = true;
            foreach ($map as $i => $field) {
                $v = $cells[$i] ?? null;
                if (is_string($v)) {
                    $v = trim($v);
                }
                if ($v !== null && $v !== '') {
                    $empty = false;
                }
                $row[$field] = $v;
            }
            if (! $empty) {
                $rows[] = $row;
            }
        }

        return ['rows' => $rows, 'unknown' => $unknown];
    }

    /** "1 234,50" / "1234.5" / 12 / "12 €" -> 1234.5 ; '' / garbage -> null */
    public static function num(mixed $v): ?float
    {
        if (is_int($v) || is_float($v)) {
            return (float) $v;
        }
        $s = preg_replace('/[^\d,.\-]/u', '', (string) $v);
        if ($s === '' || $s === '-') {
            return null;
        }
        if (str_contains($s, ',') && str_contains($s, '.')) {
            // the last separator is the decimal one
            $s = strrpos($s, ',') > strrpos($s, '.') ? str_replace(['.', ','], ['', '.'], $s) : str_replace(',', '', $s);
        } else {
            $s = str_replace(',', '.', $s);
        }

        return is_numeric($s) ? (float) $s : null;
    }

    private static function fieldFor(string $header): ?string
    {
        $h = self::norm($header);
        if ($h === '') {
            return null;
        }
        foreach (self::HEADERS as $field => $label) {
            if ($h === self::norm($label)) {
                return $field;
            }
        }
        foreach (self::ALIASES as $field => $aliases) {
            if (in_array($h, $aliases, true)) {
                return $field;
            }
        }

        return null;
    }

    private static function norm(string $s): string
    {
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s);
        $s = \Illuminate\Support\Str::ascii(mb_strtolower(trim($s)));
        $s = preg_replace('/[^a-z0-9]+/', ' ', $s);

        return trim(preg_replace('/\s+/', ' ', $s));
    }

    private static function readSpreadsheet(string $path, string $ext): array
    {
        $reader = $ext === 'ods' ? new OdsReader() : new XlsxReader();
        $reader->open($path);
        $out = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                // a formula cell gives its last computed value (Excel saves it), not the formula text (2026-11-27)
                $out[] = array_map(function ($cell) {
                    $v = $cell instanceof \OpenSpout\Common\Entity\Cell\FormulaCell ? $cell->getComputedValue() : $cell->getValue();

                    return $v instanceof \DateTimeInterface ? $v->format('Y-m-d') : $v;
                }, $row->getCells());
            }
            break; // first sheet only
        }
        $reader->close();

        return $out;
    }

    private static function readCsv(string $path): array
    {
        $head = (string) file_get_contents($path, false, null, 0, 8192);
        $first = strtok(preg_replace('/^\xEF\xBB\xBF/', '', $head), "\n") ?: '';
        $counts = [';' => substr_count($first, ';'), ',' => substr_count($first, ','), "\t" => substr_count($first, "\t")];
        arsort($counts);
        $options = new CsvOptions();
        $options->FIELD_DELIMITER = (string) array_key_first($counts);
        if (! mb_check_encoding($head, 'UTF-8')) {
            $options->ENCODING = 'Windows-1252';
        }
        $reader = new CsvReader($options);
        $reader->open($path);
        $out = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $out[] = $row->toArray();
            }
        }
        $reader->close();
        if ($out && isset($out[0][0]) && is_string($out[0][0])) {
            $out[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $out[0][0]);
        }

        return $out;
    }
}
