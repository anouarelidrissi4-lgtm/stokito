<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Product fields asked by the user (2026-11-25): type, packaging, weight per unit / per metre and purchase price per kg.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'type')) {
                $table->string('type', 100)->nullable()->after('name');
            }
            if (! Schema::hasColumn('products', 'conditionnement')) {
                $table->string('conditionnement', 100)->nullable()->after('unit');
            }
            if (! Schema::hasColumn('products', 'poids')) {
                $table->decimal('poids', 12, 3)->nullable()->after('conditionnement');
            }
            if (! Schema::hasColumn('products', 'prix_achat_kg')) {
                $table->decimal('prix_achat_kg', 12, 3)->nullable()->after('purchase_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['type', 'conditionnement', 'poids', 'prix_achat_kg'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
