<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Enums\Content;
use App\Models\Clip;
use App\Services\OpencastService;
use Illuminate\Console\Command;

class OpencastMultipleIngest extends Command
{
    use Logable;

    protected $signature = 'opencast:multiple-ingest';

    protected $description = 'Multiple ingest opencast files';

    public function handle(OpencastService $opencastService): int
    {
        $this->commandLog(message: 'Starting to ingest multiple opencast files');

        $clip = Clip::find(39198);

        $videoFile = $clip->getAssetsByType(Content::PRESENTER)->get()->first();
        $captionsFile = $clip->getCaptionAsset();
        $opencastMediaPackage = $opencastService->createMediaPackage();
        $opencastMediaPackage = $opencastService->addCatalog($opencastMediaPackage, $clip);
        $opencastMediaPackage = $opencastService->addTrack(
            $opencastMediaPackage,
            'presenter/source',
            $videoFile->path
        );
        $opencastMediaPackage = $opencastService->addTrack(
            $opencastMediaPackage,
            'captions/source+'.$clip->language->code,
            $captionsFile->path
        );
        $opencastService->ingest($opencastMediaPackage, 'edit-subs');
    }
}
