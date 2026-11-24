<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 2026-11-24: documents sent by email, payment reminders. Nullable / defaulted only. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('devis', fn (Blueprint $t) => $t->timestamp('sent_at')->nullable()->after('status'));
        Schema::table('factures', function (Blueprint $t) {
            $t->timestamp('sent_at')->nullable()->after('status');
            $t->unsignedTinyInteger('relance_niveau')->default(0)->after('sent_at');
            $t->timestamp('relance_at')->nullable()->after('relance_niveau');
        });
        Schema::table('societe', fn (Blueprint $t) => $t->boolean('relances_auto')->default(false)->after('devis_validite_jours'));
    }

    public function down(): void
    {
        Schema::table('devis', fn (Blueprint $t) => $t->dropColumn('sent_at'));
        Schema::table('factures', fn (Blueprint $t) => $t->dropColumn(['sent_at', 'relance_niveau', 'relance_at']));
        Schema::table('societe', fn (Blueprint $t) => $t->dropColumn('relances_auto'));
    }
};
