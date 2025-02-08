<?php

namespace App\Livewire;

use App\Exporters\ExportAllData;
use Livewire\Component;

class Exporter extends Component
{
    public function render()
    {
        return view('livewire.exporter');
    }

    public function export()
    {
        $exportedFile = (new ExportAllData)->export();

        return response()->download($exportedFile);
    }
}
