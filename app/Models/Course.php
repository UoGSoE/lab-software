<?php

namespace App\Models;

use App\Models\Traits\AcademicSessionScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory, AcademicSessionScope;

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
