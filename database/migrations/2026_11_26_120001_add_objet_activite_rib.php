<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Invoice layout like the classic Moroccan model (2026-11-26): company activity line, bank account, document subject. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('societe', function (Blueprint $t) {
            $t->string('activite')->nullable()->after('name');
            $t->string('rib')->nullable()->after('conditions_paiement');
        });
        Schema::table('factures', fn (Blueprint $t) => $t->string('objet')->nullable()->after('notes'));
        Schema::table('devis', fn (Blueprint $t) => $t->string('objet')->nullable()->after('notes'));
    }

    public function down(): void
    {
        Schema::table('societe', fn (Blueprint $t) => $t->dropColumn(['activite', 'rib']));
        Schema::table('factures', fn (Blueprint $t) => $t->dropColumn('objet'));
        Schema::table('devis', fn (Blueprint $t) => $t->dropColumn('objet'));
    }
};
