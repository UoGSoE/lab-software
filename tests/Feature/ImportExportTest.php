<?php

use App\Models\User;
use App\Models\Course;
use App\Jobs\ImportData;
use App\Models\Software;
use App\Livewire\ImportExport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use App\Models\AcademicSession;

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

        $this->actingAs($this->admin)->post('/importexport/import', [
            'importFile' => UploadedFile::fake()->createWithContent('test.csv', ''),
        ]);

        Queue::assertPushed(ImportData::class);
    });

    it('The import data job will import the data', function () {
        $testData = [
            ["","Application","Version","Licene Type","Licene Details","COURSE","COURSE CONTACT","ROOM","CSCE","ENG BUILDS","Request Notes","Request Notes 2"],
            ["","7-Zip","24.06","NO LICENSE NEEDED","n/a","General Use","","ALL","Y","ALL","",],
            ["","Abaqus ","Latest Version","SCHOOL HELD","See 'Licences' tab","ENG4094, ENG5053, ENG5096","person1@example.ac.uk, person2@example.ac.uk","ALL","Y","","The version could be the current installed one or the latest version that is available and stable.",],
            ["","Acrobat Reader","Latest Version","NO LICENSE NEEDED","n/a","General Use","","ALL","Y","ALL","",],
            ["","Advanced Design Systems ","2024","SCHOOL HELD","See 'Licences' tab","ENG4110P, ENG5041P, ENG5059P","person3@example.ac.uk","ALL","Y","","",],
        ];

        ImportData::dispatchSync($testData);

        expect(Software::count())->toBe(4);
        expect(Course::count())->toBe(6);
        expect(User::count())->toBe(4);  // 3 from the import + 1 admin user

    });
});

describe('Exporting data', function () {
    it('can export existing data', function () {
        $this->markTestSkipped('TODO: Implement this');
    });
});
