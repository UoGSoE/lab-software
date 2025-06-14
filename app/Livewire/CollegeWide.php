<?php

namespace App\Livewire;

use App\Models\Software;
use Flux\Flux;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Livewire\Component;

class CollegeWide extends Component
{
    public $sortBy = 'name';

    public $sortDirection = 'asc';

    public $search = '';

    public function sort($field)
    {
        $this->sortBy = $field;
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    public function render()
    {
        return view('livewire.college-wide', [
            'software' => $this->sortedSoftware(),
        ]);
    }

    public function sortedSoftware()
    {
        $sortColumn = $this->sortBy;
        if ($sortColumn === 'location') {
            $sortColumn = 'building';
        }

        return Software::global()->orderBy($sortColumn, $this->sortDirection)->when(trim($this->search), function ($query) {
            $query->where('name', 'like', '%'.trim($this->search).'%');
        })->get();
    }

    public function removeSoftware(int $softwareId)
    {
        $software = Software::findOrFail($softwareId);
        $software->removed_at = now();
        $software->removed_by = FacadesAuth::user()?->id;
        $software->save();

        Flux::toast("{$software->name} marked for removal!", variant: 'success');
    }

    public function unmarkForRemoval(int $softwareId)
    {
        $software = Software::findOrFail($softwareId);
        $software->removed_at = null;
        $software->removed_by = null;
        $software->save();

        Flux::toast("{$software->name} unmarked for removal!", variant: 'success');
    }

}
