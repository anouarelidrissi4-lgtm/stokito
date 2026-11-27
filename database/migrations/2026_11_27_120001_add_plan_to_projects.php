<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Plans Free / Pro / Premium (2026-11-27). Existing companies start with the 3-month Premium trial, like new ones. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $t) {
            $t->string('plan', 20)->default('free')->after('is_active');
            $t->timestamp('plan_ends_at')->nullable()->after('plan');
            $t->string('plan_period', 20)->nullable()->after('plan_ends_at');
            $t->boolean('plan_trial')->default(false)->after('plan_period');
        });
        DB::table('projects')->update(['plan' => 'premium', 'plan_ends_at' => now()->addMonths(3), 'plan_trial' => true]);
    }

    public function down(): void
    {
        Schema::table('projects', fn (Blueprint $t) => $t->dropColumn(['plan', 'plan_ends_at', 'plan_period', 'plan_trial']));
    }
};
