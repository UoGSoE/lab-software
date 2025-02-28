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
            $currentSession->is_default = false;
            $currentSession->save();
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
}
