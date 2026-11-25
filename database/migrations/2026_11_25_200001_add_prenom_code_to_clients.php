<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Clients (2026-11-25): first name + client code (CL-0001…, unique per company).
// Existing clients get a code in creation order, per company.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'prenom')) {
                $table->string('prenom', 100)->nullable()->after('name');
            }
            if (! Schema::hasColumn('clients', 'code')) {
                $table->string('code', 50)->nullable()->after('id');
            }
        });

        $counters = [];
        DB::table('clients')->whereNull('code')->orderBy('id')->select(['id', 'project_id', 'is_demo'])
            ->chunkById(500, function ($rows) use (&$counters) {
                foreach ($rows as $r) {
                    $k = ($r->project_id ?? 'x').'-'.(int) $r->is_demo;
                    $counters[$k] = ($counters[$k] ?? 0) + 1;
                    DB::table('clients')->where('id', $r->id)->update(['code' => 'CL-'.str_pad((string) $counters[$k], 4, '0', STR_PAD_LEFT)]);
                }
            });

        Schema::table('clients', function (Blueprint $table) {
            $table->unique(['project_id', 'is_demo', 'code'], 'clients_tenant_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_tenant_code_unique');
            $table->dropColumn(['prenom', 'code']);
        });
    }
};
