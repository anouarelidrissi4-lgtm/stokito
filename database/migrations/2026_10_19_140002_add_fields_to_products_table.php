<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('brands')->nullOnDelete();
            $table->foreignId('fournisseur_id')->nullable()->after('brand_id')->constrained('fournisseurs')->nullOnDelete();
            $table->decimal('wholesale_price', 12, 2)->nullable()->after('sale_price');
        });
    }
    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('fournisseur_id');
            $table->dropColumn('wholesale_price');
        });
    }
};
