<?php

namespace App\Livewire\Concerns;

/**
 * Gates a Livewire component (or a single action inside one) behind a
 * granular permission key from App\Support\Permissions::ALL. Admins and
 * superadmins always pass (see User::hasPermission()).
 */
trait RequiresPermission
{
    protected function requirePermission(string $key): void
    {
        abort_unless(auth()->user()->hasPermission($key), 403);
    }
}
