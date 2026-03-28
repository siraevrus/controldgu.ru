<?php

namespace App\Mail;

use App\Models\Alert;
use App\Models\Dgu;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertOpenedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $openUrl;

    public function __construct(
        public Alert $alert,
        public Dgu $dgu,
    ) {
        $this->openUrl = url(route('alerts.show', $this->alert, absolute: false));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Тревога ДГУ: '.$this->alert->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.alert-opened',
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
