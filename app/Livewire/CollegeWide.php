<?php

namespace App\Livewire;

use App\Models\Software;
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

}
