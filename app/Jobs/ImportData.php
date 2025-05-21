<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Course;
use App\Models\Software;
use App\Rules\CourseCode;
use Illuminate\Support\Str;
use App\Models\AcademicSession;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

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

        $errors = collect($this->data)->map(function ($row) use ($academicSession) {
            if (strtolower($row[1]) == 'application') {
                // skip likely header row
                return '';
            }

            $validator = $this->validateRow($row);

            if ($validator->fails()) {
                $invalidRows[] = $row;

                $combinedErrors = implode(', ', $validator->errors()->all());

                return $combinedErrors;
            }

            $validated = $validator->validated();

            // ------ Use 'CSCE' column for figuring out global or not - should be 'Y' or 'N'  --------
            if (empty($validated['courses'])) {
                $this->handleGlobalSoftware($validated, $academicSession->id);
            } else {
                $this->handleCourseSpecificSoftware($validated, $academicSession->id);
            }

            return '';
        })->filter();

        dd("TODO: send email to user with errors");
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

    public function handleCourseSpecificSoftware(array $validatedRow, int $academicSessionId)
    {
        $software = Software::firstOrCreate([
            'name' => $validatedRow['software_name'],
            'version' => $validatedRow['software_version'],
            'config' => $validatedRow['request_notes'],
            'notes' => $validatedRow['request_notes_2'],
            'os' => $validatedRow['os'],
            'building' => $validatedRow['building'],
            'lab' => $validatedRow['lab'],
            'academic_session_id' => $academicSessionId,
        ]);

        if ($software->wasRecentlyCreated) {
            $software->is_new = true;
            $software->created_by = $this->userId;
        }

        // update the existing licence details if they are empty or the incoming ones are not empty
        if ( (! $software->licence_type) || $validatedRow['licence_type'] !== '') {
            $software->licence_type = $validatedRow['licence_type'];
            $software->licence_details = $validatedRow['licence_details'];
        }

        $software->save();

        foreach ($validatedRow['courses'] as $courseCode) {
            $course = Course::firstOrCreate([
                'code' => $courseCode,
                'academic_session_id' => $academicSessionId,
            ], [
                'title' => $courseCode,
            ]);

            $course->software()->attach($software);
        }

        foreach ($validatedRow['course_contacts'] as $contact) {
            $contact = trim(strtolower($contact));
            $user = User::where('email', '=', $contact)->first();
            if (! $user) {
                $user = $this->createNewUser($contact, $academicSessionId);
            }

            $course->users()->attach($user);
        }
    }

    public function handleGlobalSoftware(array $validatedRow, int $academicSessionId)
    {

    }

    private function expandRow(array $row): array
    {
        // ['', 'Abaqus ', 'Latest Version', 'SCHOOL HELD', "See 'Licences' tab", 'ENG4094, ENG5053, ENG5096', 'person1@example.ac.uk, person2@example.ac.uk', 'ALL', 'Y', '', 'The version could be the current installed one or the latest version that is available and stable.'],
        $expandedRow = [];
        $expandedRow['software_name'] = trim($row[1]);
        $expandedRow['software_version'] = trim($row[2]);
        $expandedRow['licence_type'] = trim($row[3]);
        $expandedRow['licence_details'] = trim($row[4]);
        $expandedRow['courses'] = explode(',', strtoupper(Str::replace(' ', '', $row[5])));
        $expandedRow['course_contacts'] = explode(',', strtolower(Str::replace(' ', '', $row[6])));
        $expandedRow['request_notes'] = trim($row[10] ?? '');
        $expandedRow['request_notes_2'] = trim($row[11] ?? '');
        return $expandedRow;
    }

    private function validateRow(array $row)
    {
        $expandedRow = $this->expandRow($row);
        $validator = Validator::make($expandedRow, [
            'software_name' => 'required|string',
            'software_version' => 'required|string',
            'licence_type' => 'required|string',
            'licence_details' => 'required|string',
            'courses' => 'required|array',
            'courses.*' => ['required', 'string', new CourseCode],
            'course_contacts' => 'required|array',
            'course_contacts.*' => 'nullable|email:strict',
            'request_notes' => 'nullable|string',
            'request_notes_2' => 'nullable|string',
            'rooms' => 'nullable|string',
            'csce' => 'nullable|string',
            'eng_builds' => 'nullable|string',
        ]);

        return $validator;
    }
}
