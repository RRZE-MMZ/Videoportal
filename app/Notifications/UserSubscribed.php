<?php

namespace App\Notifications;

use App\Models\Series;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserSubscribed extends Notification implements ShouldQueue
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
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $series = Series::findOrFail($this->series->id);

        $series->refresh();
        $title = $series->title;

        if ($notifiable->subscriptions()->where('series_id', $series->id)->exists()) {
            return (new MailMessage)
                ->subject("You have subscribed to $title Series !")
                ->line("Hi {$notifiable->getFullNameAttribute()}")
                ->line("you have subscribed to $title. You will get regular updates for any new content")
                ->line('You can unsubscribe at any time using the following ')
                ->action(
                    'link ',
                    route('frontend.series.show', $this->series)
                )
                ->line('Thanks!');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
