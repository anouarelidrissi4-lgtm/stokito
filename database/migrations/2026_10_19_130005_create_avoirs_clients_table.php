<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('avoirs_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facture_id')->nullable()->constrained('factures')->nullOnDelete();
            $table->string('numero')->unique();
            $table->date('date');
            $table->decimal('montant', 12, 2);
            $table->string('motif')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('avoirs_clients');
    }
};
