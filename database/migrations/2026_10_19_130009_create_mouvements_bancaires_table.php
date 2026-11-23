<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mouvements_bancaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tresorerie_compte_id')->constrained('tresorerie_comptes')->cascadeOnDelete();
            $table->enum('type', ['entry', 'exit']);
            $table->decimal('montant', 12, 2);
            $table->date('date');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('mouvements_bancaires');
    }
};
