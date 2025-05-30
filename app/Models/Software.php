<?php

namespace App\Models;

use App\Models\Scopes\AcademicSessionScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ScopedBy([AcademicSessionScope::class])]
class Software extends Model
{
    /** @use HasFactory<\Database\Factories\SoftwareFactory> */
    use HasFactory;

    protected $fillable = ['name', 'version', 'course_id', 'os', 'building', 'lab', 'notes', 'config', 'is_new', 'is_free', 'created_by', 'academic_session_id'];

    protected function casts(): array
    {
        return [
            'building' => 'array',
            'os' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getLocationAttribute(): string
    {
        // TODO: this might be redundant - waiting on feedback
        return (string) $this->lab;
        $location = '';
        foreach ($this->building ?? [] as $building) {
            $location = $location.$building.', ';
        }

        return $location.($this->lab ? ' - '.$this->lab : '');
    }

    public function getOperatingSystemsAttribute(): string
    {
        return implode(', ', $this->os ?? []);
    }
}
