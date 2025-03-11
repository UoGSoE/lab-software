<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AcademicSessionScope;
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
        return self::where('is_default', true)->firstOrFail();
    }

    public static function getUsersSession(): ?AcademicSession
    {
        if (session()->missing('academic_session_id')) {
            return self::getDefault();
        }
        return self::find(session()->get('academic_session_id'));
    }

    public function setAsDefault(): void
    {
        self::where('id', '!=', $this->id)->update(['is_default' => false]);
        $this->is_default = true;
        $this->save();
    }

    public function getPrevious(): ?AcademicSession
    {
        return self::where('created_at', '<', $this->created_at)->orderBy('created_at', 'desc')->first();
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
        $thisId = $this->id;
        DB::transaction(function () use ($newSession, $thisId) {
            $newSoftwareMap = [];

            foreach (User::withoutGlobalScope(AcademicSessionScope::class)->where('academic_session_id', $thisId)->get() as $user) {
                $newUser = $user->replicate();
                $newUser->academic_session_id = $newSession->id;
                $newUser->save();
            }
            foreach (Software::withoutGlobalScope(AcademicSessionScope::class)->where('academic_session_id', $thisId)->get() as $software) {
                $newSoftware = $software->replicate();
                $newSoftware->academic_session_id = $newSession->id;
                $newSoftware->save();
                if (!isset($newSoftwareMap[$software->id])) {
                    $newSoftwareMap[$software->id] = [];
                }
                $newSoftwareMap[$software->id][] = $newSoftware->id;
            }
            foreach (Course::withoutGlobalScope(AcademicSessionScope::class)->where('academic_session_id', $thisId)->get() as $course) {
                $newCourse = $course->replicate();
                $newCourse->academic_session_id = $newSession->id;
                $newCourse->save();
                $course->software()->withoutGlobalScope(AcademicSessionScope::class)->get()->each(function ($software) use ($newCourse, $newSoftwareMap) {
                    $newCourse->software()->attach($newSoftwareMap[$software->id]);
                });
            }
        });
    }
}
