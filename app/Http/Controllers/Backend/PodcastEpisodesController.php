<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Traits\HandlesFilePondFiles;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePodcastEpisodeRequest;
use App\Models\Image;
use App\Models\Podcast;
use App\Models\PodcastEpisode;
use App\Models\Setting;
use App\Models\Traits\RecordsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class PodcastEpisodesController extends Controller
{
    use HandlesFilePondFiles;
    use RecordsActivity;

    public function create(Podcast $podcast)
    {
        return view('backend.podcasts.episode.create', compact('podcast'));
    }

    public function store(Podcast $podcast, StorePodcastEpisodeRequest $request)
    {
        $validated = $request->validated();

        $validated['owner_id'] = auth()->id();
        $validated['image_id'] =
            $this->updateEpisodesImage(image: $validated['image'], imageTitle: $validated['title'], podcast: $podcast);
        $episode = $podcast->episodes()->create(Arr::except($validated, ['hosts', 'guests', 'image', 'tags']));
        $episode->prepareAndSyncPodcastPresenters($validated['hosts'], $validated['guests']);
        $episode->addTags(collect($validated['tags']));

        return to_route('podcasts.episodes.edit', compact('podcast', 'episode'));
    }

    public function edit(Podcast $podcast, PodcastEpisode $episode)
    {
        $previousNextEpisodesCollection = $episode->previousNextEpisodeCollection();

        return view('backend.podcasts.episode.edit', compact('podcast', 'episode', 'previousNextEpisodesCollection'));
    }

    public function update(Podcast $podcast, PodcastEpisode $episode, StorePodcastEpisodeRequest $request)
    {
        $validated = $request->validated();
        if (! is_null($validated['image'])) {
            $validated['image_id'] =
                $this->updateEpisodesImage(
                    image: $validated['image'],
                    imageTitle: $validated['title'],
                    podcast: $podcast
                );
        }

        $episode->update(Arr::except($validated, ['hosts', 'guests', 'image', 'tags']));

        $episode->addTags(collect($validated['tags']));

        return to_route('podcasts.episodes.edit', compact('podcast', 'episode'));
    }

    public function destroy(Podcast $podcast, PodcastEpisode $episode): RedirectResponse
    {
        $this->authorize('edit-podcast', $podcast);

        $episode->delete();

        return to_route('podcasts.edit', $podcast);
    }

    private function updateEpisodesImage(?string $image, string $imageTitle, Podcast $podcast)
    {
        $setting = Setting::portal();
        $settingData = $setting->data;
        if (is_null($image)) {
            $imageID = ($podcast->cover)
                ? $podcast->image_id
                : Image::find($settingData['default_image_id'])->id;
        } else {
            $imageDescription = 'Podcast Episode'.$imageTitle.' cover image';
            $image = $this->uploadAndCreateImage(filePath: $image, description: $imageDescription);
            $imageID = $image->id;
        }

        return $imageID;
    }
}
