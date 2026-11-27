<?php

namespace App\Support;

use App\Models\AvoirClient;
use App\Models\BonLivraison;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\ReglementClient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Sales lists (2026-11-26): one place for the filters, totals and printable pages of
 * Devis, Bons de livraison, Factures, Règlements clients and Soldes clients, so the
 * screen and the printed list always show the same rows.
 */
class SalesLists
{
    public const MODES = ['especes' => 'Espèces', 'virement' => 'Virement', 'versement' => 'Versement', 'transfert' => 'Transfert cash', 'cheque' => 'Chèque', 'effet' => 'Effet', 'carte' => 'Carte', 'prelevement' => 'Prélèvement'];

    /** modes that come with a bank, a number and a due date */
    public const MODES_PAPIER = ['cheque', 'effet'];

    /** client name + first name + code search on a relation */
    public static function whereClient(Builder $q, string $s): Builder
    {
        return $q->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$s}%")->orWhere('prenom', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
    }

    public static function clientLabel(?Client $c): string
    {
        return $c ? trim(($c->prenom ? $c->prenom.' ' : '').$c->name) : '—';
    }

    protected static function dates(Builder $q, array $f): Builder
    {
        return $q->when($f['dateStart'] ?? null, fn ($q, $d) => $q->whereDate('date', '>=', $d))
            ->when($f['dateEnd'] ?? null, fn ($q, $d) => $q->whereDate('date', '<=', $d));
    }

    public static function devis(array $f): Builder
    {
        $s = trim((string) ($f['search'] ?? ''));
        $q = Devis::query()->with('client')
            ->when($s !== '', fn ($q) => $q->where(fn ($w) => self::whereClient($w->where('numero', 'like', "%{$s}%"), $s)))
            ->when($f['numeroSearch'] ?? null, fn ($q, $n) => $q->where('numero', 'like', "%{$n}%"));

        return self::dates($q, $f)->latest('date')->latest('id');
    }

    public static function bonLivraisons(array $f): Builder
    {
        $s = trim((string) ($f['search'] ?? ''));
        $q = BonLivraison::query()->with(['client', 'facture'])
            ->when($s !== '', fn ($q) => $q->where(fn ($w) => self::whereClient($w->where('numero', 'like', "%{$s}%"), $s)));

        return self::dates($q, $f)->latest('date')->latest('id');
    }

    public static function factures(array $f): Builder
    {
        $s = trim((string) ($f['search'] ?? ''));
        $q = Facture::query()->with(['client', 'bonLivraison'])
            ->when($s !== '', fn ($q) => $q->where(fn ($w) => self::whereClient($w->where('numero', 'like', "%{$s}%"), $s)
                ->orWhereHas('bonLivraison', fn ($b) => $b->where('numero', 'like', "%{$s}%"))));

        return self::dates($q, $f)->latest('date')->latest('id');
    }

    public static function reglements(array $f): Builder
    {
        $s = trim((string) ($f['search'] ?? ''));
        $q = ReglementClient::query()->with(['client', 'facture', 'bonLivraison'])
            ->when($s !== '', fn ($q) => $q->where(fn ($w) => self::whereClient($w->whereHas('facture', fn ($x) => $x->where('numero', 'like', "%{$s}%"))->orWhereHas('bonLivraison', fn ($x) => $x->where('numero', 'like', "%{$s}%")), $s)));

        return self::dates($q, $f)->latest('date')->latest('id');
    }

    public static function totals(string $type, array $f): array
    {
        return match ($type) {
            'devis' => (function () use ($f) {
                $q = self::devis($f)->reorder();
                $acc = (clone $q)->where('status', 'accepted');

                return ['Devis' => (string) (clone $q)->count(), 'Total TTC' => Fmt::money((clone $q)->sum('total_ttc')), 'Acceptés (TTC)' => Fmt::money($acc->sum('total_ttc'))];
            })(),
            'bl' => (function () use ($f) {
                $q = self::bonLivraisons($f)->reorder();

                return ['Bons de livraison' => (string) (clone $q)->count(), 'Total' => Fmt::money((clone $q)->sum('total')), 'À livrer' => (string) (clone $q)->where('status', 'draft')->count()];
            })(),
            'factures' => (function () use ($f) {
                $q = self::factures($f)->reorder();
                $ttc = (float) (clone $q)->sum('total_ttc');
                $paye = (float) (clone $q)->sum('montant_paye');
                $avoirs = (float) AvoirClient::whereIn('facture_id', (clone $q)->select('id'))->sum('montant');

                return ['Factures' => (string) (clone $q)->count(), 'Total TTC' => Fmt::money($ttc), 'Encaissé' => Fmt::money($paye), 'Reste à encaisser' => Fmt::money(max(0, $ttc - $paye - $avoirs))];
            })(),
            'reglements' => (function () use ($f) {
                $q = self::reglements($f)->reorder();

                return ['Règlements' => (string) (clone $q)->count(), 'Total encaissé' => Fmt::money((clone $q)->sum('montant'))];
            })(),
            default => [],
        };
    }

    /**
     * "Dupliquer" for BL / factures: the create form opens already filled (nothing is saved, no number
     * used, no stock moved until "Enregistrer"). Same mechanism as the Copilot (CopilotPrefill).
     */
    public static function prefillUrl(string $formClass, string $route, $doc, array $props, string $message): string
    {
        $items = $doc->items->map(fn ($i) => ['product_id' => $i->product_id, 'quantity' => (float) $i->quantity,
            'unit_price' => (float) $i->unit_price, 'tva_rate' => $i->tva_rate !== null ? (float) $i->tva_rate : (float) $doc->tva_rate]
            + ($i->getAttribute('remise_type') !== null ? ['remise_type' => $i->remise_type, 'remise_value' => (float) ($i->remise_value ?? 0)] : []))->values()->all();
        $key = Str::random(24);
        Cache::put('copilot:'.auth()->id().':'.$key, ['form' => $formClass, 'party_field' => 'client_id', 'party_id' => $doc->client_id,
            'items' => $items, 'notes' => (string) ($doc->notes ?? ''), 'props' => $props, 'message' => $message], now()->addHours(6));

        return route($route, ['copilot' => $key]);
    }

    /** Printable list (route {type}.print): columns, rows and totals with the same filters as the screen. */
    public static function printData(string $type, array $f): array
    {
        $money = fn ($v) => Fmt::money($v);
        $client = fn ($c) => self::clientLabel($c).($c?->code ? ' ('.$c->code.')' : '');

        return match ($type) {
            'devis' => ['Liste des devis', ['N°', 'Client', 'Date', 'Valable jusqu’au', 'Statut', ['Total TTC', 'r']],
                self::devis($f)->get()->map(fn ($d) => [$d->numero, $client($d->client), $d->date?->format('d/m/Y'), $d->valid_until?->format('d/m/Y') ?? '—', Fmt::status($d->status), $money($d->total_ttc)])->all()],
            'bl' => ['Liste des bons de livraison', ['N°', 'Client', 'Date', 'Statut', 'Facture', ['Total', 'r']],
                self::bonLivraisons($f)->get()->map(fn ($b) => [$b->numero, $client($b->client), $b->date?->format('d/m/Y'), Fmt::status($b->status), $b->facture?->numero ?? '—', $money($b->total)])->all()],
            'factures' => ['Liste des factures', ['N°', 'BL', 'Client', 'Date', 'Échéance', 'Statut', ['Total TTC', 'r'], ['Reste', 'r']],
                self::factures($f)->get()->map(fn ($x) => [$x->numero, $x->bonLivraison?->numero ?? '—', $client($x->client), $x->date?->format('d/m/Y'), $x->due_date?->format('d/m/Y') ?? '—', Fmt::status($x->status), $money($x->total_ttc), $money($x->resteAPayer())])->all()],
            'reglements' => ['Liste des règlements clients', ['Date', 'Client', 'Facture', 'Mode', 'N° chèque', ['Montant', 'r']],
                self::reglements($f)->get()->map(fn ($r) => [$r->date?->format('d/m/Y'), $client($r->client), $r->facture?->numero ?? $r->bonLivraison?->numero ?? '—', self::MODES[$r->mode] ?? ucfirst((string) $r->mode), implode(' · ', array_filter([$r->banque, $r->numero_cheque ? 'n° '.$r->numero_cheque : null, $r->tireur, $r->observation()])) ?: '—', $money($r->montant)])->all()],
            'soldes' => (function () use ($f, $client, $money) {
                $s = trim((string) ($f['search'] ?? ''));
                $rows = Client::query()->when($s !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('prenom', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")))
                    ->orderBy('name')->get()->map(fn ($c) => [$c->code ?? '—', self::clientLabel($c), $c->phone ?: '—', $c->credit_limit ? $money($c->credit_limit) : '—', $money($c->solde())])->all();

                return ['Soldes clients', ['Code', 'Client', 'Téléphone', ['Plafond', 'r'], ['Solde', 'r']], $rows];
            })(),
            'plafonds' => (function () use ($f, $money) {
                $rows = self::plafondClients((string) ($f['search'] ?? ''))->map(function ($c) use ($money) {
                    $solde = $c->solde();
                    $over = $solde - (float) $c->credit_limit;

                    return [$c->code ?? '—', self::clientLabel($c), $c->phone ?: '—', $money($c->credit_limit), $money($solde), $over > 0 ? $money($over) : '—'];
                })->all();

                return ['Plafonds clients', ['Code', 'Client', 'Téléphone', ['Plafond', 'r'], ['Solde', 'r'], ['Dépassement', 'r']], $rows];
            })(),
        };
    }

    /** clients with a credit limit, same filter as the Plafonds screen */
    public static function plafondClients(string $search = '')
    {
        $s = trim($search);

        return Client::query()->whereNotNull('credit_limit')->where('credit_limit', '>', 0)
            ->when($s !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('prenom', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")))
            ->orderBy('name')->get();
    }

    public static function plafondsTotals(string $search = ''): array
    {
        $clients = self::plafondClients($search);
        $over = $clients->map(fn ($c) => $c->solde() - (float) $c->credit_limit)->filter(fn ($v) => $v > 0);

        return ['Clients' => (string) $clients->count(), 'En dépassement' => (string) $over->count(), 'Total dépassement' => Fmt::money($over->sum())];
    }

    public static function totalSoldes(string $search = ''): float
    {
        $s = trim($search);

        return (float) Client::query()->when($s !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('prenom', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")))
            ->get()->sum(fn ($c) => max(0, $c->solde()));
    }
}
