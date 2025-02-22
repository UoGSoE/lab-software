<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\AcademicSessionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, AcademicSessionScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'forenames',
        'surname',
        'is_staff',
        'is_admin',
        'email',
        'password',
        'academic_session_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'signed_off_at' => 'date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)->withTimestamps();
    }

    public function getPreviousSignOffs()
    {
        $currentAcademicSession = AcademicSession::getDefualt();
        $previousAcademicSession = $currentAcademicSession->previous();

        $oldUser = User::where('username', $this->username)->where('academic_session_id', $previousAcademicSession->id)->first();
        if (! $oldUser) {
            throw new \Exception('User not found in previous session');
        }
        return $oldUser->courses()->get();
    }
    public function signOffLastYearsSoftware()
    {
        $currentAcademicSession = AcademicSession::getDefualt();
        $lastYearsCourses = $this->getPreviousSignOffs();
        foreach ($lastYearsCourses as $course) {
            $thisYearsCopy = Course::where('code', '=', $course->code)->where('academic_session_id', $currentAcademicSession->id)->firstOrFail();
            $thisYearsCopy->users()->attach($this->id);
        }
    }

    public function getSignoffLink(int $durationDays = 30)
    {
        return URL::temporarySignedRoute(
            'signed-off', now()->addDays($durationDays), ['user' => $this]
        );
    }
}
