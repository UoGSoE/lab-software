<?php

use App\Models\User;
use App\Models\Course;
use App\Jobs\ImportData;
use App\Models\Software;
use App\Livewire\ImportExport;
use App\Models\AcademicSession;
use App\Exporters\ExportAllData;
use OpenSpout\Common\Entity\Row;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use function Pest\Livewire\livewire;
use Illuminate\Support\Facades\Queue;
use Ohffs\SimpleSpout\ExcelSheet;

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
        $testData = [
            ['', 'Application', 'Version', 'Licene Type', 'Licene Details', 'COURSE', 'COURSE CONTACT', 'ROOM', 'CSCE', 'ENG BUILDS', 'Request Notes', 'Request Notes 2'],
            ['', '7-Zip', '24.06', 'NO LICENSE NEEDED', 'n/a', 'General Use', '', 'ALL', 'Y', 'ALL', ''],
            ['', 'Abaqus ', 'Latest Version', 'SCHOOL HELD', "See 'Licences' tab", 'ENG4094, ENG5053, ENG5096', 'person1@example.ac.uk, person2@example.ac.uk', 'ALL', 'Y', '', 'The version could be the current installed one or the latest version that is available and stable.'],
            ['', 'Acrobat Reader', 'Latest Version', 'NO LICENSE NEEDED', 'n/a', 'General Use', '', 'ALL', 'Y', 'ALL', ''],
            ['', 'Advanced Design Systems ', '2024', 'SCHOOL HELD', "See 'Licences' tab", 'ENG4110P, ENG5041P, ENG5059P', 'person3@example.ac.uk', 'ALL', 'Y', '', ''],
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
        $reader = new Reader();
        $reader->open($filename);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {
                $cells = $row->getCells();
                expect($cells)->toBeArray();
                if ($index === 1) {
                    // in Spout-land - the first row is index 1 and is the header row
                    expect($cells[0]->getValue())->toBe('Course Code');
                    expect($cells[1]->getValue())->toBe('Software');
                    expect($cells[2]->getValue())->toBe('Version');
                } else {
                    // we need to offset the index by 2 to account for the header row and spout's 1-based index
                    expect($cells[0]->getValue())->toBe($courseCodes[$index - 2]);
                    expect($cells[1]->getValue())->toBe($softwareNames[$index - 2]);
                    expect($cells[2]->getValue())->toBe($softwareVersions[$index - 2]);
                }
            }
        }
    });
});
