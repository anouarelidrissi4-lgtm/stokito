<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-11-25 — Caisse (point of sale) with the French "logiciel de caisse sécurisé" rules
 * (art. 286 I 3° bis CGI: inaltérabilité, sécurisation, conservation, archivage):
 *  - tickets, lines, payments, closures and the event journal are append-only;
 *  - every row carries the HMAC of its content + the previous row's HMAC (chain);
 *  - database triggers refuse UPDATE / DELETE of real (non-demo) rows, so even a manual
 *    query cannot rewrite a sale. Demo rows stay deletable for the nightly demo reset.
 */
return new class extends Migration {
    protected array $locked = ['caisse_tickets', 'caisse_ticket_lignes', 'caisse_paiements', 'caisse_clotures', 'caisse_journal'];

    public function up(): void
    {
        Schema::create('caisse_tickets', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id')->index();
            $t->boolean('is_demo')->default(false);
            $t->unsignedInteger('sequence');
            $t->string('numero', 20);
            $t->string('type', 12)->default('vente'); // vente | annulation
            $t->unsignedBigInteger('annule_ticket_id')->nullable()->index();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('user_name')->nullable();
            $t->unsignedBigInteger('client_id')->nullable();
            $t->string('client_name')->nullable();
            $t->decimal('total_ht', 12, 2);
            $t->decimal('total_tva', 12, 2);
            $t->decimal('total_ttc', 12, 2);
            $t->decimal('montant_recu', 12, 2)->default(0);
            $t->decimal('rendu', 12, 2)->default(0);
            $t->json('tva_detail');
            $t->char('prev_hash', 64);
            $t->char('hash', 64);
            $t->timestamps();
            $t->unique(['project_id', 'is_demo', 'sequence']);
        });

        Schema::create('caisse_ticket_lignes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('ticket_id')->index();
            $t->boolean('is_demo')->default(false);
            $t->unsignedBigInteger('product_id')->nullable();
            $t->string('reference')->nullable();
            $t->string('designation');
            $t->decimal('quantity', 12, 3);
            $t->decimal('prix_unitaire_ttc', 12, 2);
            $t->decimal('remise_pct', 5, 2)->default(0);
            $t->decimal('tva_rate', 5, 2);
            $t->decimal('total_ttc', 12, 2);
            $t->decimal('total_ht', 12, 2);
            $t->timestamps();
        });

        Schema::create('caisse_paiements', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('ticket_id')->index();
            $t->boolean('is_demo')->default(false);
            $t->string('mode', 20); // especes | carte | cheque | autre
            $t->decimal('montant', 12, 2);
            $t->timestamps();
        });

        Schema::create('caisse_clotures', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id')->index();
            $t->boolean('is_demo')->default(false);
            $t->string('type', 8); // jour | mois | annee
            $t->unsignedInteger('sequence');
            $t->string('periode', 10); // 2026-11-25 | 2026-11 | 2026
            $t->unsignedInteger('first_seq')->nullable();
            $t->unsignedInteger('last_seq')->nullable();
            $t->unsignedInteger('nb_tickets')->default(0);
            $t->unsignedInteger('nb_annulations')->default(0);
            $t->decimal('total_ht', 14, 2);
            $t->decimal('total_tva', 14, 2);
            $t->decimal('total_ttc', 14, 2);
            $t->json('tva_detail');
            $t->json('paiements');
            $t->decimal('cumul_ttc', 16, 2);      // grand total perpétuel
            $t->decimal('cumul_abs_ttc', 16, 2);  // grand total perpétuel en valeur absolue
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('user_name')->nullable();
            $t->char('prev_hash', 64);
            $t->char('hash', 64);
            $t->timestamps();
            $t->unique(['project_id', 'is_demo', 'type', 'sequence']);
        });

        Schema::create('caisse_journal', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id')->index();
            $t->boolean('is_demo')->default(false);
            $t->unsignedInteger('sequence');
            $t->string('code', 30);
            $t->string('libelle');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('user_name')->nullable();
            $t->json('data')->nullable();
            $t->char('prev_hash', 64);
            $t->char('hash', 64);
            $t->timestamps();
            $t->unique(['project_id', 'is_demo', 'sequence']);
        });

        Schema::table('societe', fn (Blueprint $t) => $t->boolean('caisse_enabled')->default(false)->after('bons_chargement_enabled'));

        $this->createTriggers();
    }

    protected function createTriggers(): void
    {
        $driver = DB::getDriverName();
        foreach ($this->locked as $table) {
            foreach (['UPDATE', 'DELETE'] as $op) {
                $name = $table.'_no_'.strtolower($op);
                $msg = "Caisse : les données enregistrées ne peuvent pas être modifiées ni supprimées ($table).";
                if ($driver === 'mysql') {
                    DB::unprepared("CREATE TRIGGER {$name} BEFORE {$op} ON {$table} FOR EACH ROW BEGIN IF OLD.is_demo = 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '{$msg}'; END IF; END");
                } elseif ($driver === 'sqlite') {
                    DB::unprepared("CREATE TRIGGER {$name} BEFORE {$op} ON {$table} FOR EACH ROW WHEN OLD.is_demo = 0 BEGIN SELECT RAISE(ABORT, '{$msg}'); END");
                }
            }
        }
    }

    public function down(): void
    {
        // Real sales must be kept (legal retention): refuse to drop the tables once they hold any.
        foreach ($this->locked as $table) {
            if (Schema::hasTable($table) && DB::table($table)->where('is_demo', false)->exists()) {
                throw new RuntimeException("Rollback refusé : {$table} contient des données de caisse réelles.");
            }
        }
        foreach ($this->locked as $table) {
            foreach (['update', 'delete'] as $op) {
                DB::unprepared("DROP TRIGGER IF EXISTS {$table}_no_{$op}");
            }
            Schema::dropIfExists($table);
        }
        Schema::table('societe', fn (Blueprint $t) => $t->dropColumn('caisse_enabled'));
    }
};
