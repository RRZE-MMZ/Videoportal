<?php

namespace App\Console\Commands;

use App\Models\Podcast;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RenamePodcastFolders extends Command
{
    /**
     *  After migration is finished this command and also the corresponding test can be safely deleted
     *
     * @var string
     */
    protected $signature = 'podcasts:rename-folders';

    protected $description = 'Rename folders in storage/app/podcasts-files from courseID_oldID to podcastID_newID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $podcasts = Podcast::all();

        $this->info('Looking for podcasts files...');

        $podcasts->each(function ($podcast) {
            if (! is_null($podcast->image_id)) {
                $oldFilePath = 'podcasts-files/courseID_'.$podcast->old_podcast_id.'/'.$podcast->cover->file_name;
                if (Storage::exists($oldFilePath)) {
                    Storage::move($oldFilePath, 'images/'.$podcast->cover->file_name);
                    $this->info('Moving file from podcasts folder to images folder');
                }
            }
            $podcast->episodes->each(function ($episode) use ($podcast) {

                if (! is_null($episode->image_id)) {
                    // check the course folder
                    $oldCourseFilePath =
                        'podcasts-files/courseID_'.$podcast->old_podcast_id.'/'.$episode->cover->file_name;
                    if (Storage::exists($oldCourseFilePath)) {
                        Storage::move($oldCourseFilePath, 'images/'.$episode->cover->file_name);
                        $this->info('Moving file from podcasts episode folder to images folder');
                    }
                    $oldClipPath = 'podcasts-files/clipID_'.$episode->old_episode_id.'/'.$episode->cover->file_name;
                    if (Storage::exists($oldClipPath)) {
                        Storage::move($oldClipPath, 'images/'.$episode->cover->file_name);
                        $this->info('Moving file from podcasts episode folder to images folder');
                    }
                }
            });
        });
    }
}
