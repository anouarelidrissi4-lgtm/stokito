<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('commande_fournisseur_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_fournisseur_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('commande_fournisseur_items');
    }
};
