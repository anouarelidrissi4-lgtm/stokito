<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Payments on delivery notes, bank name and due date of cheques / bills (2026-11-26). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reglements_clients', function (Blueprint $t) {
            $t->foreignId('bon_livraison_id')->nullable()->after('facture_id')->constrained('bon_livraisons')->nullOnDelete();
            $t->string('banque', 100)->nullable()->after('numero_cheque');
            $t->date('date_echeance')->nullable()->after('banque');
        });
    }

    public function down(): void
    {
        Schema::table('reglements_clients', function (Blueprint $t) {
            $t->dropConstrainedForeignId('bon_livraison_id');
            $t->dropColumn(['banque', 'date_echeance']);
        });
    }
};
