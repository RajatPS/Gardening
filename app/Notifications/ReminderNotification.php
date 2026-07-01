<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Message\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderNotification extends Notification
{
    use Queueable;

    protected $reminder;

    public function __construct($reminder)
    {
        $this->reminder = $reminder;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Reminder: ' . $this->reminder->task)
                    ->line('This is a reminder for: ' . $this->reminder->task)
                    ->line('Scheduled at: ' . $this->reminder->remind_at)
                    ->action('View', url('/reminders'))
                    ->line('Thank you for using our service.');
    }

    public function toArray($notifiable)
    {
        return [
            'task' => $this->reminder->task,
            'remind_at' => $this->reminder->remind_at,
            'type' => $this->reminder->type,
        ];
    }
}
