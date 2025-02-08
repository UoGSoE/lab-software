<?php

namespace App\Jobs;

use App\Exporters\ExportAllData;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        $exporter = new ExportAllData();
        $filePath = $exporter->export();

        // Send the file to the user
        Mail::send('emails.export_complete', ['filePath' => $filePath], function ($message) use ($filePath) {
            $message->to('john@example.com')->subject('Software Data Export Complete');
        });
    }
}
