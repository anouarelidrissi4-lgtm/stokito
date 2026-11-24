<?php

namespace App\Models\Concerns;

use LogicException;

/**
 * Caisse records can only be inserted (2026-11-25, French cash-register rules). Demo rows stay
 * deletable for the nightly demo reset. The database triggers enforce the same rule underneath.
 */
trait AppendOnly
{
    protected static function bootAppendOnly(): void
    {
        $refuse = function ($model) {
            if (! $model->is_demo) {
                throw new LogicException('Caisse : un enregistrement validé ne peut être ni modifié ni supprimé.');
            }
        };
        static::updating($refuse);
        static::deleting($refuse);
    }
}
