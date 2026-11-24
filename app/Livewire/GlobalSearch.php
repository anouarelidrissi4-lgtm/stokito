<?php

namespace App\Livewire;

use App\Models\BonLivraison;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\Product;
use App\Models\Societe;
use App\Support\MenuItems;
use Livewire\Component;

/** Ctrl+K search in the app header (2026-11-24). Every query goes through the tenant scope and the user's permissions. */
class GlobalSearch extends Component
{
    public string $q = '';

    protected function like(): string
    {
        return '%'.addcslashes(trim($this->q), '%_\\').'%';
    }

    public function results(): array
    {
        $q = trim($this->q);
        if (mb_strlen($q) < 2) {
            return [];
        }
        $u = auth()->user();
        $t = $this->like();
        $money = fn ($v) => \App\Support\Fmt::money($v).' '.(Societe::current()->currency ?: '');
        $groups = [];

        // pages of the menu the user can open
        $s = Societe::current();
        $menu = $s->resolvedMenu();
        $pages = collect($menu['unsectioned'])->concat(collect($menu['sections'])->flatMap(fn ($x) => $x['items']))
            ->filter(fn ($i) => MenuItems::visibleFor($i, $u, $s))
            ->filter(fn ($i) => mb_stripos(__($i['label']), $q) !== false)
            ->take(4)->map(fn ($i) => ['title' => __($i['label']), 'sub' => __('Page'), 'url' => route($i['route'])])->values()->all();
        if ($pages) {
            $groups[__('Pages')] = $pages;
        }

        $doc = function ($model, string $perm, string $label, string $route, string $totalCol) use ($u, $t, $money, &$groups) {
            if (! $u->hasPermission($perm)) {
                return;
            }
            $rows = $model::with('client')
                ->where(fn ($w) => $w->where('numero', 'like', $t)->orWhereHas('client', fn ($c) => $c->where('name', 'like', $t)))
                ->latest('date')->limit(5)->get();
            if ($rows->isNotEmpty()) {
                $groups[$label] = $rows->map(fn ($d) => ['title' => $d->numero.' · '.($d->client->name ?? '-'),
                    'sub' => $d->date->format('d/m/Y').' · '.$money($d->{$totalCol}).' · '.\App\Support\Fmt::status($d->status), 'url' => route($route, $d->id)])->all();
            }
        };
        $doc(Facture::class, 'factures_consulter', __('Factures'), 'factures.edit', 'total_ttc');
        $doc(Devis::class, 'devis_consulter', __('Devis'), 'devis.edit', 'total_ttc');
        $doc(BonLivraison::class, 'bl_consulter', __('Bons de livraison'), 'bon-livraisons.edit', 'total');

        if ($u->hasPermission('gestion_clients')) {
            $rows = Client::where(fn ($w) => $w->where('name', 'like', $t)->orWhere('phone', 'like', $t)->orWhere('email', 'like', $t)->orWhere('ice', 'like', $t))
                ->orderBy('name')->limit(5)->get();
            if ($rows->isNotEmpty()) {
                $groups[__('Clients')] = $rows->map(fn ($c) => ['title' => $c->name, 'sub' => trim(($c->phone ?? '').' '.($c->address ?? '')),
                    'url' => route('clients.index', ['q' => $c->name])])->all();
            }
        }
        if ($u->hasPermission('produits_consulter')) {
            $rows = Product::where(fn ($w) => $w->where('name', 'like', $t)->orWhere('reference', 'like', $t)->orWhere('barcode', trim($this->q)))
                ->orderBy('name')->limit(5)->get();
            if ($rows->isNotEmpty()) {
                $groups[__('Produits')] = $rows->map(fn ($p) => ['title' => $p->name, 'sub' => $p->reference.' · '.__('Stock').' '.\App\Support\Fmt::qty($p->stock_qty),
                    'url' => route('products.index', ['q' => $p->reference ?: $p->name])])->all();
            }
        }
        if ($u->hasPermission('gestion_fournisseurs')) {
            $rows = Fournisseur::where('name', 'like', $t)->orderBy('name')->limit(5)->get();
            if ($rows->isNotEmpty()) {
                $groups[__('Fournisseurs')] = $rows->map(fn ($f) => ['title' => $f->name, 'sub' => (string) ($f->address ?? ''),
                    'url' => route('fournisseurs.index', ['q' => $f->name])])->all();
            }
        }

        return $groups;
    }

    public function render()
    {
        return view('livewire.global-search', ['groups' => $this->results()]);
    }
}
