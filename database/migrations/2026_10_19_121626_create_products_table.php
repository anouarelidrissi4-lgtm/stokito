<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('name');
            $table->string('unit')->default('piece');
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->integer('stock_qty')->default(0);
            $table->integer('stock_alert_threshold')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('products');
    }
};
