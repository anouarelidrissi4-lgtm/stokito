<?php

namespace App\Console\Commands;

use App\Models\BonLivraison;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\CommandeFournisseur;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\Product;
use App\Models\Project;
use App\Models\ReglementClient;
use App\Models\Societe;
use App\Models\TresorerieCompte;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Rebuilds the public demo (/demo) from scratch: demo account + realistic data
 * for a building-materials trader. Default: France (EUR, SIRET, French invoice mentions);
 * --country=MA rebuilds the original Moroccan dataset (MAD, ICE). Every company, phone
 * number (ARCEP fictional ranges for France) and identifier is invented. Only touches rows with is_demo = 1
 * of the "Demo" project. Scheduled every night (routes/console.php).
 */
class DemoReset extends Command
{
    protected $signature = 'demo:reset {--country=FR : FR (France, EUR) or MA (Maroc, MAD)}';

    protected $description = 'Reset the public demo account and its sample data';

    /** Tables holding tenant data, children first. */
    protected array $tables = [
        'reglements_clients', 'reglements_fournisseurs', 'avoirs_clients', 'avoirs_fournisseurs',
        'cheques_effets_fournisseurs', 'mouvements_bancaires', 'bons_de_chargement',
        'factures', 'bon_livraisons', 'devis', 'commande_fournisseurs', 'stock_movements',
        'rappels', 'documents_externes', 'inventaires', 'products', 'categories', 'brands', 'clients',
        'fournisseurs', 'tresorerie_comptes', 'societe',
    ];

    protected array $children = [
        'devis_items' => ['devis_id', 'devis'],
        'bon_livraison_items' => ['bon_livraison_id', 'bon_livraisons'],
        'facture_items' => ['facture_id', 'factures'],
        'commande_fournisseur_items' => ['commande_fournisseur_id', 'commande_fournisseurs'],
        'bon_chargement_items' => ['bon_de_chargement_id', 'bons_de_chargement'],
        'inventaire_items' => ['inventaire_id', 'inventaires'],
    ];

    public function handle(): int
    {
        $project = Project::firstOrCreate(['name' => 'Demo'], ['is_active' => true]);

        $user = User::where('email', 'demo@stockpilot.cloud')->first() ?? new User();
        $user->fill([
            'name' => 'Compte Démo', 'first_name' => null, 'last_name' => null, 'login' => 'demo',
            'email' => 'demo@stockpilot.cloud', 'role' => 'admin', 'status' => 'active',
        ]);
        if (! $user->exists) {
            $user->password = Hash::make(Str::random(40)); // nobody logs in with it: /demo logs in directly
        }
        $user->forceFill(['project_id' => $project->id, 'is_demo' => true, 'is_superadmin' => false])->save();

        DB::transaction(function () use ($project, $user) {
            $this->wipe($project->id);
            Auth::setUser($user); // the ScopedToDemoMode hooks now tag every new row as demo + project
            $this->seed();
        });

        $this->info('Demo reset OK for project #'.$project->id.' (user #'.$user->id.').');

        return self::SUCCESS;
    }

    protected function wipe(int $projectId): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($this->children as $child => [$fk, $parent]) {
                $ids = DB::table($parent)->where('project_id', $projectId)->where('is_demo', true)->pluck('id');
                DB::table($child)->whereIn($fk, $ids)->delete();
            }
            foreach ($this->tables as $t) {
                DB::table($t)->where('project_id', $projectId)->where('is_demo', true)->delete();
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    protected function seed(): void
    {
        $fr = strtoupper((string) $this->option('country')) !== 'MA';
        $k = $fr ? 0.1 : 1.0; // money scale: the catalogue below is in dirhams; euros ≈ dirhams / 10
        $m = fn ($v) => round($v * $k, 2);

        $societe = Societe::current();
        $societe->fill($fr ? [
            'name' => 'Démo Matériaux SAS', 'country' => 'France', 'country_code' => 'FR', 'city' => 'Lyon',
            'address' => '12 rue de l’Industrie, 69007 Lyon', 'phone' => '01 99 00 12 34',
            'email' => 'contact@demo.stokito.com', 'ice' => '00000000000000',
            'identifiant_fiscal' => 'FR00000000000', 'registre_commerce' => 'Lyon 000 000 000', 'patente' => null, 'cnss' => null,
            'conditions_paiement' => 'Paiement à 30 jours fin de mois, par virement.',
            'currency' => 'EUR', 'tva_rate_default' => 20, 'facture_type' => 'avec_entete',
            'tva_franchise' => false, 'penalites_taux' => 'trois fois le taux d’intérêt légal', 'indemnite_40' => true,
            'escompte_neant' => true, 'tva_debits' => false, 'nature_operation_default' => 'biens', 'devis_validite_jours' => 30,
        ] : [
            'name' => 'Démo Matériaux SARL', 'country' => 'Maroc', 'country_code' => 'MA', 'city' => 'Casablanca',
            'address' => 'Zone industrielle, Casablanca', 'phone' => '05 22 00 00 00',
            'email' => 'contact@demo.stokito.com', 'ice' => '000000000000000',
            'identifiant_fiscal' => '00000000', 'registre_commerce' => 'RC 000000', 'patente' => '00000000', 'cnss' => '0000000', 'conditions_paiement' => 'Paiement à 30 jours fin de mois, par virement ou chèque.',
            'currency' => 'MAD', 'tva_rate_default' => 20, 'facture_type' => 'avec_entete',
            'tva_franchise' => false, 'penalites_taux' => null, 'indemnite_40' => false, 'escompte_neant' => false, 'tva_debits' => false, 'nature_operation_default' => null,
        ])->save();

        $cats = collect(['Fer et acier', 'Tôlerie', 'Quincaillerie', 'Peinture', 'Plomberie'])
            ->mapWithKeys(fn ($n) => [$n => Category::create(['name' => $n])]);
        // product rows name the Moroccan brands; in France they map to neutral ranges (no real brand names)
        $brandNames = $fr ? ['Sonasid' => 'Gamme Pro', 'Maghreb Steel' => 'Gamme Bâtiment', 'Colorado' => 'Gamme Déco', 'Générique' => 'Générique']
            : ['Sonasid' => 'Sonasid', 'Maghreb Steel' => 'Maghreb Steel', 'Colorado' => 'Colorado', 'Générique' => 'Générique'];
        $brandRows = collect(array_unique(array_values($brandNames)))->mapWithKeys(fn ($n) => [$n => Brand::create(['name' => $n])]);
        $brands = collect($brandNames)->map(fn ($n) => $brandRows[$n]);
        $fours = collect($fr ? [
            ['Aciers du Rhône SARL', 'ZI Lyon Sud, 69200 Vénissieux'], ['Tôles et Profilés de l’Est', '8 avenue de l’Europe, 67300 Schiltigheim'],
            ['Quincaillerie Centrale Lyon', '45 cours Gambetta, 69003 Lyon'], ['Peintures du Midi', 'ZA des Pins, 13127 Vitrolles'],
        ] : [
            ['Aciers du Nord SARL', 'Tanger'], ['Atlas Tôles', 'Mohammedia'],
            ['Quincaillerie Centrale', 'Casablanca'], ['Peintures du Sud', 'Agadir'],
        ])->map(fn ($f, $i) => Fournisseur::create([
            'name' => $f[0], 'ice' => $fr ? sprintf('001000000000%02d', $i + 1) : sprintf('00100%010d', $i + 1),
            'phone' => ($fr ? '01 99 00 20 ' : '06 00 00 00 ').sprintf('%02d', $i + 10),
            'email' => null, 'address' => $f[1],
        ]));

        // [ref, name, unit, category, brand, fournisseur idx, achat HT, vente TTC, gros TTC, stock, seuil]
        $rows = [
            ['FER-RD-08', 'Fer rond à béton Ø8 (barre 12 m)', 'barre', 'Fer et acier', 'Sonasid', 0, 38, 55, 52, 420, 100],
            ['FER-RD-10', 'Fer rond à béton Ø10 (barre 12 m)', 'barre', 'Fer et acier', 'Sonasid', 0, 58, 82, 78, 310, 100],
            ['FER-RD-12', 'Fer rond à béton Ø12 (barre 12 m)', 'barre', 'Fer et acier', 'Sonasid', 0, 84, 118, 112, 64, 80],
            ['TUB-CAR-40', 'Tube carré 40×40×2 (6 m)', 'barre', 'Fer et acier', 'Maghreb Steel', 0, 120, 165, 158, 85, 30],
            ['TUB-CAR-60', 'Tube carré 60×60×2 (6 m)', 'barre', 'Fer et acier', 'Maghreb Steel', 0, 185, 250, 238, 12, 20],
            ['COR-40', 'Cornière 40×40×4 (6 m)', 'barre', 'Fer et acier', 'Générique', 0, 95, 132, 125, 140, 40],
            ['IPE-80', 'Poutrelle IPE 80 (6 m)', 'barre', 'Fer et acier', 'Générique', 0, 260, 355, 340, 26, 10],
            ['TOL-GAL-05', 'Tôle galvanisée 0,5 mm 2×1 m', 'feuille', 'Tôlerie', 'Maghreb Steel', 1, 110, 150, 142, 200, 50],
            ['TOL-NER-RG', 'Tôle nervurée prélaquée rouge', 'feuille', 'Tôlerie', 'Maghreb Steel', 1, 145, 198, 188, 75, 30],
            ['TOL-PERF', 'Tôle perforée 2000×1000 15/10', 'feuille', 'Tôlerie', 'Générique', 1, 320, 430, 410, 8, 10],
            ['PAN-SAND-40', 'Panneau sandwich 40 mm (m²)', 'm²', 'Tôlerie', 'Générique', 1, 140, 195, 185, 360, 100],
            ['VIS-AUTO-50', 'Vis autoperceuse 5,5×50 (boîte 100)', 'boîte', 'Quincaillerie', 'Générique', 2, 32, 48, 44, 90, 20],
            ['DISQ-115', 'Disque à tronçonner 115 mm', 'pièce', 'Quincaillerie', 'Générique', 2, 6, 10, 9, 500, 100],
            ['ELEC-25', 'Électrodes de soudure 2,5 mm (5 kg)', 'paquet', 'Quincaillerie', 'Générique', 2, 120, 165, 158, 18, 15],
            ['GRIL-ST-150', 'Grillage simple torsion galva 1,50 m (rouleau 25 m)', 'rouleau', 'Quincaillerie', 'Générique', 2, 260, 350, 335, 40, 10],
            ['PEIN-ANTI-4', 'Peinture antirouille grise 4 L', 'pot', 'Peinture', 'Colorado', 3, 95, 135, 128, 55, 15],
            ['PEIN-GLY-BL', 'Peinture glycéro blanche 4 L', 'pot', 'Peinture', 'Colorado', 3, 140, 195, 185, 4, 10],
            ['DILU-1', 'Diluant synthétique 1 L', 'bidon', 'Peinture', 'Colorado', 3, 18, 28, 25, 120, 30],
            ['TUBE-PPR-25', 'Tube PPR Ø25 (4 m)', 'barre', 'Plomberie', 'Générique', 2, 22, 34, 31, 150, 40],
            ['VANNE-1/2', 'Vanne laiton 1/2"', 'pièce', 'Plomberie', 'Générique', 2, 28, 42, 39, 0, 10],
        ];
        $products = collect($rows)->mapWithKeys(fn ($r) => [$r[0] => Product::create([
            'reference' => $r[0], 'barcode' => null, 'name' => $r[1], 'unit' => $r[2],
            'category_id' => $cats[$r[3]]->id, 'brand_id' => $brands[$r[4]]->id, 'fournisseur_id' => $fours[$r[5]]->id,
            'purchase_price' => $m($r[6]), 'sale_price' => $m($r[7]), 'wholesale_price' => $m($r[8]),
            'stock_qty' => $r[9], 'stock_alert_threshold' => $r[10],
        ])]);

        $clients = collect($fr ? [
            ['Bâti Plus SARL', '27 rue Paul Bert, 69003 Lyon', 80000], ['Rhône Construction SAS', '5 allée des Artisans, 69100 Villeurbanne', 50000],
            ['Ferronnerie des Monts', '14 rue de la Forge, 42000 Saint-Étienne', 20000], ['Atelier Soudure du Parc', 'ZA du Parc, 38300 Bourgoin-Jallieu', 15000],
            ['Clôtures du Dauphiné', '3 chemin des Prés, 38500 Voiron', 30000], ['Les Terrasses du Lac (promoteur)', '18 avenue d’Albigny, 74000 Annecy', 120000],
            ['Client comptoir', 'Lyon', null], ['Métallerie Saint-Jean', '9 rue des Tanneurs, 26000 Valence', 25000],
        ] : [
            ['Bâti Plus SARL', 'Casablanca', 80000], ['Entreprise Amal Construction', 'Rabat', 50000],
            ['Ferronnerie Al Wahda', 'Salé', 20000], ['Atelier Soudure Nour', 'Kénitra', 15000],
            ['Agri Clôtures', 'El Jadida', 30000], ['Promotion Riad Immobilier', 'Marrakech', 120000],
            ['Client comptoir', 'Casablanca', null], ['Menuiserie Métallique Saïss', 'Fès', 25000],
        ])->map(fn ($c, $i) => Client::create([
            'name' => $c[0], 'ice' => $c[2] ? ($fr ? sprintf('002000000000%02d', $i + 1) : sprintf('00200%010d', $i + 1)) : null,
            'phone' => ($fr ? '06 39 98 00 ' : '06 11 00 00 ').sprintf('%02d', $i + 10), 'email' => null, 'address' => $c[1],
            'credit_limit' => $c[2] ? $m($c[2]) : null,
        ]));

        TresorerieCompte::create(['name' => 'Caisse magasin', 'type' => 'caisse', 'initial_balance' => $m(15000), 'is_active' => true]);
        TresorerieCompte::create(['name' => 'Compte bancaire principal', 'type' => 'banque', 'bank_name' => 'Banque (démo)', 'initial_balance' => $m(250000), 'is_active' => true]);

        $ht = fn ($ttc) => round($ttc / 1.2, 2); // catalogue prices are TTC, document lines are HT

        // [client idx, days ago, status, [[ref, qty], ...]]
        $devis = [
            [0, 52, 'accepted', [['FER-RD-10', 120], ['FER-RD-12', 60], ['TUB-CAR-40', 20]]],
            [1, 40, 'accepted', [['PAN-SAND-40', 180], ['VIS-AUTO-50', 12]]],
            [5, 25, 'sent', [['IPE-80', 14], ['COR-40', 30], ['PEIN-ANTI-4', 10]]],
            [4, 12, 'accepted', [['GRIL-ST-150', 16], ['TUB-CAR-40', 24], ['ELEC-25', 2]]],
            [2, 6, 'draft', [['TOL-PERF', 6], ['DISQ-115', 50]]],
            [7, 2, 'sent', [['TOL-NER-RG', 40], ['VIS-AUTO-50', 6]]],
        ];
        $docs = [];
        foreach ($devis as $n => [$ci, $ago, $status, $lines]) {
            $total = collect($lines)->sum(fn ($l) => $ht($products[$l[0]]->sale_price) * $l[1]);
            $d = Devis::create([
                'client_id' => $clients[$ci]->id, 'numero' => sprintf('DEV-%05d', $n + 1), 'date' => now()->subDays($ago)->toDateString(),
                'valid_until' => now()->subDays($ago)->addDays(30)->toDateString(),
                'status' => $status, 'total_ht' => $total, 'tva_rate' => 20, 'total_ttc' => round($total * 1.2, 2),
                'remise_type' => 'percent', 'remise_value' => 0,
            ]);
            foreach ($lines as [$ref, $qty]) {
                $d->items()->create(['product_id' => $products[$ref]->id, 'quantity' => $qty, 'unit_price' => $ht($products[$ref]->sale_price), 'remise_type' => 'percent', 'remise_value' => 0, 'total' => $ht($products[$ref]->sale_price) * $qty]);
            }
            $docs[] = [$d, $ci, $ago, $lines, $total];
        }

        // BL + facture for the accepted quotes
        $bl = 0; $fac = 0;
        $compte = TresorerieCompte::where('type', 'banque')->first();
        $pay = function (Facture $f, float $amount, $date) use ($compte, $k) {
            if ($amount <= 0) {
                return;
            }
            ReglementClient::create([
                'client_id' => $f->client_id, 'facture_id' => $f->id, 'tresorerie_compte_id' => $compte?->id,
                'montant' => $amount, 'date' => $date, 'mode' => $amount > 20000 * $k ? 'virement' : 'cheque',
            ]);
        };

        // paid history (months -5 .. -2) so the 6-month chart has real bars
        $mixes = [
            [['FER-RD-10', 80], ['FER-RD-08', 120]], [['PAN-SAND-40', 90], ['VIS-AUTO-50', 6]], [['TOL-GAL-05', 60], ['COR-40', 25]],
            [['GRIL-ST-150', 12], ['TUB-CAR-40', 30]], [['IPE-80', 10], ['PEIN-ANTI-4', 8]], [['TUBE-PPR-25', 80], ['VANNE-1/2', 20]],
        ];
        foreach ([5 => 2, 4 => 3, 3 => 2, 2 => 3] as $monthsAgo => $count) {
            for ($k = 0; $k < $count; $k++) {
                $lines = $mixes[($monthsAgo + $k) % count($mixes)];
                $factor = 1 + (($monthsAgo * 3 + $k) % 4) * 0.35;
                $lines = array_map(fn ($l) => [$l[0], (int) ceil($l[1] * $factor)], $lines);
                $total = collect($lines)->sum(fn ($l) => $ht($products[$l[0]]->sale_price) * $l[1]);
                $date = now()->subMonthsNoOverflow($monthsAgo)->startOfMonth()->addDays(4 + $k * 8);
                $f = Facture::create([
                    'client_id' => $clients[($monthsAgo + $k) % 6]->id, 'bon_livraison_id' => null, 'numero' => sprintf('FAC-%05d', ++$fac),
                    'date' => $date->toDateString(), 'due_date' => $date->copy()->addDays(30)->toDateString(),
                    'total_ht' => $total, 'tva_rate' => 20, 'total_ttc' => round($total * 1.2, 2), 'montant_paye' => round($total * 1.2, 2),
                    'status' => 'payee', 'remise_type' => 'percent', 'remise_value' => 0, 'nature_operation' => $fr ? 'biens' : null,
                ]);
                foreach ($lines as [$ref, $qty]) {
                    $f->items()->create(['product_id' => $products[$ref]->id, 'quantity' => $qty, 'unit_price' => $ht($products[$ref]->sale_price), 'total' => $ht($products[$ref]->sale_price) * $qty]);
                }
                $pay($f, (float) $f->total_ttc, $date->copy()->addDays(20)->toDateString());
            }
        }

        foreach ($docs as [$d, $ci, $ago, $lines, $total]) {
            if ($d->status !== 'accepted') {
                continue;
            }
            $b = BonLivraison::create([
                'client_id' => $clients[$ci]->id, 'devis_id' => $d->id, 'numero' => sprintf('BL-%05d', ++$bl),
                'date' => now()->subDays($ago - 2)->toDateString(), 'show_prices' => true, 'status' => 'delivered',
                'total' => round($total * 1.2, 2), 'total_ht' => $total, 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0,
            ]);
            foreach ($lines as [$ref, $qty]) {
                $b->items()->create(['product_id' => $products[$ref]->id, 'quantity' => $qty, 'unit_price' => $ht($products[$ref]->sale_price)]);
            }
            $ttc = round($total * 1.2, 2);
            $paid = [0 => $ttc, 1 => round($ttc * 0.4, 2), 3 => 0][$ci] ?? 0;
            $f = Facture::create([
                'client_id' => $clients[$ci]->id, 'bon_livraison_id' => $b->id, 'numero' => sprintf('FAC-%05d', ++$fac),
                'date' => now()->subDays($ago - 3)->toDateString(), 'due_date' => now()->subDays($ago - 33)->toDateString(),
                'total_ht' => $total, 'tva_rate' => 20, 'total_ttc' => $ttc, 'montant_paye' => $paid,
                'status' => $paid >= $ttc ? 'payee' : ($paid > 0 ? 'partiellement_payee' : 'envoyee'), 'nature_operation' => $fr ? 'biens' : null,
                'remise_type' => 'percent', 'remise_value' => 0,
            ]);
            foreach ($lines as [$ref, $qty]) {
                $f->items()->create(['product_id' => $products[$ref]->id, 'quantity' => $qty, 'unit_price' => $ht($products[$ref]->sale_price), 'total' => $ht($products[$ref]->sale_price) * $qty]);
            }
            $pay($f, (float) $paid, now()->subDays(min(max(0, $ago - 10), 3 + $ci))->toDateString());
        }

        // two supplier orders
        foreach ([[0, 35, 'received', [['FER-RD-08', 300], ['FER-RD-10', 200]]], [3, 4, 'ordered', [['PEIN-GLY-BL', 40], ['DILU-1', 60]]]] as $n => [$fi, $ago, $status, $lines]) {
            $total = collect($lines)->sum(fn ($l) => $products[$l[0]]->purchase_price * $l[1]);
            $c = CommandeFournisseur::create([
                'fournisseur_id' => $fours[$fi]->id, 'numero' => sprintf('CF-%05d', $n + 1), 'date' => now()->subDays($ago)->toDateString(),
                'status' => $status, 'total' => round($total * 1.2, 2), 'total_ht' => $total, 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0,
            ]);
            foreach ($lines as [$ref, $qty]) {
                $c->items()->create(['product_id' => $products[$ref]->id, 'quantity' => $qty, 'unit_price' => $products[$ref]->purchase_price, 'total' => $products[$ref]->purchase_price * $qty]);
            }
        }
    }
}
