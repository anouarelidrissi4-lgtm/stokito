<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 2026-11-24: quote validity date, invoice made straight from a quote (no delivery note). All nullable / defaulted. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('devis', fn (Blueprint $t) => $t->date('valid_until')->nullable()->after('date'));
        Schema::table('factures', fn (Blueprint $t) => $t->unsignedBigInteger('devis_id')->nullable()->index()->after('bon_livraison_id'));
        Schema::table('societe', fn (Blueprint $t) => $t->unsignedSmallInteger('devis_validite_jours')->default(30)->after('nature_operation_default'));
    }

    public function down(): void
    {
        Schema::table('devis', fn (Blueprint $t) => $t->dropColumn('valid_until'));
        Schema::table('factures', fn (Blueprint $t) => $t->dropColumn('devis_id'));
        Schema::table('societe', fn (Blueprint $t) => $t->dropColumn('devis_validite_jours'));
    }
};
