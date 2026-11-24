<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 2026-11-24: AI invoice scanner (usage log per company) + supplier invoice number on purchase orders. */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_scans', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id')->nullable()->index();
            $t->boolean('is_demo')->default(false);
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('kind', 30)->default('facture_fournisseur');
            $t->string('status', 20);
            $t->unsignedInteger('input_tokens')->default(0);
            $t->unsignedInteger('output_tokens')->default(0);
            $t->decimal('cost_usd', 8, 4)->default(0);
            $t->text('error')->nullable();
            $t->timestamps();
        });
        Schema::table('commande_fournisseurs', fn (Blueprint $t) => $t->string('reference_fournisseur')->nullable()->after('numero'));
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_scans');
        Schema::table('commande_fournisseurs', fn (Blueprint $t) => $t->dropColumn('reference_fournisseur'));
    }
};
