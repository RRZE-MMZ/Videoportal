<?php

namespace App\Jobs;

use App\Enums\Content;
use App\Models\Asset;
use App\Models\Clip;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileExistsException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class TransferAssetsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Model $model,
        protected Collection $files,
        protected string $eventID = '',
        protected string $sourceDisk = ''
    ) {}

    /**
     * Copy a collection of video files to clip path
     *
     *
     * @throws FileExistsException
     */
    public function handle(): void
    {
        $settingData = Setting::opencast()->data;

        $clipStoragePath = getClipStoragePath($this->model);
        $this->files->each(function ($file, $key) use ($clipStoragePath, $settingData) {
            $isVideo = (bool) $file['video'];
            // if it is a filePond file read the file from local /tmp dir
            if (isset($file['filePond'])) {
                $storageDisk = Storage::readStream($file['path']);
            } else {
                $storageDisk = ($this->eventID !== '')
                    ? Storage::disk($this->sourceDisk)->readStream('/'.$settingData['archive_path'].
                        "/$this->eventID/{$file['version']}/{$file['name']}")
                    : Storage::disk($this->sourceDisk)->readStream($file['name']);
            }
            try {
                Storage::disk('videos')->makeDirectory($clipStoragePath);
                Storage::disk('videos')->writeStream("{$clipStoragePath}/{$file['name']}", $storageDisk);
            } catch (FileNotFoundException $e) {
                Log::error($e);
            }

            $storedFile = "{$clipStoragePath}/{$file['name']}";
            $ffmpeg = FFMpeg::fromDisk('videos')->open($storedFile);

            $asset = Asset::create([
                'disk' => 'videos',
                'original_file_name' => $file['name'],
                'path' => $storedFile,
                'guid' => (string) Str::uuid(),
                'duration' => $ffmpeg->getDurationInSeconds(),
                'width' => ($isVideo)
                    ? $ffmpeg->getVideoStream()->getDimensions()->getWidth()
                    : 0,
                'height' => ($isVideo)
                    ? $ffmpeg->getVideoStream()->getDimensions()->getHeight()
                    : 0,
                'type' => ($isVideo) ? Content::PRESENTER() : Content::AUDIO(),
            ]);

            $this->model->addAsset($asset);

            if ($isVideo) {

                // generate a poster image for the clip
                $ffmpeg->getFrameFromSeconds(5)
                    ->export()
                    ->toDisk('thumbnails')
                    ->save("{$this->model->id}_poster.png");
                $this->model->updatePosterImage();

                Storage::disk('thumbnails')->delete("{$this->model->id}_poster.png");
            }

            // in case of local upload delete the tmp file
            if ($this->sourceDisk == 'local') {
                Storage::disk($this->sourceDisk)->delete($file['name']);
            }
        });
    }
}
