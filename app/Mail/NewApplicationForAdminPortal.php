<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewApplicationForAdminPortal extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly User $user)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Application For Admin Portal',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-application-for-admin-portal',
            with: [
                'user' => $this->user,
            ]
        );
    }
}
