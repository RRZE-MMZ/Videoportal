<?php

namespace App\Listeners;

use App\Events\AssetDeleted;
use Illuminate\Support\Facades\Storage;

class DeleteAssetFile
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Update clip poster file and delete the given video file
     */
    public function handle(AssetDeleted $event): void
    {
        // delete poster image file
        if (! is_null($event->asset->clips->first()?->posterImage)) {
            Storage::disk('thumbnails')->delete($event->asset->clips->first()->posterImage);
            $event->asset->clips->first()->updatePosterImage();
        }

        // delete the video file
        Storage::disk('videos')->delete($event->asset->path);
    }
}
