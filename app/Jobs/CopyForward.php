<?php

namespace App\Jobs;

use App\Models\AcademicSession;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CopyForward implements ShouldQueue
{
    use Queueable;

    public function __construct(public AcademicSession $old, public AcademicSession $new) {}

    public function handle(): void
    {
        $this->old->copyForwardTo($this->new);
    }
}
