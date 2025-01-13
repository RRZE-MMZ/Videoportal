<?php

namespace App\Http\Controllers\Backend\Traits;

use App\Jobs\CreateWowzaSmilFile;
use App\Jobs\TransferAssetsJob;
use App\Mail\AssetsTransferred;
use App\Services\OpencastService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

trait Transferable
{
    public function checkOpencastAssetsForUpload(Model $model, $eventID, OpencastService $opencastService)
    {
        $opencastAssets = $opencastService->getAssetsByEventID($eventID);
        $sourceDisk = 'opencast_archive';

        $deliveryAssets = $opencastAssets->filter(function ($value) {
            return Str::contains($value['tag'], 'final');
        });

        $this->uploadAssets($model, $deliveryAssets, $eventID, $sourceDisk);
    }

    public function checkDropzoneFilesForUpload(Model $model, array $validatedFiles)
    {
        $sourceDisk = 'video_dropzone';
        $assets = fetchDropZoneFiles()->filter(function ($file, $key) use ($validatedFiles) {
            if (in_array($key, $validatedFiles['files'])) {
                return $file;
            }
        });

        $this->uploadAssets($model, $assets, '', $sourceDisk);
    }

    public function checkFilePondFilesForUpload(Model $model, array $validatedFiles)
    {
        $this->uploadAssets(model: $model, assets: collect($validatedFiles), sourceDisk: 'local');
    }

    private function uploadAssets(Model $model, Collection $assets, string $eventID = '', string $sourceDisk = '')
    {
        Bus::chain([
            new TransferAssetsJob($model, $assets, $eventID, $sourceDisk),
            new CreateWowzaSmilFile($model),
        ])->dispatch();

        // mail can be chained inside anonymous function bus parameter but then the test  fails
        Mail::to($model->owner->email)->queue(new AssetsTransferred($model));
    }
}
