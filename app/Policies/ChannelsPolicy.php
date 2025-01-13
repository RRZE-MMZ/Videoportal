<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Support\Str;

class ChannelsPolicy
{
    /**
     * Determine whether the user can create/activate models.
     */
    public function create(User $user, string $handle): bool
    {
        // a  user can activate only his channel handle
        return $handle === '@'.Str::before($user->email, '@');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Channel $channel): bool
    {
        return $user->is($channel->owner) || ($user->isAdmin() || $user->isAssistant());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Channel $channel): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Channel $channel): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Channel $channel): bool
    {
        //
    }
}
