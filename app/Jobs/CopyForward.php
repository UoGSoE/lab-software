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
        foreach (User::where('academic_session_id', $this->old->id)->get() as $user) {
            $newUser = $user->replicate();
            $newUser->academic_session_id = $this->new->id;
            $newUser->save();
        }
        foreach (Software::where('academic_session_id', $this->old->id)->get() as $software) {
            $newSoftware = $software->replicate();
            $newSoftware->academic_session_id = $this->new->id;
            $newSoftware->save();
        }
        foreach (Course::where('academic_session_id', $this->old->id)->get() as $course) {
            $newCourse = $course->replicate();
            $newCourse->academic_session_id = $this->new->id;
            $newCourse->save();
        }
    }
}
