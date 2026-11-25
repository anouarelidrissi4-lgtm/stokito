<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Recurring expenses (2026-11-26): rent, Internet, salaries... are entered once and an expense is
// created on each due date by `php artisan depenses:recurrentes` (daily).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depenses_recurrentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_demo')->default(false);
            $table->string('libelle');
            $table->string('categorie', 40)->default('autre');
            $table->foreignId('fournisseur_id')->nullable()->constrained('fournisseurs')->nullOnDelete();
            $table->decimal('montant_ttc', 12, 2);
            $table->decimal('montant_tva', 12, 2)->default(0);
            $table->string('mode_paiement', 20)->default('virement');
            $table->foreignId('tresorerie_compte_id')->nullable()->constrained('tresorerie_comptes')->nullOnDelete();
            $table->string('frequence', 20)->default('mensuelle');
            $table->unsignedTinyInteger('jour')->default(1);
            $table->date('date_debut');
            $table->date('prochaine_date');
            $table->date('date_fin')->nullable();
            $table->boolean('actif')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['project_id', 'is_demo', 'actif', 'prochaine_date'], 'dep_rec_tenant_due_idx');
        });

        Schema::table('depenses', function (Blueprint $table) {
            $table->foreignId('depense_recurrente_id')->nullable()->after('user_id')->constrained('depenses_recurrentes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('depense_recurrente_id');
        });
        Schema::dropIfExists('depenses_recurrentes');
    }
};
