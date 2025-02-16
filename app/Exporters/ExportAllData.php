<?php

namespace App\Exporters;

use App\Models\Course;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ExportAllData
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function export()
    {
        $tempDir = sys_get_temp_dir();
        $fileName = now()->format('Y-m-d') . '-software-data.xlsx';
        $filePath = $tempDir . '/' . $fileName;

        $writer = new Writer();
        $writer->openToFile($filePath);

        $courses = Course::orderBy('code')->with('software.createdBy')->get();

        $headers = [
            Cell::fromValue('Course Code'),
            Cell::fromValue('Software'),
            Cell::fromValue('Version'),
            Cell::fromValue('O/S'),
            Cell::fromValue('User'),
            Cell::fromValue('Building'),
            Cell::fromValue('Lab'),
            Cell::fromValue('New'),
            Cell::fromValue('Free'),
            Cell::fromValue('Config'),
            Cell::fromValue('Notes'),
        ];

        $row = new Row($headers);
        $writer->addRow($row);

        foreach ($courses as $course) {
            foreach ($course->software as $software) {
                $cells = [];
                $cells[] = Cell::fromValue($course->code);
                $cells[] = Cell::fromValue($software->name);
                $cells[] = Cell::fromValue($software->version);
                $cells[] = Cell::fromValue(implode(', ', $software->os ?? []));
                $cells[] = Cell::fromValue($software->createdBy?->email);
                $cells[] = Cell::fromValue(implode(', ', $software->building ?? []));
                $cells[] = Cell::fromValue($software->lab);
                $cells[] = Cell::fromValue($software->is_new ? 'Yes' : 'No');
                $cells[] = Cell::fromValue($software->is_free ? 'Yes' : 'No');
                $cells[] = Cell::fromValue($software->config);
                $cells[] = Cell::fromValue($software->notes);
            }
            $row = new Row($cells);
            $writer->addRow($row);
        }

        $writer->close();

        return $filePath;
    }
}
