<?php

namespace App\Livewire\Inscriptions;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Superadmin: every company that signed up, with its activity (2026-11-24). Reads raw tables, demo excluded. */
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $periode = 'all'; // 7 | 30 | 90 | all

    #[Url]
    public bool $actifs = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
    }

    public function updating($name): void
    {
        $this->resetPage();
    }

    protected function base()
    {
        $count = fn ($table) => DB::table($table)->whereColumn("{$table}.project_id", 'p.id')->where("{$table}.is_demo", false)->selectRaw('count(*)');
        $first = fn ($col) => DB::table('users')->whereColumn('users.project_id', 'p.id')->where('users.is_demo', false)
            ->orderByRaw("users.role = 'admin' desc")->orderBy('users.id')->limit(1)->select("users.{$col}");

        return DB::table('projects as p')
            ->where('p.name', '!=', 'Demo')
            ->whereNotExists(fn ($q) => $q->from('users')->whereColumn('users.project_id', 'p.id')->where('users.is_demo', true))
            ->select('p.id', 'p.name', 'p.country_code', 'p.contact_phone', 'p.signup_source', 'p.is_active', 'p.created_at')
            ->selectSub(DB::table('societe')->whereColumn('societe.project_id', 'p.id')->where('societe.is_demo', false)->limit(1)->select('societe.name'), 'societe')
            ->selectSub($first('name'), 'admin_name')
            ->selectSub($first('email'), 'admin_email')
            ->selectSub($first('email_verified_at'), 'verified_at')
            ->selectSub($count('products'), 'nb_products')
            ->selectSub($count('clients'), 'nb_clients')
            ->selectSub($count('devis'), 'nb_devis')
            ->selectSub($count('factures'), 'nb_factures')
            ->selectSub(DB::table('factures')->whereColumn('factures.project_id', 'p.id')->where('factures.is_demo', false)->selectRaw('max(factures.created_at)'), 'last_facture')
            ->selectSub(DB::table('devis')->whereColumn('devis.project_id', 'p.id')->where('devis.is_demo', false)->selectRaw('max(devis.created_at)'), 'last_devis');
    }

    public function render()
    {
        $all = DB::query()->fromSub($this->base(), 'x');
        $stats = (clone $all)->selectRaw("count(*) as total,
            sum(created_at >= ?) as d7, sum(created_at >= ?) as d30,
            sum(nb_devis + nb_factures > 0) as actifs, sum(verified_at is not null) as verifies,
            sum(signup_source = 'self') as self", [now()->subDays(7), now()->subDays(30)])->first();
        $pays = (clone $all)->selectRaw('coalesce(country_code, \'MA\') as cc, count(*) as n')->groupBy('cc')->orderByDesc('n')->pluck('n', 'cc');

        $rows = (clone $all)
            ->when($this->periode !== 'all', fn ($q) => $q->where('created_at', '>=', now()->subDays((int) $this->periode)))
            ->when($this->actifs, fn ($q) => $q->whereRaw('nb_devis + nb_factures > 0'))
            ->when(trim($this->search) !== '', function ($q) {
                $t = '%'.trim($this->search).'%';
                $q->where(fn ($w) => $w->where('name', 'like', $t)->orWhere('societe', 'like', $t)->orWhere('admin_email', 'like', $t)->orWhere('admin_name', 'like', $t));
            })
            ->orderByDesc('created_at')->paginate(25);

        return view('livewire.inscriptions.index', ['rows' => $rows, 'stats' => $stats, 'pays' => $pays])->layout('layouts.app');
    }
}
