<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** One loading slip per delivery note, with ordered / loaded quantity per product (2026-11-27). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bons_de_chargement', function (Blueprint $t) {
            $t->foreignId('bon_livraison_id')->nullable()->after('numero')->constrained('bon_livraisons')->cascadeOnDelete();
            $t->string('commercial', 120)->nullable()->after('chauffeur');
        });
        Schema::create('bon_chargement_lignes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bon_de_chargement_id')->constrained('bons_de_chargement')->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $t->string('designation');
            $t->string('unite', 40)->nullable();
            $t->decimal('quantite_commandee', 14, 3)->default(0);
            $t->decimal('quantite_chargee', 14, 3)->nullable();
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_chargement_lignes');
        Schema::table('bons_de_chargement', function (Blueprint $t) {
            $t->dropConstrainedForeignId('bon_livraison_id');
            $t->dropColumn('commercial');
        });
    }
};
