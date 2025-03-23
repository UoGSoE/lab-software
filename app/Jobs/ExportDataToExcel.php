<?php

namespace App\Jobs;

use App\Exporters\ExportAllData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class ExportDataToExcel implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $exporter = new ExportAllData;
        $filePath = $exporter->export();

        // Send the file to the user
        Mail::send('emails.export_complete', ['filePath' => $filePath], function ($message) {
            $message->to('john@example.com')->subject('Software Data Export Complete');
        });
    }
}
