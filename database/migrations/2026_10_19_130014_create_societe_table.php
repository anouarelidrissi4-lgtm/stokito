<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('societe', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('ice')->nullable();
            $table->string('identifiant_fiscal')->nullable();
            $table->string('registre_commerce')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('currency', 10)->default('MAD');
            $table->decimal('tva_rate_default', 5, 2)->default(20);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('societe');
    }
};
