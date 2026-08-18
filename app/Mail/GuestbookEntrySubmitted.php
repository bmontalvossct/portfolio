<?php

namespace App\Mail;

use App\Models\GuestbookEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestbookEntrySubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public GuestbookEntry $entry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->entry->email, $this->entry->name)],
            subject: "New portfolio review from {$this->entry->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.guestbook.entry-submitted',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
