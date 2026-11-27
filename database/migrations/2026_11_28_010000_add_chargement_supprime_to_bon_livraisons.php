<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A delivery note whose automatic loading slip was deleted by the user: it is not created again (2026-11-28). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bon_livraisons', function (Blueprint $table) {
            $table->boolean('chargement_supprime')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('bon_livraisons', function (Blueprint $table) {
            $table->dropColumn('chargement_supprime');
        });
    }
};
