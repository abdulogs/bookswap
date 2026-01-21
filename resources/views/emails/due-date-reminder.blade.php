<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Return Reminder</title>
</head>

<body
    style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div
        style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">⏰ Book Return Reminder</h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px;">
        @if ($daysRemaining < 0)
            <h2 style="color: #dc2626;">⚠️ Book Return Overdue</h2>
            <p>Hello {{ $bookRequest->borrower->name }},</p>
            <p style="color: #dc2626; font-weight: bold;">The book you borrowed is now <strong>{{ abs($daysRemaining) }}
                    day(s) overdue</strong>.</p>
        @else
            <h2 style="color: #f59e0b;">📅 Book Return Due Soon</h2>
            <p>Hello {{ $bookRequest->borrower->name }},</p>
            <p>This is a friendly reminder that the book you borrowed is due in <strong>{{ $daysRemaining }}
                    day(s)</strong>.</p>
        @endif

        <div
            style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b;">
            <h3 style="margin: 0 0 10px 0;">{{ $bookRequest->book->title }}</h3>
            <p style="margin: 5px 0; color: #6b7280;">by {{ $bookRequest->book->author }}</p>
            <p style="margin: 5px 0;"><strong>Due Date:</strong> {{ $bookRequest->due_date->format('F d, Y') }}</p>
            <p style="margin: 5px 0;"><strong>Owner:</strong> {{ $bookRequest->owner->name }}</p>
        </div>

        <p>Please make arrangements to return the book to the owner as soon as possible.</p>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center;">
            <a href="{{ route('requests.index') }}"
                style="display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">View
                My Requests</a>
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px;">
        <p>This is an automated reminder from BookSwap. Please do not reply to this email.</p>
    </div>
</body>

</html>
