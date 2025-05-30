<?php

namespace App\Livewire;

use Flux\Flux;
use App\Models\Course;
use App\Models\School;
use Livewire\Component;
use App\Models\Software;
use Illuminate\Support\Arr;
use Livewire\WithPagination;
use App\Models\AcademicSession;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class HomePage extends Component
{
    use WithPagination;

    public string $currentSessionName = '';

    public ?Software $softwareDetails = null;

    public Collection $availableSchools;

    public array $newSoftware = [
        'id' => null,
        'os' => ['Windows'],
        'name' => '',
        'version' => '',
        'building' => [],
        'lab' => '',
        'config' => '',
        'notes' => '',
        'is_new' => false,
        'is_free' => false,
        'course_code' => '',
    ];

    public array $filters = [
        'school' => '',
        'course' => '',
        'software' => '',
    ];

    public function mount()
    {
        $this->currentSessionName = AcademicSession::getUsersSession()?->name;
        $this->softwareDetails = new Software;
        $this->availableSchools = School::orderBy('name')->get();
    }

    public function render()
    {
        $courses = $this->getFilteredCourses();
        $courseCodes = $courses->pluck('code')->unique()->sort();

        // $softwareTitles = $courses->map(fn ($course) => $course->software->map(fn ($software) => $software->name))->unique();
        return view('livewire.home-page', [
            'courses' => $courses,
            'courseCodes' => $courseCodes,
            'academicSession' => $this->currentSessionName,
        ]);
    }

    public function getFilteredCourses(): LengthAwarePaginator
    {
        $courses = Course::whereHas('software', function ($query) {
            $query->where('name', 'like', '%'.$this->filters['software'].'%');
        })->with(['software' => function ($query) {
            $query->where('name', 'like', '%'.$this->filters['software'].'%');
        }])->with('users')->orderBy('code')
            ->when(
                trim($this->filters['school']), fn ($query) => $query->where('code', 'like', $this->filters['school'].'%')
            )
            ->when(
                trim($this->filters['course']), fn ($query) => $query->where('code', 'like', '%'.$this->filters['course'].'%')
            );
        $userId = Auth::user()?->id ?? null;
        $courses = $courses->paginate(10);
        $courses->each(function ($course) use ($userId) {
            $course->signed_off = $course->users->contains($userId);
        });

        return $courses;
    }

    public function updatedFilters()
    {
        $this->resetPage();
        // request()->user()->update(['school' => $this->filters['school']]);
    }

    public function requestNewSoftware(?int $courseId = null)
    {
        $this->reset('newSoftware');
        if ($courseId) {
            $course = Course::with('software')->findOrFail($courseId);
            $this->newSoftware['course_code'] = $course->code;
            $this->newSoftware['building'] = $course->software?->first()?->building;
            $this->newSoftware['lab'] = $course->software?->first()?->lab;
        }

        $this->modal('add-software')->show();
    }

    public function addSoftware()
    {
        $validated = $this->validate([
            'newSoftware.name' => 'required|max:255',
            'newSoftware.os' => ['nullable', 'array'],
            'newSoftware.os.*' => ['nullable', 'string', Rule::in(['Windows', 'Mac', 'Linux'])],
            'newSoftware.version' => 'nullable|max:255',
            'newSoftware.building' => 'nullable|max:255',
            'newSoftware.lab' => 'nullable|max:255',
            'newSoftware.config' => 'nullable|max:1024',
            'newSoftware.notes' => 'nullable|max:1024',
            'newSoftware.course_code' => 'required|max:255|regex:/^[a-zA-Z]+[0-9]+$/',
        ]);

        $userId = Auth::user()?->id ?? null;
        $newSoftware = $this->newSoftware;
        $newSoftware['created_by'] = $userId;
        $academicSession = AcademicSession::where('is_default', true)->first();
        $newSoftware['academic_session_id'] = $academicSession->id;

        $courseCode = strtoupper(trim($this->newSoftware['course_code']));
        $course = Course::where('code', '=', $courseCode)->first();
        if (! $course) {
            $course = Course::create([
                'code' => $courseCode,
                'title' => $courseCode,
                'academic_session_id' => $academicSession->id,
            ]);
        }

        $softwareData = Arr::except($newSoftware, ['course_code']);
        $softwareData['course_id'] = $course->id;

        if ($this->newSoftware['id']) {
            $software = Software::findOrFail($this->newSoftware['id']);
            $software->update($softwareData);
        } else {
            $software = Software::create($softwareData);
        }


        $this->modal('add-software')->close();

        $this->reset('newSoftware');

        Flux::toast('Software added!', variant: 'success');
    }

    public function editSoftware(int $softwareId)
    {
        $software = Software::findOrFail($softwareId);
        $this->newSoftware['id'] = $softwareId;
        $this->newSoftware['course_code'] = $software->course->code;
        $this->newSoftware['building'] = $software->building;
        $this->newSoftware['lab'] = $software->lab;
        $this->newSoftware['name'] = $software->name;
        $this->newSoftware['version'] = $software->version;
        $this->newSoftware['config'] = $software->config;
        $this->newSoftware['notes'] = $software->notes;
        $this->newSoftware['os'] = $software->os;
        $this->modal('add-software')->show();
    }


    public function signOff(int $courseId)
    {
        $course = Course::findOrFail($courseId);
        $course->users()->attach(request()->user()->id);

        Flux::toast("{$course->code} signed off!", variant: 'success');
    }

    public function viewSoftwareDetails(int $softwareId)
    {
        $this->softwareDetails = Software::with('createdBy')->findOrFail($softwareId);
        $this->modal('view-software-details')->show();
    }

    public function removeSoftware(int $softwareId)
    {
        $software = Software::findOrFail($softwareId);
        $software->removed_at = now();
        $software->removed_by = Auth::user()?->id;
        $software->save();

        Flux::toast("{$software->name} marked for removal!", variant: 'success');
    }

    public function unmarkForRemoval(int $softwareId)
    {
        $software = Software::findOrFail($softwareId);
        $software->removed_at = null;
        $software->removed_by = null;
        $software->save();

        Flux::toast("{$software->name} unmarked for removal!", variant: 'success');
    }
}
