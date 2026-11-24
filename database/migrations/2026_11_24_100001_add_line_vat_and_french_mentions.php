<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-11-24: several VAT rates in one document + French invoice mentions.
 * Every new column is nullable or has a default, so existing documents keep their totals:
 * a line with tva_rate NULL uses its document's tva_rate, exactly as before.
 */
return new class extends Migration {
    public function up(): void
    {
        foreach (['devis_items', 'facture_items', 'bon_livraison_items'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->decimal('tva_rate', 5, 2)->nullable()->after('unit_price'));
        }
        Schema::table('products', fn (Blueprint $t) => $t->decimal('tva_rate', 5, 2)->nullable()->after('wholesale_price'));

        Schema::table('factures', function (Blueprint $t) {
            $t->string('adresse_livraison', 500)->nullable()->after('notes');
            $t->string('nature_operation', 20)->nullable()->after('adresse_livraison'); // biens | services | mixte
        });

        Schema::table('societe', function (Blueprint $t) {
            $t->boolean('tva_franchise')->default(false)->after('tva_rate_default');      // art. 293 B CGI
            $t->string('penalites_taux', 255)->nullable()->after('tva_franchise');
            $t->boolean('indemnite_40')->default(false)->after('penalites_taux');
            $t->boolean('escompte_neant')->default(false)->after('indemnite_40');
            $t->boolean('tva_debits')->default(false)->after('escompte_neant');
            $t->string('nature_operation_default', 20)->nullable()->after('tva_debits');
        });
    }

    public function down(): void
    {
        foreach (['devis_items', 'facture_items', 'bon_livraison_items', 'products'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('tva_rate'));
        }
        Schema::table('factures', fn (Blueprint $t) => $t->dropColumn(['adresse_livraison', 'nature_operation']));
        Schema::table('societe', fn (Blueprint $t) => $t->dropColumn(['tva_franchise', 'penalites_taux', 'indemnite_40', 'escompte_neant', 'tva_debits', 'nature_operation_default']));
    }
};
