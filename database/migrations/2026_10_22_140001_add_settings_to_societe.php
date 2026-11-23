<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('societe', function (Blueprint $table) {
            $table->string('country')->default('Maroc')->after('name');
            $table->string('city')->nullable()->after('country');
            $table->string('phone2')->nullable()->after('phone');
            $table->string('fax')->nullable()->after('phone2');
            $table->unsignedTinyInteger('qty_decimals')->default(2)->after('tva_rate_default');
            $table->unsignedTinyInteger('price_decimals')->default(2)->after('qty_decimals');
            $table->unsignedInteger('stock_threshold_default')->default(0)->after('price_decimals');
            $table->string('facture_type')->default('sans_entete')->after('stock_threshold_default');
            $table->boolean('stock_verification')->default(false)->after('facture_type');
            $table->boolean('show_ref_facture')->default(true)->after('stock_verification');
            $table->boolean('show_ref_bl')->default(true)->after('show_ref_facture');
            $table->boolean('bons_chargement_enabled')->default(true)->after('show_ref_bl');
        });
    }

    public function down(): void
    {
        Schema::table('societe', function (Blueprint $table) {
            $table->dropColumn([
                'country', 'city', 'phone2', 'fax', 'qty_decimals', 'price_decimals',
                'stock_threshold_default', 'facture_type', 'stock_verification',
                'show_ref_facture', 'show_ref_bl', 'bons_chargement_enabled',
            ]);
        });
    }
};
