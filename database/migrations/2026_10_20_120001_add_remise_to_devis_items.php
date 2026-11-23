<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devis_items', function (Blueprint $table) {
            $table->enum('remise_type', ['percent', 'fixed'])->default('percent')->after('unit_price');
            $table->decimal('remise_value', 10, 2)->default(0)->after('remise_type');
        });
    }

    public function down(): void
    {
        Schema::table('devis_items', function (Blueprint $table) {
            $table->dropColumn(['remise_type', 'remise_value']);
        });
    }
};
