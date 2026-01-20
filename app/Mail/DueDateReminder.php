<?php

namespace App\Mail;

use App\Models\BookRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DueDateReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BookRequest $bookRequest
    ) {}

    public function envelope(): Envelope
    {
        $daysRemaining = now()->diffInDays($this->bookRequest->due_date, false);
        
        $subject = $daysRemaining < 0 
            ? 'Book Return Overdue - Action Required'
            : 'Book Return Due Soon - Reminder';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.due-date-reminder',
            with: [
                'bookRequest' => $this->bookRequest,
                'daysRemaining' => now()->diffInDays($this->bookRequest->due_date, false),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
