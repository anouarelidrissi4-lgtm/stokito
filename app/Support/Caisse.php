<?php

namespace App\Support;

use App\Models\CaisseCloture;
use App\Models\CaisseJournal;
use App\Models\CaissePaiement;
use App\Models\CaisseTicket;
use App\Models\CaisseTicketLigne;
use App\Models\Client;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Caisse (point of sale), 2026-11-25. Built for the French "logiciel de caisse sécurisé" rules
 * (art. 286 I 3° bis CGI — inaltérabilité, sécurisation, conservation, archivage):
 *  - tickets are numbered without gaps per company and can never be changed: a mistake is fixed
 *    with a cancellation ticket (negative amounts) that points to the original;
 *  - every ticket, closure and journal event stores an HMAC-SHA256 of its content chained to the
 *    previous one (prev_hash), so any later change is detected by verifier();
 *  - Z closures (jour, mois, annee) freeze the totals and keep the perpetual grand totals;
 *  - prints, duplicates, cancellations and closures are written to the event journal.
 */
class Caisse
{
    public const VERSION = '1.0';

    public const GENESIS = '0000000000000000000000000000000000000000000000000000000000000000';

    public const MODES = ['especes' => 'Espèces', 'carte' => 'Carte bancaire', 'cheque' => 'Chèque', 'autre' => 'Autre'];

    // ------------------------------------------------------------------ signing

    public static function sign(array $payload): string
    {
        return hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), (string) config('app.key'));
    }

    protected static function m($v): string
    {
        return number_format((float) $v, 2, '.', '');
    }

    protected static function q($v): string
    {
        return number_format((float) $v, 3, '.', '');
    }

    protected static function i($v): ?int
    {
        return $v === null || $v === '' ? null : (int) $v;
    }

    public static function ticketPayload(CaisseTicket $t, iterable $lignes, iterable $paiements): array
    {
        return ['T', (int) $t->sequence, $t->numero, $t->type, self::i($t->annule_ticket_id), Carbon::parse($t->created_at)->format('Y-m-d H:i:s'),
            self::i($t->user_id), self::i($t->client_id), self::m($t->total_ht), self::m($t->total_tva), self::m($t->total_ttc),
            self::m($t->montant_recu), self::m($t->rendu), self::tvaCanon($t->tva_detail),
            collect($lignes)->map(fn ($l) => [self::i($l->product_id), (string) $l->reference, (string) $l->designation, self::q($l->quantity),
                self::m($l->prix_unitaire_ttc), self::m($l->remise_pct), self::m($l->tva_rate), self::m($l->total_ttc), self::m($l->total_ht)])->values()->all(),
            collect($paiements)->map(fn ($p) => [(string) $p->mode, self::m($p->montant)])->values()->all(),
            $t->prev_hash];
    }

    public static function cloturePayload(CaisseCloture $c): array
    {
        return ['Z', $c->type, (int) $c->sequence, $c->periode, self::i($c->first_seq), self::i($c->last_seq), (int) $c->nb_tickets, (int) $c->nb_annulations,
            self::m($c->total_ht), self::m($c->total_tva), self::m($c->total_ttc), self::m($c->cumul_ttc), self::m($c->cumul_abs_ttc),
            self::tvaCanon($c->tva_detail), collect($c->paiements ?? [])->map(fn ($v) => self::m($v))->sortKeys()->all(),
            Carbon::parse($c->created_at)->format('Y-m-d H:i:s'), self::i($c->user_id), $c->prev_hash];
    }

    /** VAT breakdown in a fixed shape (MySQL JSON columns reorder object keys, so never sign raw JSON). */
    protected static function tvaCanon($detail): array
    {
        return collect($detail ?? [])->map(fn ($d) => [self::m($d['taux']), self::m($d['ht']), self::m($d['tva']), self::m($d['ttc'])])
            ->sortBy(fn ($d) => $d[0])->values()->all();
    }

    /** Arrays with sorted keys at every level (stable JSON whatever the database did to the key order). */
    protected static function canon($v)
    {
        if (! is_array($v)) {
            return $v;
        }
        if (! array_is_list($v)) {
            ksort($v);
        }

        return array_map([self::class, 'canon'], $v);
    }

    public static function journalPayload(CaisseJournal $j): array
    {
        return ['J', (int) $j->sequence, $j->code, $j->libelle, Carbon::parse($j->created_at)->format('Y-m-d H:i:s'), self::i($j->user_id),
            self::canon($j->data ?? []), $j->prev_hash];
    }

    // ------------------------------------------------------------------ totals

    /**
     * Lines: [product_id, reference, designation, quantity, prix_unitaire_ttc, remise_pct, tva_rate].
     * Catalogue prices are TTC; HT and VAT are computed per rate on the TTC total of that rate.
     */
    public static function compute(array $cart): array
    {
        $lines = [];
        $byRate = [];
        foreach ($cart as $l) {
            $qty = round((float) ($l['quantity'] ?? 0), 3);
            $pu = round((float) ($l['prix_unitaire_ttc'] ?? 0), 2);
            $rem = min(100, max(0, round((float) ($l['remise_pct'] ?? 0), 2)));
            $rate = round((float) ($l['tva_rate'] ?? 0), 2);
            $ttc = round($qty * $pu * (1 - $rem / 100), 2);
            $lines[] = $l + ['quantity' => $qty, 'prix_unitaire_ttc' => $pu, 'remise_pct' => $rem, 'tva_rate' => $rate,
                'total_ttc' => $ttc, 'total_ht' => round($ttc / (1 + $rate / 100), 2)];
            $k = self::m($rate);
            $byRate[$k] = round(($byRate[$k] ?? 0) + $ttc, 2);
        }
        ksort($byRate);
        $detail = [];
        $ht = $tva = $ttc = 0.0;
        foreach ($byRate as $rate => $t) {
            $h = round($t / (1 + (float) $rate / 100), 2);
            $detail[] = ['taux' => (float) $rate, 'ht' => $h, 'tva' => round($t - $h, 2), 'ttc' => $t];
            $ht += $h;
            $tva += $t - $h;
            $ttc += $t;
        }

        return ['lignes' => $lines, 'tva_detail' => $detail, 'total_ht' => round($ht, 2), 'total_tva' => round($tva, 2), 'total_ttc' => round($ttc, 2)];
    }

    /** Cart line for a catalogue product (retail TTC price, product VAT rate or the company default). */
    public static function lineFor(Product $p, float $qty = 1): array
    {
        $rate = $p->tva_rate !== null ? (float) $p->tva_rate : (float) (\App\Models\Societe::current()->tva_rate_default ?? 20);
        if (\App\Models\Societe::current()->tva_franchise) {
            $rate = 0.0;
        }

        return ['product_id' => $p->id, 'reference' => (string) $p->reference, 'designation' => $p->name, 'quantity' => $qty,
            'prix_unitaire_ttc' => (float) $p->sale_price, 'remise_pct' => 0, 'tva_rate' => $rate];
    }

    // ------------------------------------------------------------------ sales

    /**
     * Records a sale. $tendered = amounts handed over per mode (especes may exceed the rest: change is given back in cash).
     *
     * @throws CaisseException
     */
    public static function encaisser(array $cart, array $tendered, ?Client $client = null): CaisseTicket
    {
        $cart = array_values(array_filter($cart, fn ($l) => (float) ($l['quantity'] ?? 0) != 0.0));
        if (! $cart) {
            throw new CaisseException('Le ticket est vide.');
        }
        foreach ($cart as $l) {
            if ((float) $l['quantity'] <= 0 || (float) $l['prix_unitaire_ttc'] < 0 || trim((string) ($l['designation'] ?? '')) === '') {
                throw new CaisseException('Vérifiez les lignes : quantité positive, prix et désignation obligatoires.');
            }
        }
        $c = self::compute($cart);
        if ($c['total_ttc'] <= 0) {
            throw new CaisseException('Le total du ticket doit être positif.');
        }
        $tendered = array_map(fn ($v) => round(max(0, (float) $v), 2), array_intersect_key($tendered, self::MODES));
        $sum = array_sum($tendered);
        if ($sum + 0.004 < $c['total_ttc']) {
            throw new CaisseException('Paiement insuffisant : il manque '.Fmt::money($c['total_ttc'] - $sum).'.');
        }
        $rendu = round($sum - $c['total_ttc'], 2);
        $cash = $tendered['especes'] ?? 0;
        if ($rendu > 0.004 && $rendu - $cash > 0.004) {
            throw new CaisseException('Le rendu de monnaie se fait en espèces : le paiement par carte ou chèque ne peut pas dépasser le total.');
        }
        $net = $tendered;
        if ($rendu > 0) {
            $net['especes'] = round($cash - $rendu, 2);
        }
        $net = array_filter($net, fn ($v) => $v > 0);

        return self::createTicket('vente', $c, $net, $client, $cash, max(0, $rendu));
    }

    /** Cancels a sale with a negative ticket (stock and payments are reversed). @throws CaisseException */
    public static function annuler(CaisseTicket $ticket, string $motif): CaisseTicket
    {
        $motif = trim($motif);
        if ($ticket->isAnnulation()) {
            throw new CaisseException('Un ticket d’annulation ne peut pas être annulé.');
        }
        if ($motif === '') {
            throw new CaisseException('Indiquez le motif de l’annulation.');
        }

        return DB::transaction(function () use ($ticket, $motif) {
            CaisseTicket::whereKey($ticket->id)->lockForUpdate()->first();
            if (CaisseTicket::where('annule_ticket_id', $ticket->id)->exists()) {
                throw new CaisseException('Ce ticket est déjà annulé.');
            }
            $lines = $ticket->lignes()->get()->map(fn ($l) => [
                'product_id' => $l->product_id, 'reference' => $l->reference, 'designation' => $l->designation,
                'quantity' => -1 * (float) $l->quantity, 'prix_unitaire_ttc' => (float) $l->prix_unitaire_ttc,
                'remise_pct' => (float) $l->remise_pct, 'tva_rate' => (float) $l->tva_rate,
            ])->all();
            $c = self::compute($lines);
            $pay = [];
            foreach ($ticket->paiements()->get() as $p) {
                $pay[$p->mode] = round(($pay[$p->mode] ?? 0) - (float) $p->montant, 2);
            }
            $client = $ticket->client_id ? Client::find($ticket->client_id) : null;
            $t = self::createTicket('annulation', $c, $pay, $client, 0, 0, $ticket);
            self::journal('ANNULATION', "Annulation du ticket {$ticket->numero} par le ticket {$t->numero}", ['ticket_id' => $ticket->id, 'annulation_id' => $t->id, 'motif' => $motif]);

            return $t;
        });
    }

    protected static function createTicket(string $type, array $c, array $pay, ?Client $client, float $recu, float $rendu, ?CaisseTicket $annule = null): CaisseTicket
    {
        return DB::transaction(function () use ($type, $c, $pay, $client, $recu, $rendu, $annule) {
            $last = CaisseTicket::orderByDesc('sequence')->lockForUpdate()->first();
            $seq = ($last->sequence ?? 0) + 1;
            $now = now()->startOfSecond();
            $user = auth()->user();

            $t = new CaisseTicket([
                'sequence' => $seq, 'numero' => 'T-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT), 'type' => $type,
                'annule_ticket_id' => $annule?->id, 'user_id' => $user?->id, 'user_name' => $user?->name,
                'client_id' => $client?->id, 'client_name' => $client?->name,
                'total_ht' => $c['total_ht'], 'total_tva' => $c['total_tva'], 'total_ttc' => $c['total_ttc'],
                'montant_recu' => $recu, 'rendu' => $rendu, 'tva_detail' => $c['tva_detail'],
                'prev_hash' => $last->hash ?? self::GENESIS, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $lignes = collect($c['lignes'])->map(fn ($l) => new CaisseTicketLigne([
                'product_id' => $l['product_id'] ?? null, 'reference' => $l['reference'] ?? null, 'designation' => mb_substr((string) $l['designation'], 0, 255),
                'quantity' => $l['quantity'], 'prix_unitaire_ttc' => $l['prix_unitaire_ttc'], 'remise_pct' => $l['remise_pct'],
                'tva_rate' => $l['tva_rate'], 'total_ttc' => $l['total_ttc'], 'total_ht' => $l['total_ht'],
            ]));
            $paiements = collect($pay)->map(fn ($v, $mode) => new CaissePaiement(['mode' => $mode, 'montant' => $v]))->values();
            $t->hash = self::sign(self::ticketPayload($t, $lignes, $paiements));
            $t->save();
            foreach ($lignes as $l) {
                $l->ticket_id = $t->id;
                $l->is_demo = $t->is_demo;
                $l->save();
            }
            foreach ($paiements as $p) {
                $p->ticket_id = $t->id;
                $p->is_demo = $t->is_demo;
                $p->save();
            }

            // stock: a sale takes the goods out, a cancellation puts them back
            foreach ($lignes as $l) {
                if (! $l->product_id) {
                    continue;
                }
                $qty = (float) $l->quantity;
                $qty > 0 ? Product::whereKey($l->product_id)->decrement('stock_qty', $qty) : Product::whereKey($l->product_id)->increment('stock_qty', -$qty);
                StockMovement::create([
                    'product_id' => $l->product_id, 'type' => $qty > 0 ? 'exit' : 'entry', 'quantity' => abs($qty),
                    'reference_type' => 'caisse_ticket', 'reference_id' => $t->id, 'date' => $now->toDateString(),
                    'notes' => ($type === 'vente' ? 'Caisse ' : 'Annulation caisse ').$t->numero,
                ]);
            }

            return $t;
        });
    }

    // ------------------------------------------------------------------ journal & prints

    public static function journal(string $code, string $libelle, array $data = []): CaisseJournal
    {
        return DB::transaction(function () use ($code, $libelle, $data) {
            $last = CaisseJournal::orderByDesc('sequence')->lockForUpdate()->first();
            $now = now()->startOfSecond();
            $user = auth()->user();
            $j = new CaisseJournal(['sequence' => ($last->sequence ?? 0) + 1, 'code' => $code, 'libelle' => mb_substr($libelle, 0, 255),
                'user_id' => $user?->id, 'user_name' => $user?->name, 'data' => $data,
                'prev_hash' => $last->hash ?? self::GENESIS, 'created_at' => $now, 'updated_at' => $now]);
            $j->hash = self::sign(self::journalPayload($j));
            $j->save();

            return $j;
        });
    }

    /** Logs a ticket print and returns 0 for the original, n for the n-th duplicate. */
    public static function impression(CaisseTicket $t): int
    {
        $n = CaisseJournal::whereIn('code', ['IMPRESSION', 'DUPLICATA'])->where('data->ticket_id', $t->id)->count();
        self::journal($n ? 'DUPLICATA' : 'IMPRESSION', ($n ? "Duplicata n° {$n} du ticket " : 'Impression du ticket ').$t->numero, ['ticket_id' => $t->id, 'n' => $n]);

        return $n;
    }

    // ------------------------------------------------------------------ closures

    /** Tickets not yet included in a daily closure. */
    public static function nonClotures(?Carbon $before = null): Collection
    {
        $lastSeq = (int) CaisseCloture::where('type', 'jour')->max('last_seq');

        return CaisseTicket::where('sequence', '>', $lastSeq)->when($before, fn ($q) => $q->where('created_at', '<', $before))->orderBy('sequence')->get();
    }

    /** Daily Z closure of every ticket not closed yet (optionally only those before $before). @throws CaisseException */
    public static function cloturerJour(?Carbon $before = null, ?string $periode = null): CaisseCloture
    {
        return DB::transaction(function () use ($before, $periode) {
            $last = CaisseCloture::where('type', 'jour')->orderByDesc('sequence')->lockForUpdate()->first();
            $tickets = CaisseTicket::where('sequence', '>', (int) ($last->last_seq ?? 0))
                ->when($before, fn ($q) => $q->where('created_at', '<', $before))->orderBy('sequence')->with('paiements')->get();
            if ($tickets->isEmpty()) {
                throw new CaisseException('Aucun ticket à clôturer.');
            }
            $tva = [];
            foreach ($tickets as $t) {
                foreach ($t->tva_detail as $d) {
                    $k = self::m($d['taux']);
                    $tva[$k] = ['taux' => (float) $d['taux'], 'ht' => round(($tva[$k]['ht'] ?? 0) + $d['ht'], 2),
                        'tva' => round(($tva[$k]['tva'] ?? 0) + $d['tva'], 2), 'ttc' => round(($tva[$k]['ttc'] ?? 0) + $d['ttc'], 2)];
                }
            }
            ksort($tva);
            $pay = [];
            foreach ($tickets->flatMap->paiements as $p) {
                $pay[$p->mode] = round(($pay[$p->mode] ?? 0) + (float) $p->montant, 2);
            }

            return self::saveCloture('jour', $last, $periode ?? now()->toDateString(), [
                'first_seq' => $tickets->first()->sequence, 'last_seq' => $tickets->last()->sequence,
                'nb_tickets' => $tickets->where('type', 'vente')->count(), 'nb_annulations' => $tickets->where('type', 'annulation')->count(),
                'total_ht' => round($tickets->sum(fn ($t) => (float) $t->total_ht), 2), 'total_tva' => round($tickets->sum(fn ($t) => (float) $t->total_tva), 2),
                'total_ttc' => round($tickets->sum(fn ($t) => (float) $t->total_ttc), 2), 'tva_detail' => array_values($tva), 'paiements' => $pay,
            ], round($tickets->sum(fn ($t) => abs((float) $t->total_ttc)), 2));
        });
    }

    /** Monthly (2026-11) or yearly (2026) closure built from the closures of the level below. @throws CaisseException */
    public static function cloturerPeriode(string $type, string $periode): CaisseCloture
    {
        $child = ['mois' => 'jour', 'annee' => 'mois'][$type] ?? throw new CaisseException('Type de clôture inconnu.');

        return DB::transaction(function () use ($type, $periode, $child) {
            $last = CaisseCloture::where('type', $type)->orderByDesc('sequence')->lockForUpdate()->first();
            if (CaisseCloture::where('type', $type)->where('periode', $periode)->exists()) {
                throw new CaisseException('Cette période est déjà clôturée.');
            }
            $src = CaisseCloture::where('type', $child)->where('periode', 'like', $periode.'-%')->orderBy('sequence')->get();
            if ($src->isEmpty()) {
                throw new CaisseException('Aucune clôture à regrouper pour '.$periode.'.');
            }
            $tva = [];
            $pay = [];
            foreach ($src as $s) {
                foreach ($s->tva_detail as $d) {
                    $k = self::m($d['taux']);
                    $tva[$k] = ['taux' => (float) $d['taux'], 'ht' => round(($tva[$k]['ht'] ?? 0) + $d['ht'], 2),
                        'tva' => round(($tva[$k]['tva'] ?? 0) + $d['tva'], 2), 'ttc' => round(($tva[$k]['ttc'] ?? 0) + $d['ttc'], 2)];
                }
                foreach ($s->paiements as $mode => $v) {
                    $pay[$mode] = round(($pay[$mode] ?? 0) + (float) $v, 2);
                }
            }
            ksort($tva);
            $abs = 0.0;
            foreach ($src as $s) {
                $prevCum = CaisseCloture::where('type', $child)->where('sequence', $s->sequence - 1)->value('cumul_abs_ttc') ?? 0;
                $abs += (float) $s->cumul_abs_ttc - (float) $prevCum;
            }

            return self::saveCloture($type, $last, $periode, [
                'first_seq' => $src->min('first_seq'), 'last_seq' => $src->max('last_seq'),
                'nb_tickets' => $src->sum('nb_tickets'), 'nb_annulations' => $src->sum('nb_annulations'),
                'total_ht' => round($src->sum(fn ($s) => (float) $s->total_ht), 2), 'total_tva' => round($src->sum(fn ($s) => (float) $s->total_tva), 2),
                'total_ttc' => round($src->sum(fn ($s) => (float) $s->total_ttc), 2), 'tva_detail' => array_values($tva), 'paiements' => $pay,
            ], round($abs, 2));
        });
    }

    protected static function saveCloture(string $type, ?CaisseCloture $last, string $periode, array $v, float $abs): CaisseCloture
    {
        $now = now()->startOfSecond();
        $user = auth()->user();
        $c = new CaisseCloture($v + ['type' => $type, 'sequence' => ($last->sequence ?? 0) + 1, 'periode' => $periode,
            'cumul_ttc' => round((float) ($last->cumul_ttc ?? 0) + $v['total_ttc'], 2),
            'cumul_abs_ttc' => round((float) ($last->cumul_abs_ttc ?? 0) + $abs, 2),
            'user_id' => $user?->id, 'user_name' => $user?->name, 'prev_hash' => $last->hash ?? self::GENESIS,
            'created_at' => $now, 'updated_at' => $now]);
        $c->hash = self::sign(self::cloturePayload($c));
        $c->save();
        self::journal('CLOTURE_'.strtoupper($type === 'annee' ? 'ANNEE' : $type), 'Clôture '.$type.' '.$periode.' (Z n° '.$c->sequence.')', ['cloture_id' => $c->id]);

        return $c;
    }

    // ------------------------------------------------------------------ integrity

    /** Re-computes every chain of the current company. Returns ['ok' => bool, 'errors' => [...], counts]. */
    public static function verifier(): array
    {
        $errors = [];
        $prev = self::GENESIS;
        $n = 0;
        CaisseTicket::orderBy('sequence')->with(['lignes', 'paiements'])->chunk(500, function ($chunk) use (&$errors, &$prev, &$n) {
            foreach ($chunk as $t) {
                $n++;
                if ($t->sequence !== $n) {
                    $errors[] = "Ticket n° {$n} manquant (trouvé {$t->numero}).";
                    $n = $t->sequence;
                }
                if ($t->prev_hash !== $prev) {
                    $errors[] = "Chaînage rompu avant le ticket {$t->numero}.";
                }
                if (! hash_equals($t->hash, self::sign(self::ticketPayload($t, $t->lignes, $t->paiements)))) {
                    $errors[] = "Le ticket {$t->numero} a été modifié.";
                }
                $prev = $t->hash;
            }
        });
        $nc = 0;
        foreach (['jour', 'mois', 'annee'] as $type) {
            $prev = self::GENESIS;
            foreach (CaisseCloture::where('type', $type)->orderBy('sequence')->get() as $k => $c) {
                $nc++;
                if ($c->sequence !== $k + 1 || $c->prev_hash !== $prev || ! hash_equals($c->hash, self::sign(self::cloturePayload($c)))) {
                    $errors[] = "Clôture {$type} n° {$c->sequence} invalide.";
                }
                $prev = $c->hash;
            }
        }
        $prev = self::GENESIS;
        $nj = 0;
        foreach (CaisseJournal::orderBy('sequence')->cursor() as $j) {
            $nj++;
            if ($j->sequence !== $nj || $j->prev_hash !== $prev || ! hash_equals($j->hash, self::sign(self::journalPayload($j)))) {
                $errors[] = "Événement du journal n° {$j->sequence} invalide.";
            }
            $prev = $j->hash;
        }

        return ['ok' => ! $errors, 'errors' => $errors, 'tickets' => $n, 'clotures' => $nc, 'journal' => $nj];
    }
}
