<?php

namespace App\Support;

use App\Models\AiScan;
use App\Models\Facture;
use App\Models\Project;

/**
 * Plans (2026-11-27). Free: 5 invoices a month, no AI. Pro: unlimited invoices, AI within the monthly quotas
 * (config services.anthropic.*_per_month). Premium: everything unlimited. A paid plan past its end date counts as Free.
 * Payment is manual for now (WhatsApp / bank transfer, activated by the superadmin); PayPal later.
 */
class Plans
{
    public const LABELS = ['free' => 'Free', 'pro' => 'Pro', 'premium' => 'Premium'];

    public const FREE_INVOICES_PER_MONTH = 5;

    public const TRIAL_MONTHS = 3;

    /** price per period, in DH for Moroccan companies, in € otherwise */
    public const PRICES = [
        'pro' => ['trimestre' => ['MAD' => 249, 'EUR' => 25], 'annee' => ['MAD' => 799, 'EUR' => 79]],
        'premium' => ['trimestre' => ['MAD' => 449, 'EUR' => 45], 'annee' => ['MAD' => 1499, 'EUR' => 149]],
    ];

    public const PERIODS = ['trimestre' => ['label' => '3 mois', 'months' => 3], 'annee' => ['label' => '1 an', 'months' => 12]];

    public static function limits(string $plan): array
    {
        return match ($plan) {
            'premium' => ['factures' => null, 'copilot' => null, 'scans' => null],
            'pro' => ['factures' => null, 'copilot' => (int) config('services.anthropic.copilot_per_month', 150), 'scans' => (int) config('services.anthropic.scans_per_month', 30)],
            default => ['factures' => self::FREE_INVOICES_PER_MONTH, 'copilot' => 0, 'scans' => 0],
        };
    }

    /** plan really in force for a company */
    public static function effective(?Project $p): string
    {
        if (! $p) {
            return 'free';
        }
        $plan = array_key_exists((string) $p->plan, self::LABELS) ? $p->plan : 'free';

        return $plan !== 'free' && $p->plan_ends_at && $p->plan_ends_at->isPast() ? 'free' : $plan;
    }

    /** plan of the signed-in user's company (the demo and the platform superadmin see everything) */
    public static function current(): string
    {
        $u = auth()->user();
        if (! $u || $u->isDemo() || ($u->isSuperAdmin() && ! $u->project_id)) {
            return 'premium';
        }

        return self::effective($u->project);
    }

    public static function limit(string $key): ?int
    {
        return self::limits(self::current())[$key];
    }

    public static function facturesThisMonth(): int
    {
        return Facture::where('created_at', '>=', now()->startOfMonth())->count();
    }

    public static function canCreateFacture(): bool
    {
        $l = self::limit('factures');

        return $l === null || self::facturesThisMonth() < $l;
    }

    public static function factureLimitMessage(): string
    {
        return 'Le plan Free comprend '.self::FREE_INVOICES_PER_MONTH.' factures par mois : elles sont toutes utilisées. Passez au plan Pro ou Premium pour des factures illimitées (Administration › Abonnement).';
    }

    public static function aiNotIncluded(string $what): string
    {
        return $what.' n’est pas inclus dans le plan Free. Passez au plan Pro ou Premium (Administration › Abonnement).';
    }

    public static function usage(): array
    {
        return [
            'factures' => self::facturesThisMonth(),
            'copilot' => AiScan::usedThisMonth('copilot'),
            'scans' => AiScan::usedThisMonth(),
        ];
    }

    public static function currency(): string
    {
        return strtoupper((string) \App\Models\Societe::current()?->currency) === 'MAD' ? 'MAD' : 'EUR';
    }

    public static function price(string $plan, string $period, ?string $currency = null): ?string
    {
        $currency ??= self::currency();
        $v = self::PRICES[$plan][$period][$currency] ?? null;

        return $v === null ? null : ($currency === 'MAD' ? number_format($v, 0, ',', ' ').' DH' : number_format($v, 0, ',', ' ').' €');
    }
}
