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
        'documents_externes', 'stock_movements', 'societe',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('project_id')->nullable()->index()->constrained('projects')->nullOnDelete();
            });
        }

        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('project_id')->nullable()->after('role')->constrained('projects')->nullOnDelete();
            $t->boolean('is_superadmin')->default(false)->after('project_id');
        });
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('project_id');
            });
        }

        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('project_id');
            $t->dropColumn('is_superadmin');
        });
    }
};
