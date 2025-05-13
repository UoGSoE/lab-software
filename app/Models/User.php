<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Scopes\AcademicSessionScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

#[ScopedBy([AcademicSessionScope::class])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
            'signed_off_at' => 'date',
            'is_admin' => 'boolean',
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

        $oldUser = User::withoutGlobalScope(AcademicSessionScope::class)
            ->where('username', '=', $this->username)
            ->where('academic_session_id', '=', $previousAcademicSession->id)
            ->first();
        if (! $oldUser) {
            return collect([]);
        }

        return $oldUser->courses()->withoutGlobalScope(AcademicSessionScope::class)->get();
    }

    public function signOffLastYearsSoftware()
    {
        $lastYearsCourses = $this->getPreviousSignOffs();
        foreach ($lastYearsCourses as $course) {
            $thisYearsCopy = Course::where('code', '=', $course->code)->firstOrFail();
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
        return $this->forenames.' '.$this->surname;
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
}
