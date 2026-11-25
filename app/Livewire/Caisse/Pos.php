<?php

namespace App\Livewire\Caisse;

use App\Livewire\Concerns\RequiresPermission;
use App\Models\CaisseTicket;
use App\Models\Client;
use App\Models\Product;
use App\Models\Societe;
use App\Support\Caisse;
use App\Support\CaisseException;
use Livewire\Component;

/** Point of sale screen (2026-11-25): search or scan, cart, payment, ticket. */
class Pos extends Component
{
    use RequiresPermission;

    public string $search = '';

    public array $cart = [];

    public $client_id = '';

    public array $pay = ['especes' => '', 'carte' => '', 'cheque' => '', 'autre' => ''];

    public bool $payOpen = false;

    public ?int $lastTicketId = null;

    public function mount(): void
    {
        self::guard();
    }

    public static function guard(string $permission = 'caisse'): void
    {
        abort_unless(Societe::current()->caisse_enabled, 403, 'La caisse est désactivée. Activez-la dans Ma Société.');
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }

    public function addProduct(int $id): void
    {
        $p = Product::find($id);
        if (! $p) {
            return;
        }
        foreach ($this->cart as $i => $l) {
            if ((int) ($l['product_id'] ?? 0) === $p->id && (float) $l['remise_pct'] == 0.0) {
                $this->cart[$i]['quantity'] = (float) $l['quantity'] + 1;
                $this->lastTicketId = null;

                return;
            }
        }
        $this->cart[] = Caisse::lineFor($p);
        $this->lastTicketId = null;
    }

    /** Enter in the search box or a barcode scan: exact barcode / reference adds the product. */
    public function addByCode(string $code = ''): void
    {
        $code = trim($code !== '' ? $code : $this->search);
        if ($code === '') {
            return;
        }
        $p = Product::where('barcode', $code)->first() ?? Product::where('reference', $code)->first();
        if ($p) {
            $this->addProduct($p->id);
            $this->search = '';
        } elseif (($list = $this->results())->count() === 1) {
            $this->addProduct($list->first()->id);
            $this->search = '';
        }
    }

    public function inc(int $i): void
    {
        if (isset($this->cart[$i])) {
            $this->cart[$i]['quantity'] = (float) $this->cart[$i]['quantity'] + 1;
        }
    }

    public function dec(int $i): void
    {
        if (isset($this->cart[$i])) {
            $q = (float) $this->cart[$i]['quantity'] - 1;
            if ($q <= 0) {
                $this->remove($i);
            } else {
                $this->cart[$i]['quantity'] = $q;
            }
        }
    }

    public function remove(int $i): void
    {
        unset($this->cart[$i]);
        $this->cart = array_values($this->cart);
    }

    public function clear(): void
    {
        $this->reset(['cart', 'client_id', 'pay', 'payOpen']);
    }

    public function getTotalsProperty(): array
    {
        return Caisse::compute($this->cart);
    }

    public function openPay(): void
    {
        if (! $this->cart) {
            return;
        }
        $this->resetErrorBag();
        $this->payOpen = true;
    }

    /** Puts what is still due on one mode (exact amount). */
    public function exact(string $mode): void
    {
        $others = collect($this->pay)->except($mode)->sum(fn ($v) => (float) $v);
        $this->pay[$mode] = (string) max(0, round($this->totals['total_ttc'] - $others, 2));
    }

    public function cash(float $amount): void
    {
        $this->pay['especes'] = (string) $amount;
    }

    public function encaisser(): void
    {
        self::guard();
        $this->resetErrorBag();
        try {
            $client = $this->client_id ? Client::find($this->client_id) : null;
            $t = Caisse::encaisser($this->cart, $this->pay, $client);
        } catch (CaisseException $e) {
            $this->addError('pay', $e->getMessage());

            return;
        }
        $this->reset(['cart', 'client_id', 'pay', 'payOpen', 'search']);
        $this->lastTicketId = $t->id;
    }

    protected function results()
    {
        $q = trim($this->search);

        return Product::query()
            ->when($q !== '', fn ($b) => $b->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('reference', 'like', "%{$q}%")->orWhere('barcode', $q)))
            ->orderBy('name')->limit($q === '' ? 18 : 24)->get(['id', 'name', 'image_path', 'reference', 'sale_price', 'stock_qty', 'unit']);
    }

    public function render()
    {
        return view('livewire.caisse.pos', [
            'products' => $this->results(),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'last' => $this->lastTicketId ? CaisseTicket::find($this->lastTicketId) : null,
            'currency' => Societe::current()->currency ?: 'EUR',
        ])->layout('layouts.app');
    }
}
