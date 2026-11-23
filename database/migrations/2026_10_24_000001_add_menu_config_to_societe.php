<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('societe', function (Blueprint $table) {
            $table->json('menu_config')->nullable()->after('bons_chargement_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('societe', function (Blueprint $table) {
            $table->dropColumn('menu_config');
        });
    }
};
