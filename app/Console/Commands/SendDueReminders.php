<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use App\Notifications\ReminderNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class SendDueReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send due reminders and mark them as notified.';

    public function handle()
    {
        $now = Carbon::now();
        $due = Reminder::where('notified', false)
            ->where('remind_at', '<=', $now)
            ->get();

        foreach ($due as $r) {
            if ($r->user) {
                $r->user->notify(new ReminderNotification($r));
            } else {
                // Send to admin email when no user is attached (fallback)
                Notification::route('mail', config('mail.from.address'))
                    ->notify(new ReminderNotification($r));
            }
            $r->notified = true;
            $r->save();
            $this->info('Notified reminder #' . $r->id);
        }

        return 0;
    }
}
