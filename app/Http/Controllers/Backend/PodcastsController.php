<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Traits\HandlesFilePondFiles;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePodcastRequest;
use App\Models\Image;
use App\Models\Podcast;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class PodcastsController extends Controller
{
    use HandlesFilePondFiles;

    public function index(): View
    {
        $podcasts = (auth()->user()->can('administrate-assistant-pages'))
            ? Podcast::all()->sortByDesc('updated_at')
            : auth()->user()->podcasts()->get();

        return view('backend.podcasts.index', compact('podcasts'));
    }

    public function create(): View
    {
        return view('backend.podcasts.create');
    }

    public function store(StorePodcastRequest $request): RedirectResponse
    {
        $settings = Setting::portal();
        $settingsData = $settings->data;

        $validated = $request->validated();

        if (is_null($validated['image'])) {
            $validated['image_id'] = Image::find($settingsData['default_image_id'])->id;
        } else {
            $imageDescription = 'Podcast '.$validated['title'].' cover image';
            $image = $this->uploadAndCreateImage(filePath: $validated['image'], description: $imageDescription);
            $validated['image_id'] = $image->id;
        }

        $validated['owner_id'] = auth()->id();
        $podcast = Podcast::create(Arr::except($validated, ['hosts', 'guests', 'image']));
        $podcast->prepareAndSyncPodcastPresenters($validated['hosts'], $validated['guests']);

        return to_route('podcasts.edit', $podcast);
    }

    public function edit(Podcast $podcast): View
    {
        $this->authorize('edit-podcast', $podcast);

        return view('backend.podcasts.edit', compact(['podcast']));
    }

    public function update(StorePodcastRequest $request, Podcast $podcast): RedirectResponse
    {
        $this->authorize('edit-podcast', $podcast);

        $validated = $request->validated();
        if (! is_null($validated['image'])) {
            $imageDescription = 'Podcast '.$validated['title'].' cover image';
            $image = $this->uploadAndCreateImage(filePath: $validated['image'], description: $imageDescription);
            $validated['image_id'] = $image->id;
        }

        $podcast->update(Arr::except($validated, ['hosts', 'guests', 'image']));
        $podcast->prepareAndSyncPodcastPresenters($validated['hosts'], $validated['guests']);

        return to_route('podcasts.edit', $podcast);
    }

    public function destroy(Podcast $podcast): RedirectResponse
    {
        $this->authorize('edit-podcast', $podcast);

        $podcast->delete();

        return to_route('podcasts.index');
    }
}
