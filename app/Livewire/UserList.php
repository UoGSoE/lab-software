<?php

namespace App\Livewire;

use App\Models\Scopes\AcademicSessionScope;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    public $search = '';

    public $onlyAdmins = false;

    public $onlyMissing = false;

    public $userDetails = [];

    public $sortBy = 'surname';

    public $sortDirection = 'asc';

    public function render()
    {
        return view('livewire.user-list', [
            'users' => $this->getUsers(),
        ]);
    }

    public function getUsers()
    {
        $search = trim($this->search);

        return User::orderBy($this->sortBy, $this->sortDirection)->with('courses')
            ->when($search, function ($query) use ($search) {
                $query->where('surname', 'like', '%'.$search.'%')
                    ->orWhere('forenames', 'like', '%'.$search.'%');
            })
            ->when($this->onlyAdmins, function ($query) {
                $query->where('is_admin', true);
            })
            ->when($this->onlyMissing, function ($query) {
                $query->whereDoesntHave('courses');
            })
            ->paginate(50);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedOnlyAdmins()
    {
        $this->resetPage();
    }

    public function updatedOnlyMissing()
    {
        $this->resetPage();
    }

    public function toggleAdmin(User $user) {
        $new_is_admin_status = !$user->is_admin;
        $users = User::withoutGlobalScope(AcademicSessionScope::class)->where('username', $user->username)->get();
        foreach ($users as $user) {
            $user->is_admin = $new_is_admin_status;
            $user->save();
        }
    }

    public function showUserDetails(User $user) {
        $this->userDetails = $user->toArray();
        $this->modal('user-details')->show();
    }
    
    public function sort($field) {
        $this->sortBy = $field;
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }
}
