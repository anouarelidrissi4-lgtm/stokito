<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-11-22: Inventaire (physical stock count) + legal invoice fields on the company
 * (Taxe professionnelle / patente, CNSS, payment conditions printed under invoices).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventaires', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->nullable()->index()->constrained('projects')->nullOnDelete();
            $t->boolean('is_demo')->default(false)->index();
            $t->string('numero');
            $t->date('date');
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('notes')->nullable();
            $t->unsignedInteger('nb_produits')->default(0);
            $t->unsignedInteger('nb_ecarts')->default(0);
            $t->timestamps();
            $t->unique(['project_id', 'is_demo', 'numero'], 'inventaires_tenant_numero_unique');
        });

        Schema::create('inventaire_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('inventaire_id')->constrained('inventaires')->cascadeOnDelete();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->integer('stock_systeme');
            $t->integer('stock_compte');
            $t->integer('ecart');
            $t->decimal('purchase_price', 12, 2)->default(0); // price at count time, for the value of the gap
            $t->timestamps();
        });

        Schema::table('societe', function (Blueprint $t) {
            $t->string('patente')->nullable()->after('registre_commerce');
            $t->string('cnss')->nullable()->after('patente');
            $t->text('conditions_paiement')->nullable()->after('cnss');
        });
    }

    public function down(): void
    {
        Schema::table('societe', fn (Blueprint $t) => $t->dropColumn(['patente', 'cnss', 'conditions_paiement']));
        Schema::dropIfExists('inventaire_items');
        Schema::dropIfExists('inventaires');
    }
};
