<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\AcademicSession;
use Illuminate\Support\Collection;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\Scopes\AcademicSessionScope;

class SystemOpen extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public Collection $softwareList;

    public function __construct(User $user)
    {
        $this->user = $user;
        $currentSession = AcademicSession::getDefault();
        $previousSession = $currentSession->getPrevious();
        if (! $previousSession) {
            $previousSession = $currentSession;
        }
        $lastYearsUser = User::forSession($previousSession->id)->where('username', $user->username)->first();
        
        if (! $lastYearsUser) {
            $lastYearsUser = $user;
        }
        // build a collection of course codes, each with a collection of software names & versions
        $this->softwareList = $lastYearsUser->courses()->forSession($previousSession->id)
            ->get()
            ->reject(
                fn ($course) => $course->software()->forSession($previousSession->id)->count() === 0
            )
            ->mapWithKeys(function ($course) use ($previousSession) {
                $software = $course->software()->forSession($previousSession->id)->get()->map(function ($software) {
                    return $software->name.($software->version ? ' version '.$software->version : '');
                });

                return [$course->code => $software];
            });
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lab Software System Open',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.system_open',
            with: [
                'user' => $this->user,
                'softwareList' => $this->softwareList,
            ],
        );
    }

    

    public function attachments(): array
    {
        return [];
    }
}
