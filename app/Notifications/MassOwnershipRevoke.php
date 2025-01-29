<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MassOwnershipRevoke extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Collection $objects, protected string $type = 'series')
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Änderung der Zugriffsrechte auf mehrere '.$this->type.' bei '.env('APP_URL'))
            ->greeting('Guten Tag '.$notifiable->getFullNameAttribute().',')
            ->line('you have been removed by '.auth()->user()->getFullNameAttribute().' as '.$this->type.' moderator from the
            following '.$this->type.' "'.$this->objects->pluck('title').'".')
            ->action('Meine '.$this->type.' anschauen', route($this->type.'.index'))
            ->salutation('Mit freundlichen Grüßen, Ihr Multimediazentrum');

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
