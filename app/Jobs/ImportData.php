<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Course;
use App\Models\Software;
use Illuminate\Support\Str;
use App\Models\AcademicSession;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportData implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data, public int $academicSessionId, public int $userId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $academicSession = AcademicSession::findOrFail($this->academicSessionId);
        $user = User::findOrFail($this->userId);

        collect($this->data)->each(function ($row) use ($academicSession, $user) {
            $softwareName = trim($row[1]);
            $softwareVersion = trim($row[2]);
            $licenceType = trim($row[3]);
            $licenceDetails = trim($row[4]);
            $courses = trim($row[5]);
            $courseContacts = trim($row[6]);
            $rooms = trim($row[7]);
            $csce = trim($row[8]);
            $engBuilds = trim($row[9]);
            $requestNotes = trim($row[10]);
            $requestNotes2 = trim($row[11]);

            if (strtolower($softwareVersion) == 'version') {
                // Probably the header row
                return;
            }

            $courseCodes = explode(',', $courses);
            $courseContacts = explode(',', $courseContacts);

            foreach ($courseCodes as $courseCode) {
                $code = trim(strtoupper($courseCode));
                $course = Course::firstOrCreate([
                    'code' => $code,
                ]);

                foreach ($courseContacts as $contact) {
                    $contact = trim(strtolower($contact));
                    $user = User::where('email', '=', $contact)->first();
                    if (!$user) {
                        $user = $this->createNewUser($contact, $academicSession->id);
                    }

                    $course->users()->attach($user);
                }

                $software = Software::firstOrCreate([
                    'name' => $softwareName,
                ]);
                $software->update([
                    'version' => $softwareVersion,
                    'licence_type' => $licenceType,
                    'licence_details' => $licenceDetails,
                    'academic_session_id' => $academicSession->id,
                    'created_by' => $user->id,
                ]);

                $software->courses()->attach($course);
            }


        });
    }

    private function createNewUser(string $email, int $academicSessionId): User
    {
        $user = User::create([
            'email' => $email,
            'password' => bcrypt(Str::random(64)),
            'academic_session_id' => $academicSessionId,
        ]);
        return $user;
    }
}
