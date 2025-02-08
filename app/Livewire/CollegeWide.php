<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Software;

class CollegeWide extends Component
{
    public $sortBy = 'name';
    public $sortDirection = 'asc';

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

        return Software::take(40)->orderBy($sortColumn, $this->sortDirection)->get();
    }
}
