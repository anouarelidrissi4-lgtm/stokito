<?php
namespace App\Livewire\CommandeFournisseurs;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\CommandeFournisseur;
use App\Models\Fournisseur;
use App\Models\Product;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use RequiresPermission;
    use \App\Livewire\Concerns\CopilotPrefill;
    use WithFileUploads;

    public ?CommandeFournisseur $commande = null;

    #[Validate('required|exists:fournisseurs,id')]
    public $fournisseur_id = '';

    #[Validate('required|date')]
    public $date;

    public $status = 'draft';

    public $tva_rate = 20;

    #[Validate('required|in:percent,fixed')]
    public $remise_type = 'percent';

    #[Validate('nullable|numeric|min:0')]
    public $remise_value = 0;

    public array $items = [];

    public $reference_fournisseur = '';

    // --- Scanner IA d'une facture fournisseur (2026-11-24) ---
    public $scanFile = null;

    /** Supplier read on the invoice but not in the list yet (offered for creation). */
    public ?array $scanSupplier = null;

    public ?string $scanMessage = null;

    /** Bumped at each scan so the product search boxes re-initialise with the matched names. */
    public int $scanRun = 0;

    // --- Recherche d'une commande existante par numero ---
    public string $numeroSearch = '';

    public array $numeroSearchResults = [];

    public bool $numeroSearchPerformed = false;

    // --- Quick-create: Product ---
    public bool $showProductModal = false;

    public ?int $productModalItemIndex = null;

    public $new_product_reference = '';

    public $new_product_barcode = '';

    public ?int $new_product_category_id = null;

    public $new_product_name = '';

    public ?int $new_product_brand_id = null;

    public ?int $new_product_fournisseur_id = null;

    public $new_product_unit = 'piece';

    public $new_product_stock_alert_threshold = null;

    public $new_product_purchase_price = 0;

    public $new_product_sale_price = 0;

    public $new_product_wholesale_price = null;

    public $new_product_stock_qty = 0;

    public function mount($commandeId = null)
    {
        $this->requirePermission('gestion_commandes_fournisseurs');

        $this->date = now()->format('Y-m-d');

        if ($commandeId) {
            $this->commande = CommandeFournisseur::with('items')->findOrFail($commandeId);
            $this->fournisseur_id = $this->commande->fournisseur_id;
            $this->date = $this->commande->date->format('Y-m-d');
            $this->status = $this->commande->status;
            $this->reference_fournisseur = $this->commande->reference_fournisseur ?? '';
            $this->tva_rate = $this->commande->tva_rate ?? 20;
            $this->remise_type = $this->commande->remise_type ?? 'percent';
            $this->remise_value = $this->commande->remise_value ?? 0;
            $this->items = $this->commande->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
            ])->toArray();
        } else {
            $this->tva_rate = \App\Models\Societe::current()->tva_rate_default;
            $this->addItem();
        }
        $this->applyCopilotPrefill();
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'quantity' => 1, 'unit_price' => 0];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key)
    {
        if (substr_count((string) $key, '.') !== 1) {
            return; // whole array replaced, nothing to sync
        }
        [$index, $field] = explode('.', $key);
        if ($field === 'product_id' && $value) {
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['unit_price'] = (float) $product->purchase_price;
            }
            if (isset($this->items[$index]['scan'])) {
                $this->items[$index]['scan']['status'] = 'ok';
            }
        }
    }

    /** The file input uploads, then the scan starts by itself. */
    public function updatedScanFile(): void
    {
        $this->scanInvoice();
    }

    public function scanInvoice(): void
    {
        $this->scanMessage = null;
        $this->resetErrorBag('scanFile');
        $this->validate(['scanFile' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240'], [
            'scanFile.mimes' => 'Envoyez une photo (JPG, PNG) ou un PDF.',
            'scanFile.max' => 'Fichier trop lourd (10 Mo maximum).',
        ]);
        $scanner = app(\App\Support\InvoiceScanner::class);
        $limit = (int) config('services.anthropic.scans_per_month', 30);

        if (auth()->user()->isDemo()) {
            $this->addError('scanFile', 'Le scanner IA est désactivé dans la démo. Créez votre compte gratuit pour l’essayer sur vos factures.');
        } elseif (! $scanner->isConfigured()) {
            $this->addError('scanFile', 'Le scanner IA n’est pas encore activé sur ce serveur.');
        } elseif (\App\Models\AiScan::usedThisMonth() >= $limit) {
            $this->addError('scanFile', "Vous avez utilisé vos {$limit} scans de ce mois. Le compteur repart le 1er du mois prochain.");
        }
        if ($this->getErrorBag()->has('scanFile')) {
            $this->scanFile = null;

            return;
        }

        @set_time_limit(180);
        $log = ['user_id' => auth()->id(), 'kind' => 'facture_fournisseur'];
        try {
            $res = $scanner->scan($this->scanFile->getRealPath(), (string) $this->scanFile->getMimeType());
        } catch (\App\Support\InvoiceScanException $e) {
            \App\Models\AiScan::create($log + ['status' => 'failed', 'error' => mb_substr($e->getPrevious()?->getMessage() ?? $e->getMessage(), 0, 2000),
                'input_tokens' => $e->inputTokens, 'output_tokens' => $e->outputTokens,
                'cost_usd' => \App\Support\InvoiceScanner::cost($e->inputTokens, $e->outputTokens)]);
            $this->addError('scanFile', $e->getMessage());

            return;
        } finally {
            try {
                $this->scanFile?->delete();
            } catch (\Throwable) {
            }
            $this->scanFile = null;
        }
        $d = $res['data'];
        \App\Models\AiScan::create($log + ['status' => 'ok', 'input_tokens' => $res['input_tokens'], 'output_tokens' => $res['output_tokens'],
            'cost_usd' => \App\Support\InvoiceScanner::cost($res['input_tokens'], $res['output_tokens'])]);

        if (empty($d['est_une_facture']) || empty($d['lignes'])) {
            $this->addError('scanFile', 'Aucune ligne d’article trouvée : ce document ne ressemble pas à une facture fournisseur.');

            return;
        }

        $f = $d['fournisseur'] ?? [];
        $known = \App\Support\InvoiceScanner::matchFournisseur($f);
        $this->scanSupplier = null;
        if ($known) {
            $this->fournisseur_id = $known->id;
        } elseif (trim((string) ($f['nom'] ?? '')) !== '') {
            $this->scanSupplier = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $f);
        }
        if (! empty($d['date']) && ($dt = \DateTime::createFromFormat('!Y-m-d', $d['date']))) {
            $this->date = $dt->format('Y-m-d');
        }
        if (! empty($d['numero_facture'])) {
            $this->reference_fournisseur = mb_substr(trim($d['numero_facture']), 0, 255);
        }

        $lines = \App\Support\InvoiceScanner::matchLines($d['lignes']);
        $rates = collect($lines)->pluck('scan.tva')->filter(fn ($r) => $r !== null)->map(fn ($r) => (string) (float) $r)->countBy()->sortDesc();
        if ($rates->isNotEmpty()) {
            $this->tva_rate = (float) $rates->keys()->first();
        }
        if (! empty($d['remise_globale_ht']) && (float) $d['remise_globale_ht'] > 0) {
            $this->remise_type = 'fixed';
            $this->remise_value = round((float) $d['remise_globale_ht'], 2);
        }
        $kept = array_values(array_filter($this->items, fn ($i) => ! empty($i['product_id'])));
        $this->items = array_merge($kept, $lines);
        $this->scanRun++;

        $c = collect($lines)->countBy(fn ($l) => $l['scan']['status']);
        $this->scanMessage = count($lines).' ligne(s) lue(s) : '.($c['ok'] ?? 0).' produit(s) reconnu(s)'
            .(($c['check'] ?? 0) ? ', '.$c['check'].' à vérifier' : '').(($c['new'] ?? 0) ? ', '.$c['new'].' à créer' : '')
            .'. Vérifiez les quantités et les prix, puis enregistrez.';
    }

    /** Creates the supplier read on the invoice and selects it. */
    public function createScannedSupplier(): void
    {
        if (! $this->scanSupplier) {
            return;
        }
        $s = $this->scanSupplier;
        $f = Fournisseur::create([
            'name' => mb_substr($s['nom'] ?? 'Fournisseur', 0, 255),
            'ice' => $s['identifiant'] ?? null,
            'phone' => $s['telephone'] ?? null,
            'email' => filter_var($s['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: null,
            'address' => $s['adresse'] ?? null,
        ]);
        $this->fournisseur_id = $f->id;
        $this->scanSupplier = null;
    }

    /**
     * Called by the barcode-scanner component: find the product and add/increment a line.
     */
    public function onBarcodeScanned(string $code): void
    {
        $product = Product::where('barcode', $code)->first();

        if (! $product) {
            $this->addError('items', "Aucun produit trouve pour le code-barres {$code}.");

            return;
        }

        foreach ($this->items as $index => $item) {
            if ((int) ($item['product_id'] ?? 0) === $product->id) {
                $this->items[$index]['quantity'] = (float) $this->items[$index]['quantity'] + 1;

                return;
            }
        }

        foreach ($this->items as $index => $item) {
            if (empty($item['product_id'])) {
                $this->items[$index]['product_id'] = $product->id;
                $this->items[$index]['unit_price'] = (float) $product->purchase_price;

                return;
            }
        }

        $this->items[] = [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => (float) $product->purchase_price,
        ];
    }

    // --- Recherche d'une commande existante par numero ---
    public function searchByNumero()
    {
        $this->numeroSearchPerformed = true;

        if (trim($this->numeroSearch) === '') {
            $this->numeroSearchResults = [];

            return;
        }

        $this->numeroSearchResults = CommandeFournisseur::query()
            ->with('fournisseur')
            ->where('numero', 'like', '%'.$this->numeroSearch.'%')
            ->latest('date')
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'numero' => $c->numero,
                'fournisseur' => $c->fournisseur->name ?? '-',
                'date' => $c->date->format('d/m/Y'),
                'total' => $c->total,
            ])
            ->toArray();
    }

    public function clearNumeroSearch()
    {
        $this->numeroSearch = '';
        $this->numeroSearchResults = [];
        $this->numeroSearchPerformed = false;
    }

    // --- Quick-create product ---
    public function openProductModal($itemIndex = null)
    {
        $this->productModalItemIndex = $itemIndex;
        $this->reset([
            'new_product_reference', 'new_product_barcode', 'new_product_category_id',
            'new_product_name', 'new_product_brand_id', 'new_product_fournisseur_id',
            'new_product_unit', 'new_product_stock_alert_threshold', 'new_product_purchase_price',
            'new_product_sale_price', 'new_product_wholesale_price', 'new_product_stock_qty',
        ]);
        $this->new_product_unit = 'piece';
        $scan = is_null($itemIndex) ? null : ($this->items[$itemIndex]['scan'] ?? null);
        if ($scan) {
            $this->new_product_name = mb_substr($scan['text'] ?? '', 0, 255);
            $this->new_product_reference = $scan['reference'] ?? '';
            $this->new_product_barcode = $scan['barcode'] ?? '';
            $this->new_product_purchase_price = (float) ($this->items[$itemIndex]['unit_price'] ?? 0);
            $this->new_product_fournisseur_id = $this->fournisseur_id ? (int) $this->fournisseur_id : null;
        }
        $this->resetValidation();
        $this->showProductModal = true;
    }

    /**
     * Called by the barcode-scanner component inside the "Nouveau produit" modal.
     */
    public function onNewProductBarcodeScanned(string $code): void
    {
        $this->new_product_barcode = $code;
    }

    public function saveProduct()
    {
        $this->validate([
            'new_product_reference' => ['required', 'string', 'max:255', \App\Support\Tenant::unique('products', 'reference')],
            'new_product_barcode' => ['nullable', 'string', 'max:255', \App\Support\Tenant::unique('products', 'barcode')],
            'new_product_name' => 'required|string|max:255',
            'new_product_unit' => 'required|string|max:50',
            'new_product_purchase_price' => 'required|numeric|min:0',
            'new_product_sale_price' => 'required|numeric|min:0',
            'new_product_wholesale_price' => 'nullable|numeric|min:0',
            'new_product_stock_qty' => 'required|integer|min:0',
            'new_product_stock_alert_threshold' => 'nullable|integer|min:0',
        ]);

        $product = Product::create([
            'reference' => $this->new_product_reference,
            'barcode' => $this->new_product_barcode ?: null,
            'name' => $this->new_product_name,
            'category_id' => $this->new_product_category_id,
            'brand_id' => $this->new_product_brand_id,
            'fournisseur_id' => $this->new_product_fournisseur_id,
            'unit' => $this->new_product_unit,
            'purchase_price' => $this->new_product_purchase_price,
            'sale_price' => $this->new_product_sale_price,
            'wholesale_price' => $this->new_product_wholesale_price ?: null,
            'stock_qty' => $this->new_product_stock_qty,
            'stock_alert_threshold' => $this->new_product_stock_alert_threshold ?: null,
        ]);

        if (! is_null($this->productModalItemIndex) && isset($this->items[$this->productModalItemIndex])) {
            $this->items[$this->productModalItemIndex]['product_id'] = $product->id;
            $this->items[$this->productModalItemIndex]['unit_price'] = (float) $product->purchase_price;
            if (isset($this->items[$this->productModalItemIndex]['scan'])) {
                $this->items[$this->productModalItemIndex]['scan']['status'] = 'ok';
            }
        } else {
            $this->items[] = [
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => (float) $product->purchase_price,
            ];
        }

        $this->showProductModal = false;
        session()->flash('success', 'Produit "'.$product->name.'" cree et ajoute.');
    }

    public function getBrutHtProperty()
    {
        return collect($this->items)->sum(fn ($i) => (float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0));
    }

    public function getMontantRemiseProperty()
    {
        $brut = $this->brutHt;

        if ($this->remise_type === 'percent') {
            return round($brut * ((float) ($this->remise_value ?: 0)) / 100, 2);
        }

        return min((float) ($this->remise_value ?: 0), $brut);
    }

    public function getTotalHtProperty()
    {
        return $this->brutHt - $this->montantRemise;
    }

    public function getTotalTtcProperty()
    {
        return $this->totalHt * (1 + ((float) $this->tva_rate) / 100);
    }

    public function save()
    {
        $this->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'date' => 'required|date',
            'remise_type' => 'required|in:percent,fixed',
            'remise_value' => 'nullable|numeric|min:0',
            'reference_fournisseur' => 'nullable|string|max:255',
        ]);

        if (empty($this->items)) {
            $this->addError('items', 'Ajoutez au moins une ligne.');

            return;
        }

        $this->validate([
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $data = [
            'fournisseur_id' => $this->fournisseur_id,
            'date' => $this->date,
            'status' => $this->status,
            'tva_rate' => $this->tva_rate,
            'remise_type' => $this->remise_type,
            'remise_value' => $this->remise_value ?: 0,
            'reference_fournisseur' => trim((string) $this->reference_fournisseur) ?: null,
        ];

        // Choosing "Reçue" in the form must add the goods to the stock, like "Marquer reçue" in the list
        // (2026-11-24: it used to only change the status, and the list then hid the button, so stock never moved).
        $receiveNow = $this->status === 'received' && ($this->commande?->status ?? null) !== 'received';
        if ($receiveNow) {
            $data['status'] = $this->commande->status ?? 'ordered';
        }

        if ($this->commande) {
            $this->commande->update($data);
            $this->commande->items()->delete();
        } else {
            $data['numero'] = \App\Support\Tenant::nextNumero(CommandeFournisseur::class, 'CF-');
            $this->commande = CommandeFournisseur::create($data);
        }

        foreach ($this->items as $item) {
            $total = (float) $item['quantity'] * (float) $item['unit_price'];
            $this->commande->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $total,
            ]);
        }

        $this->commande->recalculateTotals();

        if ($receiveNow) {
            $this->commande->load('items.product')->markReceived();
            session()->flash('success', 'Bon de commande enregistré et reçu : stock mis à jour.');

            return $this->redirect(route('commande-fournisseurs.index'), navigate: true);
        }

        session()->flash('success', 'Bon de commande enregistré.');

        return $this->redirect(route('commande-fournisseurs.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.commande-fournisseurs.form', [
            'fournisseurs' => Fournisseur::orderBy('name')->get(),
            'products' => Product::with('brand')->orderBy('name')->get(),
            'categories' => \App\Models\Category::orderBy('name')->get(),
            'brands' => \App\Models\Brand::orderBy('name')->get(),
            'scansLeft' => max(0, (int) config('services.anthropic.scans_per_month', 30) - \App\Models\AiScan::usedThisMonth()),
        ])->layout('layouts.app');
    }
}
