<?php
namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Isolates each project's data (multi-tenant) and demo data from everything else.
 *
 * - Every row belongs to exactly one project (project_id) and is either real or
 *   demo data (is_demo). A logged-in user only ever sees/creates rows matching
 *   BOTH their own project_id and their own is_demo flag.
 * - A superadmin (is_superadmin = true, project_id = null) has no project data
 *   of their own; this scope simply returns nothing for them on these tables,
 *   which is correct since their job is managing projects, not business data.
 */
trait ScopedToDemoMode
{
    protected static function bootScopedToDemoMode(): void
    {
        static::addGlobalScope('demo_mode', function (Builder $builder) {
            $table = $builder->getModel()->getTable();
            $user = auth()->user();

            $isDemoUser = $user && (bool) $user->is_demo;
            $builder->where($table.'.is_demo', $isDemoUser);

            if ($user && $user->project_id) {
                $builder->where($table.'.project_id', $user->project_id);
            } else {
                // No project (guest, or a superadmin with no project of their own): see nothing.
                $builder->whereRaw('1 = 0');
            }
        });

        static::creating(function ($model) {
            $user = auth()->user();

            if (is_null($model->is_demo)) {
                $model->is_demo = $user ? (bool) $user->is_demo : false;
            }

            if (is_null($model->project_id) && $user && $user->project_id) {
                $model->project_id = $user->project_id;
            }
        });
    }
}
