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

    /** Opened from the "Scanner IA" menu entry (same page, scanner title). */
    public bool $scannerPage = false;

    public $reference_fournisseur = '';

    /* ---- payments on the purchase order (2026-11-27), same panel as the delivery note ---- */
    public string $payGroup = '';          // '' closed, 'cash' or 'papier'

    public $payMode = 'especes';

    public $payMontant = null;

    public $payDate = null;

    public $payCompte = null;

    public $payBanque = '';

    public $payNumero = '';

    public $payEcheance = null;

    public $payTireur = '';

    public $payNotes = '';

    public $payEditKey = null;             // payment being edited: its id, or its index before the first save

    public array $pendingPayments = [];    // payments typed before the order exists

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
        $this->scannerPage = request()->routeIs('scanner.facture');

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
        $limit = \App\Support\Plans::limit('scans');

        if (auth()->user()->isDemo()) {
            $this->addError('scanFile', 'Le scanner IA est désactivé dans la démo. Créez votre compte gratuit pour l’essayer sur vos factures.');
        } elseif (! $scanner->isConfigured()) {
            $this->addError('scanFile', 'Le scanner IA n’est pas encore activé sur ce serveur.');
        } elseif ($limit === 0) {
            $this->addError('scanFile', \App\Support\Plans::aiNotIncluded('Le scanner IA'));
        } elseif ($limit !== null && \App\Models\AiScan::usedThisMonth() >= $limit) {
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
        $this->new_product_name = trim((string) $this->new_product_name);
        $this->validate([
            'new_product_reference' => ['required', 'string', 'max:255', \App\Support\Tenant::unique('products', 'reference')],
            'new_product_barcode' => ['nullable', 'string', 'max:255', \App\Support\Tenant::unique('products', 'barcode')],
            'new_product_name' => ['required', 'string', 'max:255', \App\Models\Product::uniqueNameRule()],
            'new_product_unit' => 'required|string|max:50',
            'new_product_purchase_price' => 'required|numeric|min:0',
            'new_product_sale_price' => 'required|numeric|min:0',
            'new_product_wholesale_price' => 'nullable|numeric|min:0',
            'new_product_stock_qty' => 'required|integer|min:0',
            'new_product_stock_alert_threshold' => 'nullable|integer|min:0',
        ], ['new_product_reference.unique' => 'Cette référence existe déjà.', 'new_product_name.unique' => 'Un produit porte déjà ce nom.', 'new_product_barcode.unique' => 'Ce code-barres est déjà utilisé par un autre produit.']);

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

    private function canPay(): bool
    {
        return (bool) auth()->user()?->hasPermission('gestion_reglements_fournisseurs');
    }

    public function openPayment(string $group): void
    {
        abort_unless($this->canPay(), 403);
        $this->resetValidation();
        // a payment type ('especes', 'cheque'…) or a group ('cash', 'papier')
        $mode = in_array($group, ['especes', 'virement', 'versement', 'transfert', 'cheque', 'effet'], true) ? $group : null;
        if ($mode) {
            $group = in_array($mode, \App\Support\SalesLists::MODES_PAPIER, true) ? 'papier' : 'cash';
        }
        $this->payEditKey = null;
        $this->payGroup = in_array($group, ['cash', 'papier'], true) ? $group : '';
        $this->payMode = $mode ?? ($group === 'papier' ? 'cheque' : 'especes');
        $this->payDate = now()->format('Y-m-d');
        $this->payEcheance = $group === 'papier' ? now()->format('Y-m-d') : null;
        $this->payBanque = '';
        $this->payNumero = '';
        $this->payTireur = '';
        $this->payNotes = '';
        $this->payCompte = null;
        $this->payMontant = max(0, round($this->resteAPayer(), 2)) ?: null;
    }

    public function closePayment(): void
    {
        $this->payGroup = '';
        $this->payEditKey = null;
    }

    private function fillPayment(array $p): void
    {
        $this->payMode = $p['mode'];
        $this->payGroup = in_array($p['mode'], \App\Support\SalesLists::MODES_PAPIER, true) ? 'papier' : 'cash';
        $this->payMontant = (float) $p['montant'];
        $this->payDate = $p['date'];
        $this->payCompte = $p['tresorerie_compte_id'] ?? null;
        $this->payBanque = (string) ($p['banque'] ?? '');
        $this->payNumero = (string) ($p['numero_cheque'] ?? '');
        $this->payEcheance = $p['date_echeance'] ?? null;
        $this->payTireur = (string) ($p['tireur'] ?? '');
        $this->payNotes = (string) ($p['notes'] ?? '');
    }

    private function paymentRow($r, ?string $obs): array
    {
        return ['montant' => (float) $r->montant, 'date' => $r->date?->format('Y-m-d'), 'mode' => $r->mode, 'tresorerie_compte_id' => $r->tresorerie_compte_id,
            'banque' => $r->banque, 'numero_cheque' => $r->numero_cheque, 'date_echeance' => $r->date_echeance?->format('Y-m-d'), 'tireur' => $r->tireur, 'notes' => $obs];
    }

    public function editPayment($key): void
    {
        abort_unless($this->canPay(), 403);
        $this->resetValidation();
        if ($this->commande) {
            $r = \App\Models\ReglementFournisseur::where('commande_fournisseur_id', $this->commande->id)->findOrFail((int) $key);
            $p = $this->paymentRow($r, $r->notes);
        } elseif (! ($p = $this->pendingPayments[(int) $key] ?? null)) {
            return;
        }
        $this->fillPayment($p);
        $this->payEditKey = (int) $key;
    }

    private function updatePayment(CommandeFournisseur $cf, int $id, array $data): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($cf, $id, $data) {
            $r = \App\Models\ReglementFournisseur::where('commande_fournisseur_id', $cf->id)->findOrFail($id);
            $r->removeEffects();
            $r->update($data);
            $r->refresh()->applyEffects();
            $this->syncCheque($cf, $r);
        });
    }

    /** a cheque or an effet also goes to the "Chèques / effets fournisseurs" follow-up, like the Règlements page */
    private function syncCheque(CommandeFournisseur $cf, \App\Models\ReglementFournisseur $r): void
    {
        $existing = \App\Models\ChequeEffetFournisseur::where('reglement_fournisseur_id', $r->id)->first();
        if (! in_array($r->mode, \App\Support\SalesLists::MODES_PAPIER, true)) {
            if ($existing && $existing->status === 'en_attente') {
                $existing->delete();
            }

            return;
        }
        $vals = [
            'fournisseur_id' => $cf->fournisseur_id,
            'reglement_fournisseur_id' => $r->id,
            'numero_cheque' => $r->numero_cheque ?: '—',
            'banque' => $r->banque,
            'montant' => $r->montant,
            'date_emission' => $r->date,
            'date_echeance' => $r->date_echeance ?? $r->date,
            'notes' => trim(implode(' · ', array_filter([$r->mode === 'effet' ? 'Effet' : null, $r->tireur, $r->notes]))) ?: null,
        ];
        $existing ? $existing->update($vals) : \App\Models\ChequeEffetFournisseur::create($vals + ['status' => 'en_attente']);
    }

    /** total of the order as typed, minus what is already paid */
    public function resteAPayer(): float
    {
        $paid = $this->commande ? (float) $this->commande->reglements()->sum('montant') : array_sum(array_column($this->pendingPayments, 'montant'));

        return round((float) $this->totalTtc - $paid, 2);
    }

    public function addPayment(): void
    {
        abort_unless($this->canPay(), 403);
        $modes = $this->payGroup === 'papier' ? \App\Support\SalesLists::MODES_PAPIER : ['especes', 'virement', 'versement', 'transfert'];
        if ($this->payEditKey !== null) {
            $modes = array_keys(\App\Support\SalesLists::MODES);   // an older payment may use another type
        }
        $this->validate([
            'payMode' => 'required|in:'.implode(',', $modes),
            'payMontant' => 'required|numeric|min:0.01',
            'payDate' => 'required|date',
            'payEcheance' => 'nullable|date',
            'payBanque' => 'nullable|string|max:100',
            'payNumero' => 'nullable|string|max:60',
            'payTireur' => 'nullable|string|max:120',
            'payNotes' => 'nullable|string|max:500',
        ], [], ['payMontant' => 'montant', 'payDate' => 'date', 'payTireur' => 'nom sur le chèque', 'payNotes' => 'observation']);
        if ($this->payCompte && ! \App\Models\TresorerieCompte::find($this->payCompte)) {
            $this->addError('payCompte', 'Compte introuvable.');

            return;
        }
        $data = [
            'montant' => round((float) $this->payMontant, 2),
            'date' => $this->payDate,
            'mode' => $this->payMode,
            'tresorerie_compte_id' => $this->payCompte ?: null,
            'banque' => in_array($this->payMode, ['cheque', 'effet', 'virement', 'versement'], true) ? (trim($this->payBanque) ?: null) : null,
            'numero_cheque' => trim($this->payNumero) ?: null,
            'date_echeance' => $this->payGroup === 'papier' ? ($this->payEcheance ?: null) : null,
            'tireur' => $this->payGroup === 'papier' ? (trim((string) $this->payTireur) ?: null) : null,
            'notes' => trim((string) $this->payNotes) ?: null,
        ];
        if ($this->payEditKey !== null) {
            if ($this->commande) {
                $this->updatePayment($this->commande, (int) $this->payEditKey, $data);
                $this->commande->refresh();
            } elseif (isset($this->pendingPayments[(int) $this->payEditKey])) {
                $this->pendingPayments[(int) $this->payEditKey] = $data;
            }
        } elseif ($this->commande) {
            $this->createPayment($this->commande, $data);
            $this->commande->refresh();
        } else {
            $this->pendingPayments[] = $data;
        }
        $this->payGroup = '';
        $this->payEditKey = null;
    }

    public function removePayment($key): void
    {
        abort_unless($this->canPay(), 403);
        $this->payGroup = '';
        $this->payEditKey = null;
        if ($this->commande) {
            $r = \App\Models\ReglementFournisseur::where('commande_fournisseur_id', $this->commande->id)->findOrFail((int) $key);
            \App\Support\Tenant::tryDelete($r);
            $this->commande->refresh();
        } else {
            unset($this->pendingPayments[(int) $key]);
            $this->pendingPayments = array_values($this->pendingPayments);
        }
    }

    private function createPayment(CommandeFournisseur $cf, array $data): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($cf, $data) {
            $r = \App\Models\ReglementFournisseur::create($data + [
                'fournisseur_id' => $cf->fournisseur_id,
                'commande_fournisseur_id' => $cf->id,
            ]);
            $r->applyEffects();
            $this->syncCheque($cf, $r);
        });
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
        if ($this->canPay()) {
            foreach ($this->pendingPayments as $p) {
                $this->createPayment($this->commande->fresh(), $p);
            }
        }
        $this->pendingPayments = [];
        // payments follow the order's supplier if it was changed
        \App\Models\ReglementFournisseur::where('commande_fournisseur_id', $this->commande->id)->where('fournisseur_id', '!=', $this->commande->fournisseur_id)
            ->update(['fournisseur_id' => $this->commande->fournisseur_id]);

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
            'scansLeft' => ($l = \App\Support\Plans::limit('scans')) === null ? null : max(0, $l - \App\Models\AiScan::usedThisMonth()),
            'canPay' => $this->canPay(),
            'payments' => $this->commande ? $this->commande->reglements()->with('tresorerieCompte')->orderBy('date')->orderBy('id')->get() : null,
            'banks' => \App\Support\Banks::for(),
            'comptes' => \App\Models\TresorerieCompte::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
