<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('commande_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fournisseur_id')->constrained()->cascadeOnDelete();
            $table->string('numero')->unique();
            $table->date('date');
            $table->enum('status', ['draft', 'ordered', 'received'])->default('draft');
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('commande_fournisseurs');
    }
};
