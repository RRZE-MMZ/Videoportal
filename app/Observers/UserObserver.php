<?php

namespace App\Observers;

use App\Enums\Role;
use App\Models\Activity;
use App\Models\Setting;
use App\Models\User;
use App\Services\OpenSearchService;

class UserObserver
{
    public function __construct(private OpenSearchService $openSearchService) {}

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->assignRole(Role::USER);
        $user->settings()->create([
            'name' => $user->username,
            'data' => config('settings.user'), ]);

        session()->flash('flashMessage', "{$user->getFullNameAttribute()} ".__FUNCTION__.' successfully');

        $this->openSearchService->createIndex($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        session()->flash('flashMessage', "{$user->getFullNameAttribute()} ".__FUNCTION__.' successfully');

        $this->openSearchService->updateIndex($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Setting::where('name', $user->username)->delete();
        $user->subscriptions()->delete(); // delete user subscriptions
        $user->series->each(function ($series) use ($user) {
            $series->owner_id = null;
            $series->save();
            $series->recordActivity("Owner username {$user->username} is expired");
        }); // reset user series
        $user->clips->each(function ($clip) use ($user) {
            $clip->owner_id = null;
            $clip->save();
            $clip->recordActivity("Owner username: {$user->username} is expired");
        }); // reset user clips
        $user->comments()->delete(); // delete (?) user comments
        $user->podcasts->each(function ($podcast) use ($user) {
            $podcast->owner_id = null;
            $podcast->save();
            $podcast->recordActivity("Owner username: {$user->username} is expired");
        }); // reset user podcasts
        $user->supervisedClips()->each(function ($clip) use ($user) {
            $clip->owner_id = null;
            $clip->save();
            $clip->recordActivity("Supervised username: {$user->username} is expired");
        }); // reset user supervised clips
        Activity::where('user_id', $user->id)->update(['user_real_name' => 'DELETED USER']);
        session()->flash('flashMessage', "{$user->getFullNameAttribute()} ".__FUNCTION__.' successfully');

        $this->openSearchService->deleteIndex($user);
    }
}
