<?php

namespace App\Livewire\Utilisateurs;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Form extends Component
{
    public ?User $user = null;

    #[Validate('required|string|max:255')]
    public $first_name = '';

    #[Validate('required|string|max:255')]
    public $last_name = '';

    #[Validate('required|string|max:255')]
    public $login = '';

    #[Validate('required|email|max:255')]
    public $email = '';

    public $password = '';

    #[Validate('required|in:active,inactive')]
    public $status = 'active';

    public array $permissions = [];

    public function mount($userId = null)
    {
        abort_if(auth()->user()->isSuperAdmin() && ! auth()->user()->project_id, 403);
        abort_unless(auth()->user()->isAdmin() || auth()->user()->hasPermission('gestion_utilisateurs'), 403);

        if ($userId) {
            $this->user = $this->scopedUser($userId);
            $this->first_name = $this->user->first_name ?? '';
            $this->last_name = $this->user->last_name ?? '';
            $this->login = $this->user->login ?? '';
            $this->email = $this->user->email;
            $this->status = $this->user->status ?? 'active';
            $this->permissions = $this->user->permissions ?? [];
        }
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

    public function toggleAllPermissions(): void
    {
        if (count($this->permissions) === count(Permissions::ALL)) {
            $this->permissions = [];
        } else {
            $this->permissions = Permissions::keys();
        }
    }

    public function save()
    {
        abort_if(auth()->user()?->is_demo, 403, 'Action desactivee dans la demo.');
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users,login,'.($this->user?->id),
            'email' => 'required|email|max:255|unique:users,email,'.($this->user?->id),
            'status' => 'required|in:active,inactive',
            'password' => $this->user ? 'nullable|min:8' : 'required|min:8',
        ]);

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => trim($this->first_name.' '.$this->last_name),
            'login' => $this->login,
            'email' => $this->email,
            'status' => $this->status,
            'permissions' => $this->permissions,
            // Always force the current admin's own project - never trust client input for this.
            'project_id' => auth()->user()->project_id,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->user) {
            $this->user->update($data);
        } else {
            $data['role'] = 'user';
            User::create($data);
        }

        session()->flash('success', 'Utilisateur enregistré.');

        return $this->redirect(route('utilisateurs.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.utilisateurs.form', [
            'permissionColumns' => Permissions::columns(),
        ])->layout('layouts.app');
    }
}
