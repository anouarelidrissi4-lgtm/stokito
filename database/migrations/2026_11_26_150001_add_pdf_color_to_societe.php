<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Colour of the company's PDF documents (2026-11-26). Null = Stokito yellow. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('societe', fn (Blueprint $t) => $t->string('pdf_color', 7)->nullable()->after('logo_path'));
    }

    public function down(): void
    {
        Schema::table('societe', fn (Blueprint $t) => $t->dropColumn('pdf_color'));
    }
};
