<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcademicSession extends Model
{
    /** @use HasFactory<\Database\Factories\AcademicSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public static function getDefault(): ?AcademicSession
    {
        return self::where('is_default', true)->first();
    }

    public function setAsDefault(): void
    {
        $this->is_default = true;
        $this->save();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function software(): HasMany
    {
        return $this->hasMany(Software::class);
    }

    public function copyForwardTo(AcademicSession $newSession): void
    {
        foreach (User::where('academic_session_id', $this->id)->get() as $user) {
            $newUser = $user->replicate();
            $newUser->academic_session_id = $newSession->id;
            $newUser->save();
        }
        foreach (Software::where('academic_session_id', $this->id)->get() as $software) {
            $newSoftware = $software->replicate();
            $newSoftware->academic_session_id = $newSession->id;
            $newSoftware->save();
        }
        foreach (Course::where('academic_session_id', $this->id)->get() as $course) {
            $newCourse = $course->replicate();
            $newCourse->academic_session_id = $newSession->id;
            $newCourse->save();
        }
    }
}
