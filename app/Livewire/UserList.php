<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    public $search = '';

    public $onlyAdmins = false;

    public $onlyMissing = false;

    public function render()
    {
        return view('livewire.user-list', [
            'users' => $this->getUsers(),
        ]);
    }

    public function getUsers()
    {
        $search = trim($this->search);

        return User::orderBy('surname')->with('courses')
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
}
