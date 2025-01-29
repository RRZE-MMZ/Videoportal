<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MassOwnershipAssignment extends Notification
{
    use Queueable;

    public function __construct(protected Collection $series)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->bcc(env('DEV_MAIL_ADDRESS'))
            ->subject('Änderung der Zugriffsrechte auf einem Videokurs bei '.env('APP_URL'))
            ->greeting('Guten Tag '.$notifiable->getFullNameAttribute().',')
            ->line(auth()->user()->getFullNameAttribute().' hat Ihnen im Videoportal gerade für den Videoserien 
            **"'.$this->series->pluck('title').'"** die Berechtigung zum Editieren und Hochladen von Videos übertragen.')
            ->line('Sie erhalten dadurch mehr Möglichkeiten und auch mehr Kontrolle über Ihre Videos!')
            ->line('Wenn dies nicht mit Ihnen abgesprochen war bzw. wenn Sie dies nicht wünschen, melden Sie sich 
            bitte per Reply bei uns. Wenn diese Änderung in Ordnung geht, müssen Sie nichts weiter veranlassen.')
            ->action('Meine Serien anschauen', route('series.index'))
            ->salutation('Mit freundlichen Grüßen,<br> Ihr Multimediazentrum');
    }
}
