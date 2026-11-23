<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reglements_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facture_id')->nullable()->constrained('factures')->nullOnDelete();
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
        Schema::dropIfExists('reglements_clients');
    }
};
