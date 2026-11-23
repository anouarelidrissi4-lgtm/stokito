<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('numero')->unique();
            $table->date('date');
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected'])->default('draft');
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('tva_rate', 5, 2)->default(20);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('devis');
    }
};
