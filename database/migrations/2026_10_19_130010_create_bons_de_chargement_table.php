<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bons_de_chargement', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('date');
            $table->string('vehicule')->nullable();
            $table->string('chauffeur')->nullable();
            $table->enum('status', ['preparation', 'charge', 'livre'])->default('preparation');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('bons_de_chargement');
    }
};
