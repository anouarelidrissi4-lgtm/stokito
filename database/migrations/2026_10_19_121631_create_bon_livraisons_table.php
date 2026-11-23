<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bon_livraisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('devis_id')->nullable()->constrained('devis')->nullOnDelete();
            $table->string('numero')->unique();
            $table->date('date');
            $table->boolean('show_prices')->default(true);
            $table->enum('status', ['draft', 'delivered'])->default('draft');
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('bon_livraisons');
    }
};
