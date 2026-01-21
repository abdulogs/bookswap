<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'BookSwap Notification' }}</title>
</head>

<body
    style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">📚 BookSwap</h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px;">
        @if ($type === 'request_received')
            <h2 style="color: #1f2937;">New Book Request</h2>
            <p>Hello {{ $bookRequest->owner->name }},</p>
            <p><strong>{{ $bookRequest->borrower->name }}</strong> has requested to borrow your book:</p>
            <div
                style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;">
                <h3 style="margin: 0 0 10px 0;">{{ $bookRequest->book->title }}</h3>
                <p style="margin: 0; color: #6b7280;">by {{ $bookRequest->book->author }}</p>
            </div>
            @if ($bookRequest->message)
                <p><strong>Message from borrower:</strong></p>
                <p style="background: white; padding: 15px; border-radius: 8px; font-style: italic;">
                    {{ $bookRequest->message }}</p>
            @endif
            <p>Please log in to your account to approve or reject this request.</p>
        @elseif($type === 'request_approved')
            <h2 style="color: #1f2937;">Book Request Approved! 🎉</h2>
            <p>Hello {{ $bookRequest->borrower->name }},</p>
            <p>Great news! Your request to borrow <strong>{{ $bookRequest->book->title }}</strong> has been approved.
            </p>
            <div
                style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #10b981;">
                <p><strong>Due Date:</strong> {{ $bookRequest->due_date->format('F d, Y') }}</p>
                <p><strong>Owner:</strong> {{ $bookRequest->owner->name }}</p>
            </div>
            <p>Please make sure to return the book by the due date.</p>
        @elseif($type === 'request_rejected')
            <h2 style="color: #1f2937;">Book Request Status</h2>
            <p>Hello {{ $bookRequest->borrower->name }},</p>
            <p>Unfortunately, your request to borrow <strong>{{ $bookRequest->book->title }}</strong> has been rejected
                by the owner.</p>
            <p>You can browse other available books in our library.</p>
        @elseif($type === 'book_returned')
            <h2 style="color: #1f2937;">Book Returned</h2>
            <p>Hello {{ $bookRequest->borrower->name }},</p>
            <p>The book <strong>{{ $bookRequest->book->title }}</strong> has been marked as returned.</p>
            <p>Thank you for using BookSwap! We hope you enjoyed the book.</p>
        @endif

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center;">
            <a href="{{ route('requests.index') }}"
                style="display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">View
                Requests</a>
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px;">
        <p>This is an automated email from BookSwap. Please do not reply to this email.</p>
    </div>
</body>

</html>
