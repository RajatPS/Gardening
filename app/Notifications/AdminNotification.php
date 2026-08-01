<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdminNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected array $methods;

    public function __construct(string $title, string $message, array $methods = [])
    {
        $this->title = $title;
        $this->message = $message;
        $this->methods = $methods;
    }

    public function via($notifiable)
    {
        $channels = ['database'];

        if (in_array('email', $this->methods, true)) {
            $channels[] = 'mail';
        }

        // SMS/push channels not implemented here — record intent in DB payload
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->title)
                    ->line($this->message)
                    ->action('View', url('/'));
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'methods' => $this->methods,
        ];
    }
}
