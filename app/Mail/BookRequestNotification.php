<?php

namespace App\Mail;

use App\Models\BookRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BookRequest $bookRequest,
        public string $type
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->type) {
            'request_received' => 'New Book Request Received',
            'request_approved' => 'Book Request Approved',
            'request_rejected' => 'Book Request Rejected',
            'book_returned' => 'Book Returned',
            default => 'Book Request Notification',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.book-request-notification',
            with: [
                'bookRequest' => $this->bookRequest,
                'type' => $this->type,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
