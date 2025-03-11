<?php

namespace App\Livewire;

use Flux\Flux;
use App\Models\School;
use Livewire\Component;
use App\Jobs\CopyForward;
use App\Models\AcademicSession;

class Settings extends Component
{
    public $newSessionNameStart = '';
    public $newSessionNameEnd = '';
    public $newSessionIsDefault = true;

    public $newSchoolName = '';
    public $newSchoolCoursePrefix = '';

    public $defaultSessionId = null;

    public $editSchoolId = null;
    public $editSchoolName = '';
    public $editSchoolCoursePrefix = '';

    public $deleteSchoolId = null;

    public function mount()
    {
        $defaultSession = AcademicSession::getDefault();
        if (! $defaultSession) {
            throw new \Exception('No default academic session found');
        }

        [$start, $end] = explode('-', $defaultSession->name);
        $this->newSessionNameStart = $start + 1;
        $this->newSessionNameEnd = $end + 1;
        $this->defaultSessionId = $defaultSession->id;
    }

    public function render()
    {
        return view('livewire.settings', [
            'schools' => School::orderBy('name')->get(),
            'academicSessions' => AcademicSession::orderBy('name')->get(),
        ]);
    }

    function createNewSession()
    {
        $earliestYear = date('Y');
        $latestYear = $earliestYear + 3;

        $this->validate([
            'newSessionNameStart' => "required|integer|min:{$earliestYear}|max:{$latestYear}",
            'newSessionNameEnd' => "required|integer|min:{$earliestYear}|max:{$latestYear}",
            'newSessionIsDefault' => 'required|boolean',
        ]);

        $newSessionName = "{$this->newSessionNameStart}-{$this->newSessionNameEnd}";

        $existingSession = AcademicSession::where('name', '=', $newSessionName)->first();
        if ($existingSession) {
            Flux::toast("Session {$newSessionName} already exists!", variant: 'danger');
            return;
        }

        $currentSession = AcademicSession::getDefault();
        if (! $currentSession) {
            throw new \Exception('No default academic session found');
        }

        $newSession = new AcademicSession();
        $newSession->name = $newSessionName;
        $newSession->is_default = $this->newSessionIsDefault;
        $newSession->save();

        if ($this->newSessionIsDefault) {
            $newSession->setAsDefault();
        }

        CopyForward::dispatch($currentSession, $newSession);

        $this->modal('create-new-session')->close();

        Flux::toast("New session {$newSessionName} created!", variant: 'success');

        $this->reset('newSessionNameStart', 'newSessionNameEnd', 'newSessionIsDefault');
    }

    public function createNewSchool()
    {
        $this->validate([
            'newSchoolName' => 'required|string|max:255|unique:schools,name',
            'newSchoolCoursePrefix' => 'required|string|max:255',
        ]);

        $school = School::create([
            'name' => $this->newSchoolName,
            'course_prefix' => $this->newSchoolCoursePrefix,
        ]);

        Flux::toast("New school {$this->newSchoolName} created!", variant: 'success');

        $this->reset('newSchoolName', 'newSchoolCoursePrefix');

        $this->modal('create-new-school')->close();
    }

    public function updateDefaultSession()
    {
        $this->validate([
            'defaultSessionId' => 'required|exists:academic_sessions,id',
        ]);

        $academicSession = AcademicSession::find($this->defaultSessionId);
        if (! $academicSession) {
            Flux::toast('Academic session not found', variant: 'danger');
            return;
        }

        $academicSession->setAsDefault();

        Flux::toast("Default academic session updated to {$academicSession->name}", variant: 'success');
    }

    public function editSchool($id)
    {
        $school = School::find($id);
        if (! $school) {
            Flux::toast('School not found', variant: 'danger');
            return;
        }

        $this->editSchoolId = $school->id;
        $this->editSchoolName = $school->name;
        $this->editSchoolCoursePrefix = $school->course_prefix;

        $this->modal('edit-school')->show();
    }

    public function updateSchool()
    {
        $this->validate([
            'editSchoolId' => 'required|exists:schools,id',
            'editSchoolName' => 'required|string|max:255',
            'editSchoolCoursePrefix' => 'required|string|max:255',
        ]);

        $school = School::find($this->editSchoolId);
        if (! $school) {
            Flux::toast('School not found', variant: 'danger');
            return;
        }

        $school->name = $this->editSchoolName;
        $school->course_prefix = $this->editSchoolCoursePrefix;
        $school->save();

        $this->reset('editSchoolId', 'editSchoolName', 'editSchoolCoursePrefix');

        $this->modal('edit-school')->close();

        Flux::toast("School {$this->editSchoolName} updated!", variant: 'success');
    }

    public function deleteSchool($schoolId)
    {
        $school = School::find($schoolId);
        if (! $school) {
            Flux::toast('School not found', variant: 'danger');
            return;
        }

        $school->delete();

        Flux::toast("School {$school->name} deleted!", variant: 'success');
    }


}
