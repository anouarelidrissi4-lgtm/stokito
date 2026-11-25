<?php

namespace App\Support;

use App\Models\CaisseTicket;
use App\Models\CaisseTicketLigne;
use App\Models\Client;
use App\Models\Facture;
use App\Models\FactureItem;
use App\Models\Fournisseur;
use App\Models\Product;
use App\Models\ReglementClient;
use App\Models\Societe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * The actions the Copilot may take (2026-11-25). Read-only lookups, plus "preparer_document" which only
 * stores a pre-filled form for the user to check — nothing is ever saved without the user's click.
 * Every query runs in the logged-in user's company (tenant scope).
 */
class CopilotTools
{
    public static function definitions(): array
    {
        $line = ['type' => 'object', 'properties' => [
            'product_id' => ['type' => 'integer', 'description' => 'id renvoyé par chercher_produits'],
            'quantite' => ['type' => 'number'],
            'prix_unitaire_ht' => ['type' => 'number', 'description' => 'Seulement si l’utilisateur donne un prix ; HT (convertir un prix TTC avec le taux de TVA du produit). Sinon omettre : le prix du catalogue est utilisé.'],
            'remise_pct' => ['type' => 'number', 'description' => 'Remise en %, devis uniquement'],
        ], 'required' => ['product_id', 'quantite']];

        return [
            ['name' => 'chercher_clients', 'description' => 'Cherche des clients de la société par nom, téléphone ou identifiant.',
                'inputSchema' => ['type' => 'object', 'properties' => ['recherche' => ['type' => 'string']], 'required' => ['recherche']]],
            ['name' => 'chercher_fournisseurs', 'description' => 'Cherche des fournisseurs par nom.',
                'inputSchema' => ['type' => 'object', 'properties' => ['recherche' => ['type' => 'string']], 'required' => ['recherche']]],
            ['name' => 'chercher_produits', 'description' => 'Cherche des produits du catalogue (désignation, référence, code-barres). Renvoie prix de vente TTC, prix d’achat HT, TVA et stock.',
                'inputSchema' => ['type' => 'object', 'properties' => ['recherche' => ['type' => 'string']], 'required' => ['recherche']]],
            ['name' => 'preparer_document', 'description' => 'Prépare un devis, une facture, un bon de livraison (clients) ou un bon de commande (fournisseurs) : renvoie un lien vers le formulaire pré-rempli. Rien n’est enregistré ; l’utilisateur vérifie et enregistre.',
                'inputSchema' => ['type' => 'object', 'properties' => [
                    'type' => ['type' => 'string', 'enum' => ['devis', 'facture', 'bon_livraison', 'bon_commande']],
                    'client_id' => ['type' => 'integer', 'description' => 'Pour devis, facture et bon de livraison'],
                    'fournisseur_id' => ['type' => 'integer', 'description' => 'Pour un bon de commande'],
                    'lignes' => ['type' => 'array', 'items' => $line],
                    'notes' => ['type' => 'string'],
                ], 'required' => ['type', 'lignes']]],
            ['name' => 'statistiques', 'description' => 'Chiffre d’affaires (factures + caisse), encaissements, nombre de factures, meilleurs produits et clients sur une période.',
                'inputSchema' => ['type' => 'object', 'properties' => [
                    'periode' => ['type' => 'string', 'enum' => ['aujourd_hui', 'hier', 'semaine', 'mois', 'mois_dernier', 'annee']],
                ], 'required' => ['periode']]],
            ['name' => 'factures_impayees', 'description' => 'Factures pas (entièrement) payées, les plus anciennes d’abord, avec le reste à payer.',
                'inputSchema' => ['type' => 'object', 'properties' => ['client_id' => ['type' => 'integer']]]],
            ['name' => 'stock', 'description' => 'Stock des produits : une recherche, ou seulement les produits en stock bas / en rupture.',
                'inputSchema' => ['type' => 'object', 'properties' => ['recherche' => ['type' => 'string'], 'stock_bas_seulement' => ['type' => 'boolean']]]],
        ];
    }

    /** Runs a tool; returns [array $result, ?array $action]. */
    public static function run(string $name, array $in): array
    {
        $u = auth()->user();
        try {
            return match ($name) {
                'chercher_clients' => [self::clients((string) ($in['recherche'] ?? '')), null],
                'chercher_fournisseurs' => [self::fournisseurs((string) ($in['recherche'] ?? '')), null],
                'chercher_produits' => [self::produits((string) ($in['recherche'] ?? '')), null],
                'preparer_document' => self::preparer($in),
                'statistiques' => [$u->hasPermission('statistiques') || $u->hasPermission('factures_consulter') ? self::stats((string) ($in['periode'] ?? 'mois')) : ['erreur' => 'Accès refusé : droit « statistiques » nécessaire.'], null],
                'factures_impayees' => [$u->hasPermission('factures_consulter') ? self::impayes($in['client_id'] ?? null) : ['erreur' => 'Accès refusé.'], null],
                'stock' => [self::stock((string) ($in['recherche'] ?? ''), (bool) ($in['stock_bas_seulement'] ?? false)), null],
                default => [['erreur' => 'Outil inconnu.'], null],
            };
        } catch (\Throwable $e) {
            report($e);

            return [['erreur' => 'Erreur interne pendant l’action.'], null];
        }
    }

    protected static function like(string $q): string
    {
        return '%'.str_replace(['%', '_'], ['\%', '\_'], trim($q)).'%';
    }

    public static function clients(string $q): array
    {
        return Client::query()->when(trim($q) !== '', fn ($b) => $b->where(fn ($w) => $w->where('name', 'like', self::like($q))->orWhere('phone', 'like', self::like($q))->orWhere('ice', 'like', self::like($q))))
            ->orderBy('name')->limit(8)->get()->map(fn ($c) => ['id' => $c->id, 'nom' => $c->name, 'ville' => $c->city ?? null, 'telephone' => $c->phone])->all();
    }

    public static function fournisseurs(string $q): array
    {
        return Fournisseur::query()->when(trim($q) !== '', fn ($b) => $b->where('name', 'like', self::like($q)))
            ->orderBy('name')->limit(8)->get()->map(fn ($f) => ['id' => $f->id, 'nom' => $f->name])->all();
    }

    protected static function rate(Product $p): float
    {
        $s = Societe::current();
        if ($s->tva_franchise) {
            return 0.0;
        }

        return $p->tva_rate !== null ? (float) $p->tva_rate : (float) ($s->tva_rate_default ?? 20);
    }

    public static function produits(string $q): array
    {
        $words = array_filter(preg_split('/\s+/', trim($q)));
        $seePurchase = auth()->user()->hasPermission('produits_prix_achat');

        return Product::query()
            ->where(function ($b) use ($q, $words) {
                $b->where('reference', $q)->orWhere('barcode', $q)->orWhere(function ($w) use ($words) {
                    foreach ($words as $word) {
                        $w->where(fn ($x) => $x->where('name', 'like', self::like($word))->orWhere('reference', 'like', self::like($word)));
                    }
                });
            })
            ->orderBy('name')->limit(10)->get()
            ->map(fn ($p) => array_filter([
                'id' => $p->id, 'reference' => $p->reference, 'designation' => $p->name, 'unite' => $p->unit,
                'prix_vente_ttc' => (float) $p->sale_price, 'taux_tva' => self::rate($p),
                'prix_achat_ht' => $seePurchase ? (float) $p->purchase_price : null, 'stock' => (float) $p->stock_qty,
            ], fn ($v) => $v !== null))->all();
    }

    /** @return array{0: array, 1: ?array} */
    protected static function preparer(array $in): array
    {
        $u = auth()->user();
        $map = [
            'devis' => [\App\Livewire\Devis\Form::class, 'devis.create', 'devis_creer', 'client_id', 'Devis'],
            'facture' => [\App\Livewire\Factures\Form::class, 'factures.create', 'factures_consulter', 'client_id', 'Facture'],
            'bon_livraison' => [\App\Livewire\BonLivraisons\Form::class, 'bon-livraisons.create', 'bl_creer', 'client_id', 'Bon de livraison'],
            'bon_commande' => [\App\Livewire\CommandeFournisseurs\Form::class, 'commande-fournisseurs.create', 'gestion_commandes_fournisseurs', 'fournisseur_id', 'Bon de commande'],
        ];
        [$form, $route, $perm, $partyField, $label] = $map[$in['type'] ?? ''] ?? [null, null, null, null, null];
        if (! $form) {
            return [['erreur' => 'Type de document inconnu.'], null];
        }
        if (! $u->hasPermission($perm)) {
            return [['erreur' => "Accès refusé : vous n’avez pas le droit de créer ce document ({$label})."], null];
        }
        $partyId = null;
        $partyName = null;
        if ($partyField === 'client_id' && ! empty($in['client_id'])) {
            $c = Client::find((int) $in['client_id']);
            if (! $c) {
                return [['erreur' => 'Client introuvable : utilisez chercher_clients.'], null];
            }
            [$partyId, $partyName] = [$c->id, $c->name];
        }
        if ($partyField === 'fournisseur_id' && ! empty($in['fournisseur_id'])) {
            $f = Fournisseur::find((int) $in['fournisseur_id']);
            if (! $f) {
                return [['erreur' => 'Fournisseur introuvable : utilisez chercher_fournisseurs.'], null];
            }
            [$partyId, $partyName] = [$f->id, $f->name];
        }

        $items = [];
        $summary = [];
        $ht = $ttc = 0.0;
        foreach ((array) ($in['lignes'] ?? []) as $l) {
            $p = Product::find((int) ($l['product_id'] ?? 0));
            $qty = (float) ($l['quantite'] ?? 0);
            if (! $p || $qty <= 0) {
                return [['erreur' => 'Ligne invalide : chaque ligne doit avoir un product_id trouvé avec chercher_produits et une quantité positive.'], null];
            }
            $rate = self::rate($p);
            $pu = isset($l['prix_unitaire_ht']) && $l['prix_unitaire_ht'] !== null
                ? round((float) $l['prix_unitaire_ht'], 2)
                : ($in['type'] === 'bon_commande' ? round((float) $p->purchase_price, 2) : round((float) $p->sale_price / (1 + $rate / 100), 2));
            $rem = $in['type'] === 'devis' ? min(100, max(0, (float) ($l['remise_pct'] ?? 0))) : 0.0;
            $items[] = ['product_id' => $p->id, 'quantity' => $qty, 'unit_price' => $pu, 'tva_rate' => $rate, 'remise_type' => 'percent', 'remise_value' => $rem];
            $lineHt = round($qty * $pu * (1 - $rem / 100), 2);
            $ht += $lineHt;
            $ttc += $in['type'] === 'bon_commande' ? $lineHt : round($lineHt * (1 + $rate / 100), 2);
            $summary[] = Fmt::qty($qty).' × '.$p->name.' à '.Fmt::money($pu).' HT';
        }
        if (! $items) {
            return [['erreur' => 'Aucune ligne.'], null];
        }
        $key = Str::random(24);
        Cache::put('copilot:'.$u->id.':'.$key, ['form' => $form, 'party_field' => $partyField, 'party_id' => $partyId,
            'items' => $items, 'notes' => mb_substr((string) ($in['notes'] ?? ''), 0, 1000)], now()->addHours(6));
        $url = route($route, ['copilot' => $key]);
        $cur = Societe::current()->currency ?: 'EUR';

        return [[
            'ok' => true, 'document' => $label, 'pour' => $partyName ?? 'à choisir dans le formulaire',
            'lignes' => $summary, 'total_ht' => Fmt::money($ht).' '.$cur,
            'total_ttc_estime' => $in['type'] === 'bon_commande' ? null : Fmt::money($ttc).' '.$cur,
            'note' => 'Le formulaire pré-rempli s’ouvre avec le bouton affiché sous votre réponse ; rien n’est enregistré avant que l’utilisateur clique sur Enregistrer.',
        ], ['label' => 'Ouvrir '.mb_strtolower($label).($partyName ? ' — '.$partyName : '').' pré-rempli', 'url' => $url]];
    }

    public static function stats(string $periode): array
    {
        $now = now();
        [$from, $to, $label] = match ($periode) {
            'aujourd_hui' => [$now->copy()->startOfDay(), $now->copy()->addDay()->startOfDay(), 'aujourd’hui'],
            'hier' => [$now->copy()->subDay()->startOfDay(), $now->copy()->startOfDay(), 'hier'],
            'semaine' => [$now->copy()->startOfWeek(), $now->copy()->addDay()->startOfDay(), 'cette semaine'],
            'mois_dernier' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->startOfMonth(), 'le mois dernier'],
            'annee' => [$now->copy()->startOfYear(), $now->copy()->addDay()->startOfDay(), 'cette année'],
            default => [$now->copy()->startOfMonth(), $now->copy()->addDay()->startOfDay(), 'ce mois-ci'],
        };
        $fac = Facture::horsCaisse()->where('date', '>=', $from->toDateString())->where('date', '<', $to->toDateString());
        $tk = CaisseTicket::where('created_at', '>=', $from)->where('created_at', '<', $to);
        $cur = Societe::current()->currency ?: 'EUR';
        $caFac = (float) (clone $fac)->sum('total_ttc');
        $caTk = (float) (clone $tk)->sum('total_ttc');
        $top = FactureItem::query()->selectRaw('product_id, SUM(total) as ca')->whereIn('facture_id', (clone $fac)->select('id'))->groupBy('product_id')->get()
            ->concat(CaisseTicketLigne::query()->selectRaw('product_id, SUM(total_ht) as ca')->whereNotNull('product_id')->whereIn('ticket_id', (clone $tk)->select('id'))->groupBy('product_id')->get())
            ->groupBy('product_id')->map(fn ($g) => $g->sum(fn ($r) => (float) $r->ca))->sortDesc()->take(5);
        $names = Product::whereIn('id', $top->keys())->pluck('name', 'id');

        return [
            'periode' => $label.' (du '.$from->format('d/m/Y').' au '.$to->copy()->subDay()->format('d/m/Y').')', 'devise' => $cur,
            'chiffre_affaires_ttc' => Fmt::money($caFac + $caTk), 'dont_factures_ttc' => Fmt::money($caFac), 'dont_caisse_ttc' => Fmt::money($caTk),
            'chiffre_affaires_ht' => Fmt::money((float) (clone $fac)->sum('total_ht') + (float) (clone $tk)->sum('total_ht')),
            'nombre_factures' => (clone $fac)->count(), 'nombre_tickets_caisse' => (clone $tk)->where('type', 'vente')->count(),
            'encaissements_factures' => Fmt::money((float) ReglementClient::where('date', '>=', $from->toDateString())->where('date', '<', $to->toDateString())->sum('montant')),
            'meilleurs_produits_ht' => $top->map(fn ($v, $id) => ['produit' => $names[$id] ?? '-', 'ca_ht' => Fmt::money($v)])->values()->all(),
            'meilleurs_clients_ttc' => (clone $fac)->with('client')->selectRaw('client_id, SUM(total_ttc) as ca')->groupBy('client_id')->orderByDesc('ca')->limit(5)->get()
                ->map(fn ($r) => ['client' => $r->client->name ?? '-', 'ca_ttc' => Fmt::money($r->ca)])->all(),
        ];
    }

    public static function impayes($clientId = null): array
    {
        $rows = Facture::with('client')->where('status', '!=', 'payee')->whereRaw('total_ttc - montant_paye > 0.009')
            ->when($clientId, fn ($q) => $q->where('client_id', (int) $clientId))
            ->orderByRaw('COALESCE(due_date, date)')->limit(15)->get();

        return [
            'devise' => Societe::current()->currency ?: 'EUR',
            'total_reste' => Fmt::money($rows->sum(fn ($f) => $f->total_ttc - $f->montant_paye)),
            'factures' => $rows->map(fn ($f) => ['numero' => $f->numero, 'client' => $f->client->name ?? '-', 'date' => $f->date->format('d/m/Y'),
                'echeance' => optional($f->due_date)->format('d/m/Y'), 'en_retard' => $f->due_date && $f->due_date->isPast(),
                'total_ttc' => Fmt::money($f->total_ttc), 'reste' => Fmt::money($f->total_ttc - $f->montant_paye)])->all(),
        ];
    }

    public static function stock(string $q, bool $low): array
    {
        $def = (int) (Societe::current()->stock_threshold_default ?? 0);

        return Product::query()
            ->when(trim($q) !== '', fn ($b) => $b->where(fn ($w) => $w->where('name', 'like', self::like($q))->orWhere('reference', 'like', self::like($q))))
            ->when($low, fn ($b) => $b->where(fn ($w) => $w->where('stock_qty', '<=', 0)->orWhere(fn ($x) => $x->whereNotNull('stock_alert_threshold')->whereColumn('stock_qty', '<=', 'stock_alert_threshold'))
                ->orWhere(fn ($x) => $def > 0 ? $x->whereNull('stock_alert_threshold')->where('stock_qty', '<=', $def) : $x->whereRaw('1 = 0'))))
            ->orderBy('stock_qty')->limit(20)->get()
            ->map(fn ($p) => ['reference' => $p->reference, 'designation' => $p->name, 'stock' => (float) $p->stock_qty, 'unite' => $p->unit, 'seuil' => $p->stock_alert_threshold])->all();
    }
}
