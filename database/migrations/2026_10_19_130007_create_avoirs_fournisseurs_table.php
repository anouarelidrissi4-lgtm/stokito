<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('avoirs_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fournisseur_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commande_fournisseur_id')->nullable()->constrained('commande_fournisseurs')->nullOnDelete();
            $table->string('numero')->unique();
            $table->date('date');
            $table->decimal('montant', 12, 2);
            $table->string('motif')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('avoirs_fournisseurs');
    }
};
