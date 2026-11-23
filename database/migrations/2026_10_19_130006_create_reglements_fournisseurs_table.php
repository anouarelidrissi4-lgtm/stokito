<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reglements_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fournisseur_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commande_fournisseur_id')->nullable()->constrained('commande_fournisseurs')->nullOnDelete();
            $table->foreignId('tresorerie_compte_id')->nullable()->constrained('tresorerie_comptes')->nullOnDelete();
            $table->decimal('montant', 12, 2);
            $table->date('date');
            $table->enum('mode', ['especes', 'cheque', 'virement', 'carte'])->default('especes');
            $table->string('numero_cheque')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('reglements_fournisseurs');
    }
};
