<?php

namespace App\Listeners;

use App\Events\ClipDeleting;
use Illuminate\Support\Facades\Storage;

class DeleteClipAssets
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
     * Handle the event.
     */
    public function handle(ClipDeleting $event): void
    {
        // delete all clip documents
        $event->clip->documents->each(function ($document) {
            $document->delete();
        });

        // delete all clip files
        $event->clip->assets->each(function ($asset) {
            $asset->delete();
        });

        // check if clip is public and unlink all symbolic links
        if ($event->clip->acls->pluck('id')->contains('1')) {
            $event->clip->assets->each(function ($asset) {
                if (Storage::disk('assetsSymLinks')->exists("{$asset->guid}.".getFileExtension($asset))) {
                    unlink(Storage::disk('assetsSymLinks')->path("{$asset->guid}.".getFileExtension($asset)));
                }
            });
        }
    }
}
