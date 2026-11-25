<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 2026-11-25: an invoice can be made for a till ticket (customer asks for one); the ticket already paid it and took the stock. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('factures', fn (Blueprint $t) => $t->unsignedBigInteger('caisse_ticket_id')->nullable()->index()->after('devis_id'));
    }

    public function down(): void
    {
        Schema::table('factures', fn (Blueprint $t) => $t->dropColumn('caisse_ticket_id'));
    }
};
