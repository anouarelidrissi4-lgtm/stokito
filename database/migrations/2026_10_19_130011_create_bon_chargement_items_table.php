<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bon_chargement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_de_chargement_id')->constrained('bons_de_chargement')->cascadeOnDelete();
            $table->foreignId('bon_livraison_id')->constrained('bon_livraisons')->cascadeOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('bon_chargement_items');
    }
};
