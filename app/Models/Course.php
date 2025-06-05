<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AcademicSessionScope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ScopedBy([AcademicSessionScope::class])]
class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    protected $fillable = ['title', 'code', 'academic_session_id'];

    public function software(): HasMany
    {
        return $this->hasMany(Software::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function scopeForSession($query, $id)
    {
        return $query->withoutGlobalScope(AcademicSessionScope::class)->where('academic_session_id', '=', $id);
    }
}
