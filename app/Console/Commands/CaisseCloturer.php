<?php

namespace App\Console\Commands;

use App\Models\CaisseCloture;
use App\Models\CaisseTicket;
use App\Models\User;
use App\Support\Caisse;
use App\Support\CaisseException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Automatic Z closures (2026-11-25), run every night: one daily closure per past day that still has
 * open tickets, then the monthly closure of every finished month and the yearly closure of every
 * finished year. Runs as the company's admin so the tenant scope applies; logs out after each company.
 */
class CaisseCloturer extends Command
{
    protected $signature = 'caisse:cloturer';

    protected $description = 'Clôtures automatiques de caisse (jour, mois, année)';

    public function handle(): int
    {
        $companies = CaisseTicket::withoutGlobalScopes()->select('project_id', 'is_demo')->distinct()->get();
        foreach ($companies as $co) {
            $owner = User::where('project_id', $co->project_id)->where('is_demo', (bool) $co->is_demo)
                ->orderByRaw("role = 'admin' desc")->orderBy('id')->first();
            if (! $owner) {
                continue;
            }
            Auth::setUser($owner);
            try {
                $this->closeCompany();
            } catch (\Throwable $e) {
                report($e);
                $this->error("Projet {$co->project_id}: ".$e->getMessage());
            } finally {
                Auth::forgetUser();
            }
        }

        return self::SUCCESS;
    }

    protected function closeCompany(): void
    {
        $today = now()->startOfDay();
        // days: close each past day separately
        while (($first = Caisse::nonClotures($today)->first())) {
            $day = Carbon::parse($first->created_at)->startOfDay();
            Caisse::cloturerJour($day->copy()->addDay(), $day->toDateString());
        }
        // months before the current one
        $months = CaisseCloture::where('type', 'jour')->where('periode', '<', now()->format('Y-m').'-01')->pluck('periode')
            ->map(fn ($p) => substr($p, 0, 7))->unique();
        foreach ($months as $m) {
            if (! CaisseCloture::where('type', 'mois')->where('periode', $m)->exists()) {
                $this->tryClose('mois', $m);
            }
        }
        // years before the current one
        $years = CaisseCloture::where('type', 'mois')->where('periode', '<', now()->format('Y').'-01')->pluck('periode')
            ->map(fn ($p) => substr($p, 0, 4))->unique();
        foreach ($years as $y) {
            if (! CaisseCloture::where('type', 'annee')->where('periode', $y)->exists()) {
                $this->tryClose('annee', $y);
            }
        }
    }

    protected function tryClose(string $type, string $periode): void
    {
        try {
            Caisse::cloturerPeriode($type, $periode);
        } catch (CaisseException $e) {
            $this->warn($e->getMessage());
        }
    }
}
