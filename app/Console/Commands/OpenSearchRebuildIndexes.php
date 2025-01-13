<?php

namespace App\Console\Commands;

use App\Services\OpenSearchService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

use function Laravel\Prompts\select;

class OpenSearchRebuildIndexes extends Command
{
    protected $signature = 'opensearch:rebuild-indexes';

    protected $description = 'Rebuild OpenSearch indexes';

    /**
     * @throws GuzzleException
     */
    public function handle(OpenSearchService $openSearchService): int
    {

        $modelName = select(
            label: 'Which search index do you want to rebuild?',
            options: ['Series', 'Clip', 'Podcast', 'PodcastEpisode'],
            default: 'Series',
            hint: 'Clips may take longer as expected'
        );

        $modelClass = "App\\Models\\{$modelName}";
        $modelResource = "App\\Http\\Resources\\{$modelName}Resource";

        $this->info('Staring rebuild of model '.$modelName);
        $this->newLine(2);
        if (! class_exists($modelClass)) {
            $this->error("Model doesn't exists");

            return Command::FAILURE;
        }

        if ($modelName === 'PodcastEpisode') {
            $openSearchService->deleteIndexes('podcast_episodes');
        } else {
            $openSearchService->deleteIndexes(Str::plural($modelName));
        }

        $this->info($modelName.' Indexes deleted successfully');
        $counter = $modelClass::count();
        $bar = $this->output->createProgressBar($counter);
        $bar->start();
        $modelClass::chunk(200, function (Collection $models) use ($openSearchService, $modelResource, $bar) {
            $models->each(function ($model) use ($openSearchService, $modelResource, $bar) {
                // create the necessary json resource
                if ($model) {
                    $openSearchService->createIndex(new $modelResource($model));
                }
                $bar->advance();
            });
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("{$modelClass::count()} ".Str::plural($modelName).' Indexes created successfully');

        return Command::SUCCESS;
    }
}
