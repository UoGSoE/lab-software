<?php

namespace App\Livewire;

use Flux\Flux;
use App\Models\Course;
use Livewire\Component;
use App\Models\Software;
use Illuminate\Support\Arr;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class HomePage extends Component
{
    use WithPagination;

    public array $newSoftware = [
        'os' => ['Windows'],
        'name' => '',
        'version' => '',
        'building' => '',
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
        // $this->filters['school'] = request()->user()->school;
    }

    public function render()
    {
        return view('livewire.home-page', [
            'courses' => $this->getFilteredCourses(),
            'softwareTitles' => Software::pluck('name')->unique(),
            'courseCodes' => $this->getCourseCodes(),
        ]);
    }

    public function getCourseCodes(): Collection
    {
        return Course::when(
            trim($this->filters['school']), fn ($query) => $query->where('code', 'like', $this->filters['school'] . '%')
        )->pluck('code')->unique()->sort();
    }

    public function getFilteredCourses(): LengthAwarePaginator
    {
        $courses = Course::whereHas('software', function ($query) {
            $query->where('name', 'like', '%' . $this->filters['software'] . '%');
        })->with(['software' => function ($query) {
            $query->where('name', 'like', '%' . $this->filters['software'] . '%');
        }])->orderBy('code')
            ->when(
                trim($this->filters['school']), fn ($query) => $query->where('code', 'like', $this->filters['school'] . '%')
            )
            ->when(
                trim($this->filters['course']), fn ($query) => $query->where('code', 'like', '%' . $this->filters['course'] . '%')
            );

        return $courses->paginate(25);
    }

    public function updatedFilters()
    {
        // request()->user()->update(['school' => $this->filters['school']]);
    }

    public function addSoftware()
    {
        $newSoftware = $this->newSoftware;
        $newSoftware['os'] = implode(',', $newSoftware['os']);
        Software::create(Arr::except($newSoftware, ['course_code']));

        $this->modal('add-software')->close();

        $this->reset('newSoftware');

        Flux::toast('Software added!');
    }
}
