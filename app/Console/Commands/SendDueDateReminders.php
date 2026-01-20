<?php

namespace App\Console\Commands;

use App\Mail\DueDateReminder;
use App\Models\BookRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDueDateReminders extends Command
{
    protected $signature = 'bookswap:send-due-reminders';
    protected $description = 'Send email reminders for books due soon or overdue';

    public function handle()
    {
        $this->info('Checking for books due soon or overdue...');

        // Get approved requests with due dates
        $requests = BookRequest::where('status', 'Approved')
            ->whereNotNull('due_date')
            ->whereHas('book')
            ->with(['book', 'borrower', 'owner'])
            ->get();

        $sentCount = 0;

        foreach ($requests as $request) {
            $daysRemaining = now()->diffInDays($request->due_date, false);
            
            // Send reminder if:
            // 1. Due in 3 days or less (but not overdue yet)
            // 2. Already overdue
            // 3. Haven't sent a reminder in the last 24 hours
            if (($daysRemaining <= 3 && $daysRemaining >= 0) || $daysRemaining < 0) {
                $shouldSend = false;
                
                if (!$request->reminder_sent) {
                    $shouldSend = true;
                } elseif ($request->last_reminder_at) {
                    // Send another reminder if last one was more than 24 hours ago
                    $hoursSinceLastReminder = now()->diffInHours($request->last_reminder_at);
                    if ($hoursSinceLastReminder >= 24) {
                        $shouldSend = true;
                    }
                }
                
                if ($shouldSend) {
                    try {
                        Mail::to($request->borrower->email)->send(new DueDateReminder($request));
                        
                        $request->update([
                            'reminder_sent' => true,
                            'last_reminder_at' => now(),
                        ]);
                        
                        // Also create a notification
                        \App\Models\Notification::create([
                            'user_id' => $request->borrower_id,
                            'type' => 'due_reminder',
                            'title' => $daysRemaining < 0 ? 'Book Return Overdue' : 'Book Return Due Soon',
                            'message' => "The book '{$request->book->title}' is " . 
                                        ($daysRemaining < 0 ? abs($daysRemaining) . " day(s) overdue" : "due in {$daysRemaining} day(s)"),
                            'notifiable_type' => BookRequest::class,
                            'notifiable_id' => $request->id,
                            'email_sent' => true,
                        ]);
                        
                        $sentCount++;
                        $this->line("Sent reminder to {$request->borrower->name} for '{$request->book->title}'");
                    } catch (\Exception $e) {
                        $this->error("Failed to send reminder to {$request->borrower->name}: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Sent {$sentCount} reminder(s).");
        return Command::SUCCESS;
    }
}
