<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Collection;
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
        'name',
        'email',
        'password',
        'username',
        'forenames',
        'surname',
        'is_staff',
        'is_admin',
        'academic_session_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'signed_off_at' => 'date',
        'is_admin' => 'boolean',
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

    public function getPreviousSignOffs(): Collection
    {
        $currentAcademicSession = AcademicSession::getDefault();
        $previousAcademicSession = $currentAcademicSession->getPrevious();

        $oldUser = User::where('username', '=', $this->username)->where('academic_session_id', '=', $previousAcademicSession->id)->first();
        if (! $oldUser) {
            return collect([]);
        }
        return $oldUser->courses()->get();
    }

    public function signOffLastYearsSoftware()
    {
        $currentAcademicSession = AcademicSession::getDefault();
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

    public function getFullNameAttribute(): string
    {
        return $this->forenames . ' ' . $this->surname;
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
}
