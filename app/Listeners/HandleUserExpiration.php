<?php

namespace App\Listeners;

use App\Events\UserExpired;
use App\Models\Activity;

class HandleUserExpiration
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserExpired $event): void
    {
        $expiredUser = $event->user; // get the user from event object
        $expiredUser->subscriptions()->delete(); // delete user subscriptions
        $expiredUser->series->each(function ($series) use ($expiredUser) {
            $series->owner_id = null;
            $series->save();
            $series->recordActivity("Owner username {$expiredUser->username} is expired");
        }); // reset user series
        $expiredUser->clips->each(function ($clip) use ($expiredUser) {
            $clip->owner_id = null;
            $clip->save();
            $clip->recordActivity("Owner username: {$expiredUser->username} is expired");
        }); // reset user clips
        $expiredUser->podcasts->each(function ($podcast) use ($expiredUser) {
            $podcast->owner_id = null;
            $podcast->save();
            $podcast->recordActivity("Owner username: {$expiredUser->username} is expired");
        }); // reset user podcasts
        $expiredUser->podcastEpisodes->each(function ($podcastEpisode) use ($expiredUser) {
            $podcastEpisode->owner_id = null;
            $podcastEpisode->save();
            $podcastEpisode->recordActivity("Owner username: {$expiredUser->username} is expired");
        }); // reset user podcast Episodes
        $expiredUser->supervisedClips()->each(function ($clip) use ($expiredUser) {
            $clip->supervisor_id = null;
            $clip->save();
            $clip->recordActivity("Supervised username: {$expiredUser->username} is expired");
        }); // reset user supervised clips
        $expiredUser->comments()->delete(); // delete (?) user comments
        Activity::where('user_id', $expiredUser->id)->update(['user_real_name' => 'DELETED USER']);
        $expiredUser->delete(); // deleting the user will also delete user settings
    }
}
