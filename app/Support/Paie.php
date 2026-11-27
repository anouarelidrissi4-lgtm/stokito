<?php

namespace App\Support;

use App\Models\Depense;
use App\Models\Employe;
use App\Models\EmployeAvance;
use App\Models\EmployePaie;
use App\Models\EmployePresence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Pay of a worker (module Personnel, 2026-11-28). Not a legal payslip (no CNSS / IR): a record of what was paid.
 *  - par jour    : days worked × daily rate (present = 1, half day = 0.5)
 *  - par semaine : weekly rate × weeks in the period − absences × (rate / 6)
 *  - par mois    : monthly rate × share of the month − absences × (rate / 26)
 * Days off (congé) and public holidays (férié) do not reduce a weekly or monthly pay.
 * Advances not yet deducted and the monthly loan repayments are taken off; the net paid becomes a "salaires" expense.
 */
class Paie
{
    public const JOURS_MOIS = 26;

    public const JOURS_SEMAINE = 6;

    /** Default period: the current month (monthly), the current week (weekly), or from the day after the last pay (daily). */
    public static function periodeParDefaut(Employe $e, ?Carbon $today = null): array
    {
        $t = ($today ?? now())->copy()->startOfDay();
        $last = $e->paies()->max('periode_fin');
        $last = $last ? Carbon::parse($last)->startOfDay() : null;
        if ($e->type_paie === 'mois' || $e->type_paie === 'semaine') {
            // the current month / week, or the next one once it has been paid
            $ref = $last && $last->gte($t) ? $last->copy()->addDay() : $t->copy();
            if ($e->type_paie === 'mois') {
                return [$ref->copy()->startOfMonth(), $ref->copy()->endOfMonth()->startOfDay()];
            }

            return [$ref->copy()->startOfWeek(Carbon::MONDAY), $ref->copy()->endOfWeek(Carbon::SUNDAY)->startOfDay()];
        }
        // by the day: from the day after the last pay (or the 1st of the month) to today; nothing left when already paid up to today
        $debut = $last ? $last->copy()->addDay() : $t->copy()->startOfMonth();

        return [$debut, $debut->gt($t) ? $debut->copy() : $t];
    }

    /** Attendance counts over a period: ['P' => n, 'D' => n, 'A' => n, 'C' => n, 'F' => n]. */
    public static function compteur(Employe $e, Carbon $debut, Carbon $fin): array
    {
        $n = array_fill_keys(array_keys(EmployePresence::STATUTS), 0);
        foreach (EmployePresence::where('employe_id', $e->id)->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])->pluck('statut') as $s) {
            if (isset($n[$s])) {
                $n[$s]++;
            }
        }

        return $n;
    }

    public static function joursTravailles(array $n): float
    {
        return $n['P'] + $n['D'] * 0.5;
    }

    /** Pay before bonus and deductions. */
    public static function base(Employe $e, Carbon $debut, Carbon $fin, array $n): float
    {
        $taux = (float) $e->taux;
        $absences = $n['A'] + $n['D'] * 0.5;
        $jours = (int) round(abs($debut->diffInDays($fin))) + 1;

        return match ($e->type_paie) {
            'mois' => round(max(0, $taux * min(1, $jours / $debut->daysInMonth) - $absences * $taux / self::JOURS_MOIS), 2),
            'semaine' => round(max(0, $taux * ($jours / 7) - $absences * $taux / self::JOURS_SEMAINE), 2),
            default => round(self::joursTravailles($n) * $taux, 2),
        };
    }

    /** Monthly loan repayments due on this pay, oldest loan first: [credit_id => amount]. */
    public static function retenuesCredit(Employe $e): array
    {
        $out = [];
        foreach ($e->credits()->orderBy('date')->orderBy('id')->get() as $c) {
            $r = min((float) $c->mensualite, $c->restant());
            if ($r > 0) {
                $out[$c->id] = round($r, 2);
            }
        }

        return $out;
    }

    /** Everything the pay form shows, for a period. */
    public static function calcul(Employe $e, Carbon $debut, Carbon $fin): array
    {
        $n = self::compteur($e, $debut, $fin);
        $avances = (float) EmployeAvance::where('employe_id', $e->id)->whereNull('paie_id')->whereDate('date', '<=', $fin->toDateString())->sum('montant');
        $credit = self::retenuesCredit($e);

        return [
            'compteur' => $n,
            'jours' => self::joursTravailles($n),
            'base' => self::base($e, $debut, $fin, $n),
            'avances' => round($avances, 2),
            'retenue_credit' => round(array_sum($credit), 2),
        ];
    }

    /**
     * Records a pay. $d = [debut, fin, jours, base, prime, retenue_credit, date_paiement, mode_paiement, tresorerie_compte_id, notes].
     * Takes every advance not yet deducted up to the end of the period, spreads the loan repayment over the loans (oldest first),
     * and writes the net as a "salaires" expense (with its treasury movement when an account is chosen).
     */
    public static function payer(Employe $e, array $d): EmployePaie
    {
        return DB::transaction(function () use ($e, $d) {
            $fin = Carbon::parse($d['fin']);
            $avances = EmployeAvance::where('employe_id', $e->id)->whereNull('paie_id')->whereDate('date', '<=', $fin->toDateString())->get();
            $totalAvances = round((float) $avances->sum('montant'), 2);

            $retenue = round(max(0, (float) ($d['retenue_credit'] ?? 0)), 2);
            $alloc = [];
            $left = $retenue;
            foreach ($e->credits()->orderBy('date')->orderBy('id')->get() as $c) {
                if ($left <= 0) {
                    break;
                }
                $r = round(min($left, $c->restant()), 2);
                if ($r > 0) {
                    $alloc[$c->id] = $r;
                    $c->update(['rembourse' => round((float) $c->rembourse + $r, 2)]);
                    $left = round($left - $r, 2);
                }
            }
            $retenue = round($retenue - $left, 2);           // never more than what is still owed

            $base = round((float) $d['base'], 2);
            $prime = round(max(0, (float) ($d['prime'] ?? 0)), 2);
            $net = round($base + $prime - $totalAvances - $retenue, 2);

            $paie = EmployePaie::create([
                'employe_id' => $e->id, 'periode_debut' => $d['debut'], 'periode_fin' => $d['fin'], 'jours' => (float) ($d['jours'] ?? 0),
                'base' => $base, 'prime' => $prime, 'avances' => $totalAvances, 'retenue_credit' => $retenue, 'credit_allocation' => $alloc ?: null,
                'net' => $net, 'date_paiement' => $d['date_paiement'], 'mode_paiement' => $d['mode_paiement'] ?? 'especes',
                'tresorerie_compte_id' => $d['tresorerie_compte_id'] ?: null, 'notes' => $d['notes'] ?? null,
            ]);
            EmployeAvance::whereIn('id', $avances->pluck('id'))->update(['paie_id' => $paie->id]);

            if ($net > 0) {
                $dep = Depense::create([
                    'date' => $d['date_paiement'], 'categorie' => 'salaires',
                    'libelle' => 'Salaire — '.$e->nom.' ('.Carbon::parse($d['debut'])->format('d/m').' au '.$fin->format('d/m/Y').')',
                    'montant_ttc' => $net, 'montant_tva' => 0, 'mode_paiement' => self::modeDepense($d['mode_paiement'] ?? 'especes'),
                    'tresorerie_compte_id' => $d['tresorerie_compte_id'] ?: null, 'user_id' => auth()->id(),
                ]);
                $dep->syncTresorerie();
                $paie->update(['depense_id' => $dep->id]);
            }

            return $paie;
        });
    }

    /** An advance is paid now: it is a "salaires" expense right away, then deducted from the next pay. */
    public static function avance(Employe $e, array $d): EmployeAvance
    {
        return DB::transaction(function () use ($e, $d) {
            $a = EmployeAvance::create(['employe_id' => $e->id, 'date' => $d['date'], 'montant' => round((float) $d['montant'], 2),
                'mode_paiement' => $d['mode_paiement'] ?? 'especes', 'tresorerie_compte_id' => $d['tresorerie_compte_id'] ?: null, 'notes' => $d['notes'] ?? null]);
            $dep = Depense::create([
                'date' => $d['date'], 'categorie' => 'salaires', 'libelle' => 'Avance sur salaire — '.$e->nom,
                'montant_ttc' => $a->montant, 'montant_tva' => 0, 'mode_paiement' => self::modeDepense($a->mode_paiement),
                'tresorerie_compte_id' => $a->tresorerie_compte_id, 'user_id' => auth()->id(),
            ]);
            $dep->syncTresorerie();
            $a->update(['depense_id' => $dep->id]);

            return $a;
        });
    }

    public const MODES = ['especes' => 'Espèces', 'virement' => 'Virement', 'cheque' => 'Chèque'];

    private static function modeDepense(string $m): string
    {
        return array_key_exists($m, Depense::MODES) ? $m : 'especes';
    }
}
