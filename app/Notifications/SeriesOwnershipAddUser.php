<?php

namespace App\Notifications;

use App\Models\Series;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SeriesOwnershipAddUser extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(protected Series $series)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->bcc(env('DEV_MAIL_ADDRESS'))
            ->subject('Änderung der Zugriffsrechte auf einem Videokurs bei '.env('APP_URL'))
            ->greeting('Guten Tag '.$notifiable->getFullNameAttribute().',')
            ->line(auth()->user()->getFullNameAttribute().' hat Ihnen im Videoportal gerade für den Videoserien 
            **"'.$this->series->title.'"** die Berechtigung zum Editieren und Hochladen von Videos übertragen.')
            ->line('Sie erhalten dadurch mehr Möglichkeiten und auch mehr Kontrolle über Ihre Videos!')
            ->line('Wenn dies nicht mit Ihnen abgesprochen war bzw. wenn Sie dies nicht wünschen, melden Sie sich 
            bitte per Reply bei uns. Wenn diese Änderung in Ordnung geht, müssen Sie nichts weiter veranlassen.')
            ->action('Kurs Link', route('series.edit', $this->series))
            ->salutation('Mit freundlichen Grüßen,<br> Ihr Multimediazentrum');
    }
}
