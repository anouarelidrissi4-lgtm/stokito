<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected array $tables = [
        'categories', 'brands', 'products', 'clients', 'fournisseurs',
        'devis', 'bon_livraisons', 'commande_fournisseurs', 'factures',
        'tresorerie_comptes', 'reglements_clients', 'reglements_fournisseurs',
        'avoirs_clients', 'avoirs_fournisseurs', 'cheques_effets_fournisseurs',
        'mouvements_bancaires', 'bons_de_chargement', 'rappels',
        'documents_externes', 'stock_movements',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('is_demo')->default(false)->index();
            });
        }

        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_demo')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('is_demo');
            });
        }

        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn('is_demo');
        });
    }
};
