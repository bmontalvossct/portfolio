<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PrivateInquiry extends Mailable
{
    public function __construct(public array $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New portfolio inquiry');
    }

    public function content(): Content
    {
        return new Content(text: 'mail.private-inquiry');
    }
}
