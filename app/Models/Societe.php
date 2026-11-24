<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Societe extends Model
{
    use ScopedToDemoMode;

    protected $table = 'societe';

    protected $fillable = [
        'name', 'country', 'country_code', 'city', 'address', 'phone', 'phone2', 'fax', 'email', 'ice',
        'identifiant_fiscal', 'registre_commerce', 'patente', 'cnss', 'conditions_paiement', 'logo_path',
        'currency', 'tva_rate_default', 'qty_decimals', 'price_decimals',
        'tva_franchise', 'penalites_taux', 'indemnite_40', 'escompte_neant', 'tva_debits', 'nature_operation_default', 'devis_validite_jours', 'relances_auto',
        'stock_threshold_default', 'facture_type', 'stock_verification',
        'show_ref_facture', 'show_ref_bl', 'bons_chargement_enabled', 'caisse_enabled', 'menu_config',
    ];

    protected $casts = [
        'stock_verification' => 'boolean',
        'tva_franchise' => 'boolean',
        'indemnite_40' => 'boolean',
        'escompte_neant' => 'boolean',
        'tva_debits' => 'boolean',
        'relances_auto' => 'boolean',
        'show_ref_facture' => 'boolean',
        'show_ref_bl' => 'boolean',
        'bons_chargement_enabled' => 'boolean',
        'menu_config' => 'array',
    ];

    /** @var array<string, self> memo of current(), per user/project/demo flag */
    protected static array $currentCache = [];

    /** Forget the memoised companies (tests, long-running workers). */
    public static function flushCurrent(): void
    {
        static::$currentCache = [];
    }

    public static function current(): self
    {
        $user = auth()->user();
        if (! $user || ! $user->project_id) {
            // Guest / superadmin without a project: in-memory defaults, never saved
            // (this used to insert a new empty 'Ma Societe' row on every call).
            return new static(['name' => 'Ma Societe', 'country' => 'Maroc', 'currency' => 'MAD', 'tva_rate_default' => 20, 'qty_decimals' => 2, 'price_decimals' => 2]);
        }

        // memoised per user for the request: Fmt::money() calls this for every amount on a page
        $cache = &static::$currentCache;
        $key = $user->id.'-'.$user->project_id.'-'.(int) $user->is_demo;
        if (isset($cache[$key]) && $cache[$key]->exists) {
            return $cache[$key];
        }

        return $cache[$key] = static::firstOrCreate([], [
            'name' => 'Ma Societe',
            'country' => 'Maroc',
            'currency' => 'MAD',
            'tva_rate_default' => 20,
            'qty_decimals' => 2,
            'price_decimals' => 2,
            'facture_type' => 'sans_entete',
            'show_ref_facture' => true,
            'show_ref_bl' => true,
            'bons_chargement_enabled' => true,
        ]);
    }

    /**
     * Base64 data URI for the logo, safe to embed directly in a dompdf view
     * (dompdf's remote/disk image loading can be unreliable, so PDF templates
     * should always use this instead of Storage::url()).
     */
    public function logoDataUri(): ?string
    {
        if (! $this->logo_path || ! Storage::disk('public')->exists($this->logo_path)) {
            return null;
        }

        $path = Storage::disk('public')->path($this->logo_path);
        $mime = Storage::disk('public')->mimeType($this->logo_path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }

    /**
     * Sidebar sections/items, with this company's Menu lateral customizations
     * (if any) merged over the App\Support\MenuItems catalog defaults.
     */
    public function resolvedMenu(): array
    {
        return \App\Support\MenuItems::resolve($this->menu_config);
    }
}
