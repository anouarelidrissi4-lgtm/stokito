<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Per-user interface preferences (2026-11-25): first use = columns of the products list.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'ui_prefs')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('ui_prefs')->nullable()->after('permissions');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'ui_prefs')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('ui_prefs');
            });
        }
    }
};
