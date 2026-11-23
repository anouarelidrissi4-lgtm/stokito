<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tresorerie_comptes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['banque', 'caisse'])->default('caisse');
            $table->string('bank_name')->nullable();
            $table->string('rib')->nullable();
            $table->decimal('initial_balance', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tresorerie_comptes');
    }
};
