<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bon_livraisons', function (Blueprint $table) {
            $table->decimal('total_ht', 12, 2)->default(0)->after('show_prices');
            $table->decimal('tva_rate', 5, 2)->default(20)->after('total_ht');
            $table->enum('remise_type', ['percent', 'fixed'])->default('percent')->after('tva_rate');
            $table->decimal('remise_value', 10, 2)->default(0)->after('remise_type');
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->enum('remise_type', ['percent', 'fixed'])->default('percent')->after('notes');
            $table->decimal('remise_value', 10, 2)->default(0)->after('remise_type');
        });

        Schema::table('commande_fournisseurs', function (Blueprint $table) {
            $table->decimal('total_ht', 12, 2)->default(0)->after('status');
            $table->decimal('tva_rate', 5, 2)->default(20)->after('total_ht');
            $table->enum('remise_type', ['percent', 'fixed'])->default('percent')->after('tva_rate');
            $table->decimal('remise_value', 10, 2)->default(0)->after('remise_type');
        });
    }

    public function down(): void
    {
        Schema::table('bon_livraisons', function (Blueprint $table) {
            $table->dropColumn(['total_ht', 'tva_rate', 'remise_type', 'remise_value']);
        });
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['remise_type', 'remise_value']);
        });
        Schema::table('commande_fournisseurs', function (Blueprint $table) {
            $table->dropColumn(['total_ht', 'tva_rate', 'remise_type', 'remise_value']);
        });
    }
};
