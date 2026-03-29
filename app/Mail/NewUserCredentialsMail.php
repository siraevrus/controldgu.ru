<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewUserCredentialsMail extends Mailable
{
    public function __construct(
        public User $user,
        protected string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Доступ к системе «'.config('app.name').'»',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-user-credentials',
            with: [
                'userName' => $this->user->name,
                'email' => $this->user->email,
                'plainPassword' => $this->plainPassword,
                'loginUrl' => url(route('login', absolute: false)),
                'appName' => config('app.name'),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
