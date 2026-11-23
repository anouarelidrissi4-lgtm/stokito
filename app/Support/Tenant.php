<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Helpers that keep every project (and the demo) independent from the others.
 * Added 2026-11-22: document numbers and unique fields used to be global, so a
 * second project (or the demo) collided with the first one's numbers -> error 500.
 */
class Tenant
{
    /** Unique rule limited to the current user's project + demo flag. */
    public static function unique(string $table, string $column, $ignoreId = null): Unique
    {
        $user = auth()->user();

        return Rule::unique($table, $column)
            ->where(fn ($q) => $q->where('project_id', $user?->project_id)->where('is_demo', (bool) $user?->is_demo))
            ->ignore($ignoreId);
    }

    /**
     * Next document number for the current project, e.g. DEV-00006.
     * Based on the highest existing number (not on count()), so deleting a
     * document never makes two documents share a number.
     */
    public static function nextNumero(string $modelClass, string $prefix, int $pad = 5): string
    {
        $max = $modelClass::query()
            ->where('numero', 'like', $prefix.'%')
            ->pluck('numero')
            ->map(fn ($n) => (int) preg_replace('/\D/', '', substr($n, strlen($prefix))))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($max + 1), $pad, '0', STR_PAD_LEFT);
    }

    /**
     * Delete a row; if other documents still point to it, show a clear message
     * instead of crashing with an SQL foreign-key error.
     */
    public static function tryDelete(Model $model): bool
    {
        try {
            DB::transaction(fn () => $model->delete());

            return true;
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '23000') {
                session()->flash('error', "Suppression impossible : cet élément est utilisé dans d'autres documents (devis, BL, factures, règlements...). Supprimez ou modifiez d'abord ces documents.");

                return false;
            }
            throw $e;
        }
    }
}
