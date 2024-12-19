<?php

namespace App\Policies;

use App\Enums\Acl;
use App\Models\Clip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClipPolicy
{
    use HandlesAuthorization;

    public function index(User $user): bool
    {
        return auth()->check() && ($user->isAdmin() || $user->isAssistant());
    }

    public function create(User $user): bool
    {
        return auth()->check() && ($user->isModerator() || $user->isAssistant() || $user->isAdmin());
    }

    public function edit(User $user, Clip $clip): bool
    {
        return $user->is($clip->owner) || $user->is($clip->series->owner) || ($user->isAdmin() || $user->isAssistant());
    }

    public function view(?User $user, Clip $clip): bool
    {
        if (optional($user)->is($clip->owner) || optional($user)->isAdmin() || optional($user)->isAssistant()) {
            return true;
        } elseif ($clip->is_public &&
            (is_null($clip->series->is_public) || $clip->series->is_public)
            && ($clip->assets()->count() > 0 ||
                $clip->is_livestream ||
                ($clip->hasRecordingDateInPast() && $clip->isPartOfSeries())
            )
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function viewComments(?User $user, Clip $clip): bool
    {
        return auth()->check() && $clip->allow_comments;
    }

    public function viewVideo(User $user, Clip $clip): bool
    {
        return (auth()->check() && $clip->acls->pluck('id')->contains(Acl::PUBLIC())) ||
            ($user->is($clip->owner));
    }

    public function canWatchVideo(?User $user, Clip $clip): bool
    {
        return $clip->checkAcls();
    }
}
