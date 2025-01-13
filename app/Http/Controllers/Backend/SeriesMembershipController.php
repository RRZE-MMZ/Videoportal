<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeriesMembershipRequest;
use App\Models\Series;
use App\Models\User;
use App\Notifications\SeriesMembershipAddUser;
use App\Notifications\SeriesMembershipRemoveUser;
use Illuminate\Http\RedirectResponse;

use function to_route;

class SeriesMembershipController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return RedirectResponse
     */
    public function add(Series $series, SeriesMembershipRequest $request)
    {
        $validated = $request->validated();
        $user = User::findOrFail($validated['userID']);

        // after adding the user to series notify him via email
        $series->addMember($user)->notify(new SeriesMembershipAddUser($series));

        return to_route('series.edit', $series);
    }

    public function remove(Series $series, SeriesMembershipRequest $request)
    {
        $validated = $request->validated();
        $user = User::findOrFail($validated['userID']);

        $series->removeMember($user)->notify(new SeriesMembershipRemoveUser($series));

        return to_route('series.edit', $series);
    }
}
