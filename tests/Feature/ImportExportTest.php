<?php

use App\Models\User;
use App\Models\Course;
use App\Jobs\ImportData;
use App\Models\Software;
use App\Livewire\ImportExport;
use App\Models\AcademicSession;
use App\Exporters\ExportAllData;
use OpenSpout\Common\Entity\Row;
use App\Mail\ImportCompleteEmail;
use Illuminate\Http\UploadedFile;
use Ohffs\SimpleSpout\ExcelSheet;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use function Pest\Livewire\livewire;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->academicSession = AcademicSession::factory()->create();
    $this->admin = User::factory()->create([
        'is_admin' => true,
        'academic_session_id' => $this->academicSession->id,
    ]);
});

it('has importexport page', function () {
    $response = $this->actingAs($this->admin)->get('/importexport');

    $response->assertStatus(200);
    $response->assertSeeLivewire(ImportExport::class);
});

describe('Importing data', function () {
    it('Fires a queued job when the import is started', function () {
        Queue::fake();

        $headingRow = ['', 'Application', 'Version', 'Licene Type', 'Licene Details', 'COURSE', 'COURSE CONTACT', 'ROOM', 'CSCE', 'ENG BUILDS', 'Request Notes', 'Request Notes 2'];
        $sheetName = (new ExcelSheet())->generate([$headingRow]);

        $this->actingAs($this->admin)->post(route('import-software'), [
            'importFile' => UploadedFile::fake()->createWithContent('test.xlsx', file_get_contents($sheetName)),
        ])->assertRedirect(route('importexport'));

        unlink($sheetName);

        Queue::assertPushed(ImportData::class);
    });

    it('The import data job will import the data', function () {
        Mail::fake();
        $testData = [
            ['', 'Application', 'Version', 'Licene Type', 'Licene Details', 'COURSE', 'COURSE CONTACT', 'ROOM', 'CSCE', 'ENG BUILDS', 'Request Notes', 'Request Notes 2'],
            ['', '7-Zip', '24.06', 'NO LICENSE NEEDED', 'n/a', 'General Use', '', 'ALL', 'Y', 'ALL', ''],
            ['', 'Abaqus ', 'Latest Version', 'SCHOOL HELD', "See 'Licences' tab", 'ENG4094, ENG5053, ENG5096', 'person1@example.ac.uk, person2@example.ac.uk', 'ALL', 'N', '', 'The version could be the current installed one or the latest version that is available and stable.'],
            ['', 'Acrobat Reader', 'Latest Version', 'NO LICENSE NEEDED', 'n/a', 'General Use', '', 'ALL', 'Y', 'ALL', ''],
            ['', 'Advanced Design Systems ', '2024', 'SCHOOL HELD', "See 'Licences' tab", 'ENG4110P, ENG5041P, ENG5059P', 'person3@example.ac.uk', 'ALL', 'N', '', ''],
        ];

        expect(Software::count())->toBe(0);
        expect(Course::count())->toBe(0);
        expect(User::count())->toBe(1);  // 1 admin user

        ImportData::dispatchSync($testData, $this->academicSession->id, $this->admin->id);

        expect(Software::count())->toBe(4);
        expect(Course::count())->toBe(6);
        expect(User::count())->toBe(4);  // 3 from the import + 1 admin user

        foreach (['ENG4094', 'ENG5053', 'ENG5096'] as $courseCode) {
            $course = Course::where('code', '=', $courseCode)->first();
            expect($course)->not->toBeNull();
            expect($course->academic_session_id)->toBe($this->academicSession->id);
            expect($course->users->count())->toBe(2);
            expect($course->users->pluck('email'))->toContain('person1@example.ac.uk', 'person2@example.ac.uk');

            expect($course->software->count())->toBe(1);
            expect($course->software->pluck('name'))->toContain('Abaqus');
        }

        foreach (['ENG4110P', 'ENG5041P', 'ENG5059P'] as $courseCode) {
            $course = Course::where('code', '=', $courseCode)->first();
            expect($course)->not->toBeNull();
            expect($course->academic_session_id)->toBe($this->academicSession->id);
            expect($course->users->count())->toBe(1);
            expect($course->users->pluck('email'))->toContain('person3@example.ac.uk');

            expect($course->software->count())->toBe(1);
            expect($course->software->pluck('name'))->toContain('Advanced Design Systems');
        }

        Mail::assertQueued(ImportCompleteEmail::class, 1);
        Mail::assertQueued(ImportCompleteEmail::class, function ($mail) {
            expect($mail->errors)->toBeCollection();
            expect($mail->errors)->toHaveCount(0);
            expect($mail->hasTo($this->admin->email))->toBeTrue();
            return true;
        });

    });

    it('Importing data will not duplicate existing records', function () {
        $testData = [
            ['', 'Application', 'Version', 'Licene Type', 'Licene Details', 'COURSE', 'COURSE CONTACT', 'ROOM', 'CSCE', 'ENG BUILDS', 'Request Notes', 'Request Notes 2'],
            ['', '7-Zip', '24.06', 'NO LICENSE NEEDED', 'n/a', 'General Use', '', 'ALL', 'Y', 'ALL', ''],
            ['', 'Abaqus ', 'Latest Version', 'SCHOOL HELD', "See 'Licences' tab", 'ENG4094, ENG5053, ENG5096', 'person1@example.ac.uk, person2@example.ac.uk', 'ALL', 'N', '', 'The version could be the current installed one or the latest version that is available and stable.'],
            ['', 'Acrobat Reader', 'Latest Version', 'NO LICENSE NEEDED', 'n/a', 'General Use', '', 'ALL', 'Y', 'ALL', ''],
            ['', 'Advanced Design Systems ', '2024', 'SCHOOL HELD', "See 'Licences' tab", 'ENG4110P, ENG5041P, ENG5059P', 'person3@example.ac.uk', 'ALL', 'N', '', ''],
        ];

        $existingCourse = Course::factory()->create([
            'code' => 'ENG4094',
            'academic_session_id' => $this->academicSession->id,
        ]);
        $existingSoftware = Software::factory()->create([
            'name' => 'Abaqus',
            'academic_session_id' => $this->academicSession->id,
            'version' => 'Latest Version',
            'licence_type' => 'SCHOOL HELD',
            'licence_details' => "See 'Licences' tab",
            'config' => 'The version could be the current installed one or the latest version that is available and stable.',
            'notes' => '',
            'lab' => 'ALL',
        ]);
        $existingCourse->software()->attach($existingSoftware);
        $existingUser = User::factory()->create([
            'email' => 'person1@example.ac.uk',
            'academic_session_id' => $this->academicSession->id,
        ]);
        $existingCourse->users()->attach($existingUser);

        expect(Software::count())->toBe(1);
        expect(Course::count())->toBe(1);
        expect(User::count())->toBe(2);  // 1 admin user + 1 existing user
        expect($existingCourse->software->count())->toBe(1);
        expect($existingCourse->users->count())->toBe(1);

        ImportData::dispatchSync($testData, $this->academicSession->id, $this->admin->id);

        expect(Software::count())->toBe(4);
        expect(Course::count())->toBe(6);
        expect(User::count())->toBe(4);  // 2 from the import + 1 admin user + 1 existing user

        foreach (['ENG4094', 'ENG5053', 'ENG5096'] as $courseCode) {
            $course = Course::where('code', '=', $courseCode)->first();
            expect($course)->not->toBeNull();
            expect($course->academic_session_id)->toBe($this->academicSession->id);
            expect($course->users->count())->toBe(2);
            expect($course->users->pluck('email'))->toContain('person1@example.ac.uk', 'person2@example.ac.uk');

            expect($course->software->count())->toBe(1);
            expect($course->software->pluck('name'))->toContain('Abaqus');
        }

        foreach (['ENG4110P', 'ENG5041P', 'ENG5059P'] as $courseCode) {
            $course = Course::where('code', '=', $courseCode)->first();
            expect($course)->not->toBeNull();
            expect($course->academic_session_id)->toBe($this->academicSession->id);
            expect($course->users->count())->toBe(1);
            expect($course->users->pluck('email'))->toContain('person3@example.ac.uk');

            expect($course->software->count())->toBe(1);
            expect($course->software->pluck('name'))->toContain('Advanced Design Systems');
        }
    });

    it('Any rows with errors are included in the email to the admin', function () {
        Mail::fake();
        $testData = [
            ['', 'Application', 'Version', 'Licene Type', 'Licene Details', 'COURSE', 'COURSE CONTACT', 'ROOM', 'CSCE', 'ENG BUILDS', 'Request Notes', 'Request Notes 2'],
            ['', '', '24.06', 'NO LICENSE NEEDED', 'n/a', 'General Use', '', 'ALL', 'Y', 'ALL', ''],
            ['', 'Abaqus ', 'Latest Version', 'SCHOOL HELD', "See 'Licences' tab", 'ENG4094, ENG5053, ENG5096', 'person1............@example.ac.uk, person2@example.ac.uk', 'ALL', 'N', '', 'The version could be the current installed one or the latest version that is available and stable.'],
            ['', 'Acrobat Reader', 'Latest Version', 'NO LICENSE NEEDED', 'n/a', 'General Use', '', 'ALL', 'Y', 'ALL', ''],
            ['', 'Advanced Design Systems ', '2024', 'SCHOOL HELD', "See 'Licences' tab", 'ENG4110P, ENG5041P, ENG5059P', 'person3@example.ac.uk', 'ALL', 'N', '', ''],
        ];

        expect(Software::count())->toBe(0);
        expect(Course::count())->toBe(0);
        expect(User::count())->toBe(1);  // 1 admin user

        ImportData::dispatchSync($testData, $this->academicSession->id, $this->admin->id);

        expect(Software::count())->toBe(2); // We do not create a record for the row with no application name or the one with an invalid email address
        expect(Course::count())->toBe(3);
        expect(User::count())->toBe(2);  // 1 from the import + 1 admin user and we skip the row with the person with an invalid email address

        foreach (['ENG4094', 'ENG5053', 'ENG5096'] as $courseCode) {
            $course = Course::where('code', '=', $courseCode)->first();
            expect($course)->toBeNull();
        }

        foreach (['ENG4110P', 'ENG5041P', 'ENG5059P'] as $courseCode) {
            $course = Course::where('code', '=', $courseCode)->first();
            expect($course)->not->toBeNull();
            expect($course->academic_session_id)->toBe($this->academicSession->id);
            expect($course->users->count())->toBe(1);
            expect($course->users->pluck('email'))->toContain('person3@example.ac.uk');

            expect($course->software->count())->toBe(1);
            expect($course->software->pluck('name'))->toContain('Advanced Design Systems');
        }

        Mail::assertQueued(ImportCompleteEmail::class, 1);
        Mail::assertQueued(ImportCompleteEmail::class, function ($mail) {
            expect($mail->errors)->toBeCollection();
            expect($mail->errors)->toHaveCount(2);
            expect($mail->errors)
                ->toContain('Error on row 2: The software name field is required.');
            expect($mail->errors)
                ->toContain('Error on row 3: The course_contacts.0 field must be a valid email address.');
            expect($mail->hasTo($this->admin->email))->toBeTrue();
            return true;
        });

    });

});

describe('Exporting data', function () {
    it('can export existing data', function () {
        $expectedFilename = now()->format('Y-m-d').'-software-data.xlsx';
        livewire(ImportExport::class)->call('export')->assertFileDownloaded($expectedFilename);
    });

    it('exports the correct data', function () {

        $courseCodes = ['ENG4094', 'ENG5053', 'ENG5096'];
        $softwareNames = ['7-Zip', 'Abaqus', 'Acrobat Reader'];
        $softwareVersions = ['24.06', 'Latest Version', 'Latest Version'];
        foreach ($courseCodes as $index => $courseCode) {
            $course =Course::factory()->create([
                'code' => $courseCode,
                'academic_session_id' => $this->academicSession->id,
            ]);

            $software = Software::factory()->create([
                'academic_session_id' => $this->academicSession->id,
                'name' => $softwareNames[$index],
                'version' => $softwareVersions[$index],
            ]);

            $course->software()->attach($software);
        }

        $filename = (new ExportAllData)->export();

        expect($filename)->toBeString();
        expect(file_exists($filename))->toBeTrue();
        $rows = (new ExcelSheet())->trimmedImport($filename);
        expect($rows)->toBeArray();
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                expect($row[0])->toBe('Course Code');
                expect($row[1])->toBe('Software');
                expect($row[2])->toBe('Version');
            } else {
                expect($row[0])->toBe($courseCodes[$index - 1]);
                expect($row[1])->toBe($softwareNames[$index - 1]);
                expect($row[2])->toBe($softwareVersions[$index - 1]);
            }
        }
    });
});
