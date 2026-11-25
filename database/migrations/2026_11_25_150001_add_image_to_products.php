<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 2026-11-25: optional product photo (shown on the till buttons). */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->string('image_path')->nullable()->after('name'));
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('image_path'));
    }
};
