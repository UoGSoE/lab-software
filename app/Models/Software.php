<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Software extends Model
{
    /** @use HasFactory<\Database\Factories\SoftwareFactory> */
    use HasFactory;

    protected $fillable = ['name', 'version', 'course_code', 'os', 'building', 'lab', 'notes', 'config', 'is_new', 'is_free', 'created_by', 'academic_session_id'];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getLocationAttribute(): string
    {
        return $this->building . ($this->lab ? ' - ' . $this->lab : '');
    }
}
