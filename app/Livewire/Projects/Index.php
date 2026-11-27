<?php
namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Societe;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showModal = false;

    #[Validate('required|string|max:255')]
    public $project_name = '';

    #[Validate('required|string|max:255')]
    public $admin_name = '';

    #[Validate('required|email|unique:users,email')]
    public $admin_email = '';

    #[Validate('required|min:8')]
    public $admin_password = '';

    #[Validate('nullable|image|max:2048')]
    public $logo;

    public function mount()
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
    }

    public function create()
    {
        $this->reset(['project_name', 'admin_name', 'admin_email', 'admin_password', 'logo']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $project = Project::create(['name' => $this->project_name, 'is_active' => true]);

            $admin = new User([
                'name' => $this->admin_name,
                'email' => $this->admin_email,
                'role' => 'admin',
                'project_id' => $project->id,
            ]);
            $admin->password = Hash::make($this->admin_password);
            $admin->save();

            // Seed the project's own company settings row so Societe::current()
            // has something to find as soon as that admin logs in.
            // project_id/is_demo are intentionally not mass-assignable (see the
            // model's $fillable), so they're set directly here instead - this
            // runs as the superadmin, who must be able to target another
            // project's data, unlike the normal auto-fill-from-current-user path.
            $societe = new Societe([
                'name' => $this->project_name,
                'currency' => 'MAD',
                'tva_rate_default' => 20,
            ]);
            if ($this->logo) {
                $societe->logo_path = $this->logo->store('logos', 'public');
            }
            $societe->project_id = $project->id;
            $societe->is_demo = false;
            $societe->country_code = 'MA';
            $societe->save();
        });

        $this->showModal = false;
        session()->flash('success', 'Projet créé avec son administrateur.');
    }

    /** manual activation after a transfer / WhatsApp payment (2026-11-27) */
    public function setPlan($id, string $plan, int $months = 0)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        abort_unless(array_key_exists($plan, \App\Support\Plans::LABELS), 422);
        $p = Project::findOrFail($id);
        if ($plan === 'free') {
            $p->forceFill(['plan' => 'free', 'plan_ends_at' => null, 'plan_period' => null, 'plan_trial' => false])->save();
        } else {
            $from = ($p->plan === $plan && $p->plan_ends_at && $p->plan_ends_at->isFuture() && ! $p->plan_trial) ? $p->plan_ends_at : now();
            $p->forceFill(['plan' => $plan, 'plan_ends_at' => $from->copy()->addMonths(max(1, $months)), 'plan_period' => $months >= 12 ? 'annee' : 'trimestre', 'plan_trial' => false])->save();
        }
        session()->flash('success', $p->name.' : plan '.\App\Support\Plans::LABELS[$plan].($p->plan_ends_at ? ' jusqu’au '.$p->plan_ends_at->format('d/m/Y') : '').'.');
    }

    public function toggleActive($id)
    {
        $project = Project::findOrFail($id);
        $project->update(['is_active' => ! $project->is_active]);
    }

    public function render()
    {
        return view('livewire.projects.index', [
            'projects' => Project::withCount('users')->orderBy('name')->paginate(15),
        ])->layout('layouts.app');
    }
}
