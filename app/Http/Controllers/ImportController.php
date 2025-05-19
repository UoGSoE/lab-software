<?php

namespace App\Http\Controllers;

use App\Jobs\ImportData;
use Illuminate\Http\Request;
use App\Models\AcademicSession;
use Ohffs\SimpleSpout\ExcelSheet;

class ImportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx',
        ]);

        $rows = (new ExcelSheet())->trimmedImport($request->file('importFile')->getRealPath());

        ImportData::dispatch($rows, AcademicSession::getDefault()->id, $request->user()->id);

        return redirect()->route('importexport')->with('success', 'Import started');
    }
}
