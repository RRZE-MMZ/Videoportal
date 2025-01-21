<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLocalUserCreated extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly User $user, private readonly string $token)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to '.config('app.name'),
        );
    }

    public function content(): Content
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $this->user->email,
        ]));

        return new Content(
            markdown: 'mail.new-local-user-created',
            with: [
                'user' => $this->user,
                'url' => $url,
            ]
        );
    }
}
