<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\PodcastEpisode;

class ShowPodcastsController extends Controller
{
    /**
     * Display a listing of all podcasts.
     */
    public function index()
    {
        // for visitors show only the published podcasts with episodes containing audio files
        $podcasts = Podcast::where('is_published', true)
            ->whereHas('episodes.assets')
            ->orderBy('updated_at', 'desc')->paginate(12);

        return view('frontend.podcasts.index', compact('podcasts'));
    }

    public function show(Podcast $podcast)
    {
        return view('frontend.podcasts.show', compact('podcast'));
    }

    public function episode(Podcast $podcast, PodcastEpisode $episode)
    {
        return view('frontend.podcasts.episode.show', compact('podcast', 'episode'));
    }
}
