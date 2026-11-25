<?php

namespace App\Console\Commands;

use App\Models\DepenseRecurrente;
use App\Models\Societe;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

/**
 * Daily (2026-11-26): creates the expenses of every recurring expense whose date has come.
 * Each template is handled in its company's context (its creator, or an admin of the company),
 * so the expense and its treasury movement belong to the right company.
 */
class GenererDepensesRecurrentes extends Command
{
    protected $signature = 'depenses:recurrentes';

    protected $description = 'Crée les dépenses récurrentes arrivées à échéance';

    public function handle(): int
    {
        $total = 0;
        $due = DepenseRecurrente::withoutGlobalScopes()->where('actif', true)->whereDate('prochaine_date', '<=', today())->get();
        foreach ($due as $r) {
            $user = User::find($r->user_id);
            if (! $user || $user->project_id !== $r->project_id || (bool) $user->is_demo !== (bool) $r->is_demo) {
                $user = User::where('project_id', $r->project_id)->where('is_demo', $r->is_demo)->where('role', 'admin')->orderBy('id')->first();
            }
            if (! $user) {
                continue;
            }
            Auth::setUser($user);
            Societe::flushCurrent();
            try {
                $model = DepenseRecurrente::find($r->id);
                $total += $model ? $model->genererEcheances() : 0;
            } catch (\Throwable $e) {
                report($e);
            } finally {
                Auth::forgetUser();
                Societe::flushCurrent();
            }
        }
        $this->info($total.' dépense(s) créée(s).');

        return self::SUCCESS;
    }
}
