<?php

namespace App\Livewire;

use App\Models\Software;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeletedSoftware extends Component
{
    public function unmarkForRemoval(int $softwareId)
    {
        $software = Software::findOrFail($softwareId);
        $software->removed_at = null;
        $software->removed_by = null;
        $software->save();

        Flux::toast("{$software->name} unmarked for removal!", variant: 'success');
    }
    
    public function render()
    {
        return view('livewire.deleted-software', ['software' => Software::whereNotNull('removed_at')->orderBy('name')->get()]);
    }

    public function deleteSoftware(int $softwareId)
    {
        $software = Software::findOrFail($softwareId);
        $software->delete();

        Flux::toast("{$software->name} deleted!", variant: 'success');
    }
}
