<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reglements_fournisseurs', function (Blueprint $table) {
            $table->string('banque', 100)->nullable()->after('numero_cheque');
            $table->date('date_echeance')->nullable()->after('banque');
            $table->string('tireur', 120)->nullable()->after('date_echeance');
        });
    }

    public function down(): void
    {
        Schema::table('reglements_fournisseurs', function (Blueprint $table) {
            $table->dropColumn(['banque', 'date_echeance', 'tireur']);
        });
    }
};
