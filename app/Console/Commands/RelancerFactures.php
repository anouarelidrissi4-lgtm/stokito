<?php

namespace App\Console\Commands;

use App\Mail\DocumentMail;
use App\Models\Facture;
use App\Models\Societe;
use App\Models\User;
use App\Support\DocumentPdf;
use App\Support\DocumentSender;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Automatic payment reminders by email (2026-11-24), only for companies that switched on
 * "Relances automatiques" in Ma Société. Level 1 at due date + 7 days, level 2 at + 30 days.
 * Each level is sent once per invoice; only invoices entering that window in the last 7 days
 * are reminded, so switching the option on never floods customers with very old invoices.
 */
class RelancerFactures extends Command
{
    protected $signature = 'factures:relancer {--dry-run : list what would be sent, send nothing}';

    protected $description = 'Send automatic payment reminders (J+7, J+30) for overdue invoices';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $projects = DB::table('societe')->where('relances_auto', true)->where('is_demo', false)->pluck('project_id')->filter()->unique();
        $today = now()->startOfDay();
        $sent = 0;

        foreach ($projects as $projectId) {
            $user = User::where('project_id', $projectId)->where('is_demo', false)->where('status', 'active')
                ->orderByRaw("role = 'admin' desc")->orderBy('id')->first();
            if (! $user) {
                continue;
            }
            Auth::setUser($user); // tenant scope: this project's real data only
            $s = Societe::current();

            foreach ([1 => [7, 14], 2 => [30, 37]] as $niveau => [$from, $to]) {
                $factures = Facture::with('client')
                    ->where('relance_niveau', '<', $niveau)
                    ->whereNotNull('due_date')
                    ->whereBetween('due_date', [$today->copy()->subDays($to), $today->copy()->subDays($from)])
                    ->whereRaw('total_ttc - montant_paye > 0.009')
                    ->where('status', '!=', 'payee')
                    ->limit(50)->get();

                foreach ($factures as $f) {
                    $email = trim((string) ($f->client->email ?? ''));
                    $txt = DocumentSender::relance($f, $niveau);
                    if (! $txt || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }
                    $this->line("project {$projectId} {$f->numero} level {$niveau} -> client #{$f->client_id}".($dry ? ' (dry-run)' : ''));
                    if ($dry) {
                        continue;
                    }
                    try {
                        Mail::to($email)->send(new DocumentMail($txt['sujet'], $txt['message'], $s->name ?: 'Stokito', $s->email ?: $user->email,
                            DocumentPdf::facture($f)->output(), $f->numero.'.pdf'));
                        $f->update(['relance_niveau' => $niveau, 'relance_at' => now()]);
                        $sent++;
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }
            Auth::forgetUser();
        }

        $this->info("Relances envoyées : {$sent}");

        return self::SUCCESS;
    }
}
