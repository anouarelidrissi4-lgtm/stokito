<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Personnel (2026-11-28): employees paid by the day, week or month, attendance (pointage),
 * salary advances, loans repaid from the pay, and pay records (net paid → a "salaires" expense).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->nullable()->index()->constrained('projects')->nullOnDelete();
            $t->boolean('is_demo')->default(false)->index();
            $t->string('nom');
            $t->string('poste', 100)->nullable();
            $t->string('telephone', 40)->nullable();
            $t->string('cin', 40)->nullable();
            $t->date('date_entree')->nullable();
            $t->string('type_paie', 10)->default('jour');          // jour | semaine | mois
            $t->decimal('taux', 12, 2)->default(0);                  // per day, per week or per month
            $t->boolean('actif')->default(true);
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('employe_presences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->nullable()->index()->constrained('projects')->nullOnDelete();
            $t->boolean('is_demo')->default(false)->index();
            $t->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $t->date('date');
            $t->char('statut', 1);                                    // P présent, D demi-journée, A absent, C congé, F férié
            $t->timestamps();
            $t->unique(['employe_id', 'date']);
        });

        Schema::create('employe_paies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->nullable()->index()->constrained('projects')->nullOnDelete();
            $t->boolean('is_demo')->default(false)->index();
            $t->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $t->date('periode_debut');
            $t->date('periode_fin');
            $t->decimal('jours', 6, 1)->default(0);
            $t->decimal('base', 12, 2)->default(0);
            $t->decimal('prime', 12, 2)->default(0);
            $t->decimal('avances', 12, 2)->default(0);
            $t->decimal('retenue_credit', 12, 2)->default(0);
            $t->json('credit_allocation')->nullable();               // {credit_id: amount} to undo on delete
            $t->decimal('net', 12, 2)->default(0);
            $t->date('date_paiement');
            $t->string('mode_paiement', 20)->default('especes');
            $t->foreignId('tresorerie_compte_id')->nullable()->constrained('tresorerie_comptes')->nullOnDelete();
            $t->foreignId('depense_id')->nullable()->constrained('depenses')->nullOnDelete();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('employe_avances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->nullable()->index()->constrained('projects')->nullOnDelete();
            $t->boolean('is_demo')->default(false)->index();
            $t->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $t->date('date');
            $t->decimal('montant', 12, 2);
            $t->string('mode_paiement', 20)->default('especes');
            $t->foreignId('tresorerie_compte_id')->nullable()->constrained('tresorerie_comptes')->nullOnDelete();
            $t->foreignId('depense_id')->nullable()->constrained('depenses')->nullOnDelete();
            $t->foreignId('paie_id')->nullable()->constrained('employe_paies')->nullOnDelete();   // deducted on this pay
            $t->string('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('employe_credits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->nullable()->index()->constrained('projects')->nullOnDelete();
            $t->boolean('is_demo')->default(false)->index();
            $t->foreignId('employe_id')->constrained('employes')->cascadeOnDelete();
            $t->date('date');
            $t->decimal('montant', 12, 2);
            $t->decimal('mensualite', 12, 2)->default(0);            // taken from each pay
            $t->decimal('rembourse', 12, 2)->default(0);
            $t->string('mode_paiement', 20)->default('especes');
            $t->foreignId('tresorerie_compte_id')->nullable()->constrained('tresorerie_comptes')->nullOnDelete();
            $t->string('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employe_credits');
        Schema::dropIfExists('employe_avances');
        Schema::dropIfExists('employe_paies');
        Schema::dropIfExists('employe_presences');
        Schema::dropIfExists('employes');
    }
};
