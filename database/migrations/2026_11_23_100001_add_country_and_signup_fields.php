<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-11-23: free self-service sign-up for francophone markets.
 * - societe.country_code drives currency / VAT / identifier labels (App\Support\Countries).
 * - projects keep where and how a company signed up (data for the owner).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('societe', function (Blueprint $t) {
            $t->string('country_code', 2)->nullable()->after('country');
        });
        DB::table('societe')->update(['country_code' => 'MA']);

        Schema::table('projects', function (Blueprint $t) {
            $t->string('country_code', 2)->nullable()->after('name');
            $t->string('contact_phone', 40)->nullable()->after('country_code');
            $t->string('signup_source', 20)->nullable()->after('contact_phone'); // 'admin' | 'self'
        });
    }

    public function down(): void
    {
        Schema::table('projects', fn (Blueprint $t) => $t->dropColumn(['country_code', 'contact_phone', 'signup_source']));
        Schema::table('societe', fn (Blueprint $t) => $t->dropColumn('country_code'));
    }
};
