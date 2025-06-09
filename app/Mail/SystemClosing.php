<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Carbon;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class SystemClosing extends Mailable
{
    use Queueable, SerializesModels;

    public $closingDate;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user)
    {
        
        $this->closingDate = Setting::getSetting('notifications_closing_date')->toDate()->format('jS F');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lab Software System Closing',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.system_closing',
            with: [
                'user' => $this->user,
                'closingDate' => $this->closingDate,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
