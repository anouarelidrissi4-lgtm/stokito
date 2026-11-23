<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bon_livraison_id')->nullable()->constrained('bon_livraisons')->nullOnDelete();
            $table->string('numero')->unique();
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('tva_rate', 5, 2)->default(20);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->enum('status', ['brouillon', 'envoyee', 'partiellement_payee', 'payee', 'en_retard'])->default('brouillon');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('factures');
    }
};
