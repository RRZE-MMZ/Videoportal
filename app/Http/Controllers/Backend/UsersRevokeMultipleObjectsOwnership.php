<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRevokeMultipleClipsOwnershipRequest;
use App\Http\Requests\UserRevokeMultipleSeriesOwnershipRequest;
use App\Models\Clip;
use App\Models\Series;
use App\Models\User;
use App\Notifications\MassOwnershipAssignment;
use App\Notifications\MassOwnershipRevoke;

class UsersRevokeMultipleObjectsOwnership extends Controller
{
    public function series(User $user, UserRevokeMultipleSeriesOwnershipRequest $request)
    {
        $validated = $request->validated();
        $previousOwner = $user;
        $seriesIds = collect($validated['series_ids']);
        $newOwner = User::find($validated['userID']);
        $seriesCollection = Series::whereIn('id', $seriesIds)->get();
        $seriesCollection->each(function ($series) use ($newOwner) {
            $series->owner_id = $newOwner->id;
            $series->save();
            $series->clips()->update(['owner_id' => $newOwner->id]);
        });

        $previousOwner->notify(new MassOwnershipRevoke($seriesCollection));
        $newOwner->notify(new MassOwnershipAssignment($seriesCollection));

        session()->flash('flashMessage', "{$user->getFullNameAttribute()}  Series updated successfully");

        return to_route('users.edit', $user);

    }

    public function clips(User $user, UserRevokeMultipleClipsOwnershipRequest $request)
    {
        $validated = $request->validated();
        $previousOwner = $user;
        $clipIds = collect($validated['clip_ids']);
        $newOwner = User::find($validated['userID']);
        $clipsQuery = Clip::whereIn('id', $clipIds);
        $clips = $clipsQuery->get();
        $clipsQuery->update(['owner_id' => $newOwner->id]);

        $previousOwner->notify(new MassOwnershipRevoke($clips, 'clips'));
        $newOwner->notify(new MassOwnershipAssignment($clips, 'clips'));

        session()->flash('flashMessage', "{$user->getFullNameAttribute()}  Clips updated successfully");

        return to_route('users.edit', $user);

    }
}
