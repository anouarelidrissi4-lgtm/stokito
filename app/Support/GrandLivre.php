<?php

namespace App\Support;

use App\Models\Client;
use Illuminate\Support\Carbon;

/**
 * Grand livre client (2026-11-27): every sale and every payment of one customer, with a running balance.
 * Same totals as Client::solde(): invoices (not till tickets) + delivery notes not invoiced, minus payments and credit notes.
 * Balance = crédit - débit, so a customer who owes money has a negative balance (like the user's former software).
 */
class GrandLivre
{
    public static function client(Client $client, ?string $from = null, ?string $to = null, bool $hidePaid = false): array
    {
        $rows = [];
        $hiddenFactures = [];
        $hiddenBls = [];

        foreach ($client->factures()->whereNull('caisse_ticket_id')->get() as $f) {
            $paid = $f->resteAPayer() <= 0.009;
            if ($hidePaid && $paid) {
                $hiddenFactures[] = $f->id;

                continue;
            }
            $rows[] = ['date' => $f->date, 'element' => 'Facture', 'ref' => $f->numero, 'debit' => (float) $f->total_ttc, 'credit' => 0.0, 'kind' => 'facture', 'id' => $f->id, 'sort' => 0];
        }
        foreach ($client->bonLivraisons()->whereDoesntHave('facture')->get() as $b) {
            $paid = (float) $b->total > 0 && $b->resteAPayer() <= 0.009;
            if ($hidePaid && $paid) {
                $hiddenBls[] = $b->id;

                continue;
            }
            $rows[] = ['date' => $b->date, 'element' => 'Bon de livraison', 'ref' => $b->numero, 'debit' => (float) $b->total, 'credit' => 0.0, 'kind' => 'bl', 'id' => $b->id, 'sort' => 0];
        }
        foreach ($client->reglements()->with(['facture', 'bonLivraison'])->get() as $r) {
            if ($hidePaid && (($r->facture_id && in_array($r->facture_id, $hiddenFactures, true)) || (! $r->facture_id && $r->bon_livraison_id && in_array($r->bon_livraison_id, $hiddenBls, true)))) {
                continue;
            }
            $pre = fn ($p, $n) => preg_match('/^'.$p.'/i', (string) $n) ? $n : $p.' '.$n;   // no "FAC FAC-00006"
            $ref = $r->facture ? $pre('FAC', $r->facture->numero) : ($r->bonLivraison ? $pre('BL', $r->bonLivraison->numero) : '—');
            $rows[] = ['date' => $r->date, 'element' => 'Règlement '.$r->modeLabel(), 'ref' => $ref, 'debit' => 0.0, 'credit' => (float) $r->montant, 'kind' => 'reglement', 'id' => $r->id, 'sort' => 1,
                'detail' => trim(implode(' · ', array_filter([$r->numero_cheque ? 'n° '.$r->numero_cheque : null, $r->banque]))) ?: null];
        }
        foreach ($client->avoirs()->get() as $a) {
            if ($hidePaid && $a->facture_id && in_array($a->facture_id, $hiddenFactures, true)) {
                continue;
            }
            $rows[] = ['date' => $a->date, 'element' => 'Avoir', 'ref' => $a->numero, 'debit' => 0.0, 'credit' => (float) $a->montant, 'kind' => 'avoir', 'id' => $a->id, 'sort' => 2];
        }

        usort($rows, fn ($x, $y) => [($x['date']?->format('Y-m-d') ?? ''), $x['sort'], $x['id']] <=> [($y['date']?->format('Y-m-d') ?? ''), $y['sort'], $y['id']]);

        $from = $from ? Carbon::parse($from)->startOfDay() : null;
        $to = $to ? Carbon::parse($to)->endOfDay() : null;
        $report = 0.0;
        $period = [];
        foreach ($rows as $row) {
            $d = $row['date'];
            if ($from && $d && $d->lt($from)) {
                $report += $row['credit'] - $row['debit'];

                continue;
            }
            if ($to && $d && $d->gt($to)) {
                continue;
            }
            $period[] = $row;
        }
        $balance = $report;
        foreach ($period as &$row) {
            $balance += $row['credit'] - $row['debit'];
            $row['balance'] = round($balance, 2);
        }
        unset($row);

        $debits = array_sum(array_column($period, 'debit'));
        $credits = array_sum(array_column($period, 'credit'));

        return [
            'rows' => $period,
            'report' => round($report, 2),
            'hasReport' => $from !== null,
            'documents' => count(array_filter($period, fn ($r) => in_array($r['kind'], ['facture', 'bl'], true))),
            'debits' => round($debits, 2),
            'credits' => round($credits, 2),
            'soldePeriode' => round($report + $credits - $debits, 2),
            'soldeComptable' => round(-$client->solde(), 2),
        ];
    }
}
