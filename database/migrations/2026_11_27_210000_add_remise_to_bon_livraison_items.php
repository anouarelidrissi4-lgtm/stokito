<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Réduction par ligne sur les bons de livraison (comme les devis) + total net de la ligne. 2026-11-27 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bon_livraison_items', function (Blueprint $table) {
            $table->enum('remise_type', ['percent', 'fixed'])->default('percent')->after('unit_price');
            $table->decimal('remise_value', 10, 2)->default(0)->after('remise_type');
            $table->decimal('total', 12, 2)->nullable()->after('remise_value');
        });
    }

    public function down(): void
    {
        Schema::table('bon_livraison_items', function (Blueprint $table) {
            $table->dropColumn(['remise_type', 'remise_value', 'total']);
        });
    }
};
