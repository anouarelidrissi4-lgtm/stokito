<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-11-24 schema drift fix: production got societe.is_demo outside any migration (the 2026-10-19
 * is_demo migration skipped this table), so a fresh install or the test database had no such column.
 * No-op where the column already exists (production).
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('societe', 'is_demo')) {
            Schema::table('societe', function (Blueprint $t) {
                $t->boolean('is_demo')->default(false)->index();
            });
        }
    }

    public function down(): void
    {
        // left in place on purpose: production had this column before the migration existed
    }
};
