<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Course;
use App\Models\Software;
use App\Rules\CourseCode;
use Illuminate\Support\Str;
use App\Models\AcademicSession;
use App\Mail\ImportCompleteEmail;
use Illuminate\Support\Facades\Mail;
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
        $currentRow = 0;
        $errors = collect($this->data)->map(function ($row) use ($academicSession, &$currentRow) {
            $currentRow++;
            if (strtolower($row[1]) == 'application') {
                // skip likely header row
                return '';
            }

            $validator = $this->validateRow($row);

            if ($validator->fails()) {
                $combinedErrors = "Error on row {$currentRow}: " . implode(', ', $validator->errors()->all());
                // No software is created for invalid rows
                return $combinedErrors;
            }

            $validated = $validator->validated();

            // Only create software for valid rows
            if ($this->isSoftwareGlobal($validated)) {
                $this->handleGlobalSoftware($validated, $academicSession->id);
            } else {
                $this->handleCourseSpecificSoftware($validated, $academicSession->id);
            }

            return '';
        })->filter();

        Mail::to(User::find($this->userId))->queue(new ImportCompleteEmail($errors));
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

    private function createNewSoftware(array $validatedRow, int $academicSessionId, int $courseId = null): Software
    {
        $attributes = [
            'name' => $validatedRow['software_name'],
            'version' => $validatedRow['software_version'],
            'config' => $validatedRow['request_notes'],
            'notes' => $validatedRow['request_notes_2'],
            'lab' => $validatedRow['room'],
            'academic_session_id' => $academicSessionId,
        ];
        if ($courseId !== null) {
            $attributes['course_id'] = $courseId;
        }

        $software = Software::firstOrNew($attributes);

        if ($software->wasRecentlyCreated) {
            $software->is_new = true;
        }

        if (! $software->created_by) {
            $software->created_by = $this->userId;
        }

        // update the existing licence details if they are empty or the incoming ones are not empty
        if ((! $software->licence_type) || $validatedRow['licence_type'] !== '') {
            $software->licence_type = $validatedRow['licence_type'];
            $software->licence_details = $validatedRow['licence_details'];
        }

        $software->save();

        return $software;
    }

    public function handleCourseSpecificSoftware(array $validatedRow, int $academicSessionId)
    {
        foreach ($validatedRow['courses'] as $courseCode) {
            $course = Course::firstOrCreate([
                'code' => $courseCode,
                'academic_session_id' => $academicSessionId,
            ], [
                'title' => $courseCode,
            ]);

            // Create software with course_id set
            $software = $this->createNewSoftware($validatedRow, $academicSessionId, $course->id);

            foreach ($validatedRow['course_contacts'] as $contact) {
                $contact = trim(strtolower($contact));
                $user = User::where('email', '=', $contact)->first();
                if (! $user) {
                    $user = $this->createNewUser($contact, $academicSessionId);
                }

                $course->users()->syncWithoutDetaching([$user->id]);
            }
        }
    }

    public function handleGlobalSoftware(array $validatedRow, int $academicSessionId)
    {
        $software = $this->createNewSoftware($validatedRow, $academicSessionId);
    }

    private function expandRow(array $row): array
    {
        $expandedRow = [];
        $expandedRow['software_name'] = trim($row[1]);
        $expandedRow['software_version'] = trim($row[2]);
        $expandedRow['licence_type'] = trim($row[3]);
        $expandedRow['licence_details'] = trim($row[4]);
        $expandedRow['courses'] = explode(',', strtoupper(Str::replace(' ', '', $row[5])));
        $expandedRow['course_contacts'] = explode(',', strtolower(Str::replace(' ', '', $row[6])));
        $expandedRow['room'] = trim($row[7] ?? '');
        $expandedRow['csce'] = trim($row[8] ?? '');
        $expandedRow['eng_builds'] = trim($row[9] ?? '');
        $expandedRow['request_notes'] = trim($row[10] ?? '');
        $expandedRow['request_notes_2'] = trim($row[11] ?? '');
        return $expandedRow;
    }

    private function isSoftwareGlobal(array $validatedRow): bool
    {
        return str_starts_with(strtolower($validatedRow['csce']), 'y');
    }

    private function validateRow(array $row)
    {
        $expandedRow = $this->expandRow($row);
        $isGlobal = $this->isSoftwareGlobal($expandedRow);
        $rules = [
            'software_name' => 'required|string',
            'software_version' => 'required|string',
            'licence_type' => 'required|string',
            'licence_details' => 'required|string',
            'course_contacts' => 'required|array',
            'course_contacts.*' => 'nullable|email:strict',
            'request_notes' => 'nullable|string',
            'request_notes_2' => 'nullable|string',
            'room' => 'nullable|string',
            'csce' => 'nullable|string',
            'eng_builds' => 'nullable|string',
        ];
        if (! $isGlobal) {
            $rules['courses'] = 'required|array';
            $rules['courses.*'] = ['nullable', new CourseCode];
        }
        $validator = Validator::make($expandedRow, $rules);

        return $validator;
    }
}
