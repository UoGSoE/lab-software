<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class SystemOpen extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Collection $softwareList;

    public function __construct(User $user)
    {
        # NOTE: assumes the user has courses.software eager loaded
        $this->user = $user;
        $this->softwareList = $user->courses->filter(function ($course) {
            return $course->software->count() > 0;
        })->mapWithKeys(function ($course) {
            # we end up with a collection of course codes, each with a collection of software names
            return [$course->code => $course->software->pluck('name')];
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
