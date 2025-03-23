<?php

namespace App\Jobs;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Software;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class ImportData implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data, public int $academicSessionId, public int $userId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $academicSession = AcademicSession::findOrFail($this->academicSessionId);
        $user = User::findOrFail($this->userId);

        $invalidRows = [];

        collect($this->data)->each(function ($row) use ($academicSession, $user, &$invalidRows) {
            $softwareName = trim($row[1]);
            $softwareVersion = trim($row[2]);
            $licenceType = trim($row[3]);
            $licenceDetails = trim($row[4]);
            $courses = trim($row[5]);
            $courseContacts = trim($row[6]);
            $rooms = trim($row[7] ?? '');
            $csce = trim($row[8] ?? '');
            $engBuilds = trim($row[9] ?? '');
            $requestNotes = trim($row[10] ?? '');
            $requestNotes2 = trim($row[11] ?? '');

            if (strtolower($softwareVersion) == 'version') {
                // Probably the header row
                return;
            }

            $courseCodes = explode(',', $courses);
            $courseContacts = explode(',', $courseContacts);

            foreach ($courseCodes as $courseCode) {
                $courseCode = trim(strtoupper($courseCode));
                $isTiedToCourse = true;
                // check the course code is 2 or more alpha characters followed by four numbers
                if (! preg_match('/^[A-Za-z]{2,}\d{4}/', $courseCode)) {
                    // this is not invalid, it just means the software isn't tied to a course
                    $isTiedToCourse = false;
                }

                if ($isTiedToCourse) {
                    $code = trim(strtoupper($courseCode));
                    $course = Course::firstOrCreate([
                        'code' => $code,
                        'title' => $code,
                        'academic_session_id' => $academicSession->id,
                    ]);
                }

                foreach ($courseContacts as $contact) {
                    $contact = trim(strtolower($contact));
                    if (! filter_var($contact, FILTER_VALIDATE_EMAIL)) {
                        // TODO: fix this
                        continue;
                    }
                    $user = User::where('email', '=', $contact)->first();
                    if (! $user) {
                        $user = $this->createNewUser($contact, $academicSession->id);
                    }

                    if ($isTiedToCourse) {
                        $course->users()->attach($user);
                    }
                }

                $software = Software::where('name', $softwareName)->where('academic_session_id', $academicSession->id)->first();
                if (! $software) {
                    $software = Software::create([
                        'name' => $softwareName,
                        'academic_session_id' => $academicSession->id,
                        'created_by' => $user->id,
                    ]);
                }
                $software->update([
                    'version' => $softwareVersion,
                    'licence_type' => $licenceType,
                    'licence_details' => $licenceDetails,
                ]);

                if ($isTiedToCourse) {
                    $software->courses()->attach($course);
                }
            }

        });
    }

    private function createNewUser(string $email, int $academicSessionId): User
    {
        $user = User::create([
            'email' => $email,
            'username' => $email,
            'surname' => 'SMITH',
            'forenames' => 'JOHN',
            'is_staff' => true,
            'is_admin' => false,
            'password' => bcrypt(Str::random(64)),
            'academic_session_id' => $academicSessionId,
        ]);

        return $user;
    }
}
