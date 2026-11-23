<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Document numbers, product references/barcodes and brand names were unique
 * across the WHOLE database. Each project (and the demo) numbers its own
 * documents, so they collided (DEV-00001 in two projects -> SQL error).
 * Now they are unique per (project_id, is_demo).
 */
return new class extends Migration {
    protected array $indexes = [
        'devis' => 'numero',
        'factures' => 'numero',
        'bon_livraisons' => 'numero',
        'commande_fournisseurs' => 'numero',
        'avoirs_clients' => 'numero',
        'avoirs_fournisseurs' => 'numero',
        'bons_de_chargement' => 'numero',
        'products' => ['reference', 'barcode'],
        'brands' => 'name',
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $columns) {
            foreach ((array) $columns as $col) {
                Schema::table($table, function (Blueprint $t) use ($table, $col) {
                    $t->dropUnique("{$table}_{$col}_unique");
                    $t->unique(['project_id', 'is_demo', $col], "{$table}_tenant_{$col}_unique");
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $columns) {
            foreach ((array) $columns as $col) {
                Schema::table($table, function (Blueprint $t) use ($table, $col) {
                    $t->dropUnique("{$table}_tenant_{$col}_unique");
                    $t->unique($col);
                });
            }
        }
    }
};
