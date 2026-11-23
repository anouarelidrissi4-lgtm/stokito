<?php
namespace App\Livewire\Utilisateurs;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function mount()
    {
        // A "pure" superadmin with no project of their own manages users from
        // the Projets screen instead; a superadmin who is also a project admin
        // (e.g. the owner) still manages their own project's users here.
        abort_if(auth()->user()->isSuperAdmin() && ! auth()->user()->project_id, 403);
        abort_unless(auth()->user()->isAdmin() || auth()->user()->hasPermission('gestion_utilisateurs'), 403);
    }

    public function delete($id)
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');

            return;
        }

        $this->scopedUser($id)->delete();
        session()->flash('success', 'Utilisateur supprimé.');
    }

    /**
     * Fetch a user, guaranteeing it belongs to the current admin's own project
     * and is never the global superadmin account.
     */
    protected function scopedUser($id): User
    {
        return User::where('id', $id)
            ->where('project_id', auth()->user()->project_id)
            ->where('is_superadmin', false)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.utilisateurs.index', [
            'users' => User::where('project_id', auth()->user()->project_id)
                ->where('is_superadmin', false)
                ->orderBy('name')
                ->paginate(15),
        ])->layout('layouts.app');
    }
}
