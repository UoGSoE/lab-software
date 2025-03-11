<?php

namespace App\Livewire;

use App\Exporters\ExportAllData;
use Livewire\Component;

class ImportExport extends Component
{
    public function render()
    {
        return view('livewire.importexport');
    }

    public function export()
    {
        $exportedFile = (new ExportAllData)->export();

        return response()->download($exportedFile);
    }
}
