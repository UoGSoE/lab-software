<?php

namespace App\Mail;

use App\Models\Scopes\AcademicSessionScope;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SystemOpen extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public Collection $softwareList;

    public function __construct(User $user)
    {
        $this->user = $user;
        // build a collection of course codes, each with a collection of software names & versions
        $this->softwareList = $user->courses()->withoutGlobalScope(AcademicSessionScope::class)
            ->get()
            ->reject(
                fn ($course) => $course->software()->withoutGlobalScope(AcademicSessionScope::class)->count() === 0
            )
            ->mapWithKeys(function ($course) {
                $software = $course->software()->withoutGlobalScope(AcademicSessionScope::class)->get()->map(function ($software) {
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
