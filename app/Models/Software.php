<?php

namespace App\Models;

use App\Models\Scopes\AcademicSessionScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

#[ScopedBy([AcademicSessionScope::class])]
class Software extends Model
{
    /** @use HasFactory<\Database\Factories\SoftwareFactory> */
    use HasFactory;

    protected $fillable = ['name', 'version', 'course_id', 'os', 'building', 'lab', 'notes', 'config', 'is_new', 'is_free', 'created_by', 'academic_session_id', 'removed_by', 'removed_at'];

    protected function casts(): array
    {
        return [
            'building' => 'array',
            'os' => 'array',
            'removed_at' => 'datetime',
        ];
    }

    protected static function booted(): void

    {

        static::updated(function (Software $software) {

            Cache::forget('pendingDeletionCount');

        });

        static::deleted(function (Software $software) {

            Cache::forget('pendingDeletionCount');

        });

    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function removedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('course_id');
    }

    public function scopePendingDeletion($query)
    {
        return $query->whereNotNull('removed_at');
    }

    public function scopeForSession($query, $id)
    {
        return $query->withoutGlobalScope(AcademicSessionScope::class)->where('academic_session_id', '=', $id);
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
