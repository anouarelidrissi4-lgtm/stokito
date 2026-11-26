<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Name written on the cheque / bill (the drawer), 2026-11-27. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reglements_clients', fn (Blueprint $t) => $t->string('tireur', 120)->nullable()->after('banque'));
    }

    public function down(): void
    {
        Schema::table('reglements_clients', fn (Blueprint $t) => $t->dropColumn('tireur'));
    }
};
