<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Réduction par ligne sur les factures (comme les devis et les BL). 2026-11-27 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facture_items', function (Blueprint $table) {
            $table->enum('remise_type', ['percent', 'fixed'])->default('percent')->after('unit_price');
            $table->decimal('remise_value', 10, 2)->default(0)->after('remise_type');
        });
    }

    public function down(): void
    {
        Schema::table('facture_items', function (Blueprint $table) {
            $table->dropColumn(['remise_type', 'remise_value']);
        });
    }
};
