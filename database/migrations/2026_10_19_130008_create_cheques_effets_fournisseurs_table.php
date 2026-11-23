<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cheques_effets_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fournisseur_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reglement_fournisseur_id')->nullable()->constrained('reglements_fournisseurs')->nullOnDelete();
            $table->string('numero_cheque');
            $table->string('banque')->nullable();
            $table->decimal('montant', 12, 2);
            $table->date('date_emission');
            $table->date('date_echeance');
            $table->enum('status', ['en_attente', 'encaisse', 'rejete'])->default('en_attente');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('cheques_effets_fournisseurs');
    }
};
