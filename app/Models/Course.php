<?php

namespace App\Models;

use App\Models\Scopes\AcademicSessionScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([AcademicSessionScope::class])]
class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    protected $fillable = ['title', 'code', 'academic_session_id'];

    public function software(): BelongsToMany
    {
        return $this->belongsToMany(Software::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
