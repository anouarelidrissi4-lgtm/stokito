<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Module Dépenses (2026-11-25): running costs (rent, energy, fuel...) outside supplier purchase orders. */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('depenses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->nullable()->index()->constrained('projects')->nullOnDelete();
            $t->boolean('is_demo')->default(false)->index();
            $t->date('date');
            $t->string('categorie', 40);
            $t->string('libelle');
            $t->foreignId('fournisseur_id')->nullable()->constrained('fournisseurs')->nullOnDelete();
            $t->decimal('montant_ttc', 12, 2);
            $t->decimal('montant_tva', 12, 2)->default(0);
            $t->string('mode_paiement', 20)->default('especes');
            $t->foreignId('tresorerie_compte_id')->nullable()->constrained('tresorerie_comptes')->nullOnDelete();
            $t->string('reference', 100)->nullable();
            $t->string('justificatif_path')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['project_id', 'is_demo', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
