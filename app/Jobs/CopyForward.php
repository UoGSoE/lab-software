<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Course;
use App\Models\Software;
use App\Models\AcademicSession;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CopyForward implements ShouldQueue
{
    use Queueable;

    public function __construct(public AcademicSession $old, public AcademicSession $new)
    {
    }

    public function handle(): void
    {
        $this->old->copyForwardTo($this->new);
    }
}
