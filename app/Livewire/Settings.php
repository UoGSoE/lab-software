<?php

namespace App\Livewire;

use App\Models\School;
use Livewire\Component;
use App\Models\AcademicSession;

class Settings extends Component
{
    public function render()
    {
        return view('livewire.settings', [
            'schools' => School::orderBy('name')->get(),
            'academicSessions' => AcademicSession::orderBy('name')->get(),
        ]);
    }
}
