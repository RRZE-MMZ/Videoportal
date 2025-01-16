<?php

use App\Models\Clip;
use App\Models\Series;
use App\Services\OpenSearchService;
use Tests\Setup\WorksWithOpenSearchClient;

uses(WorksWithOpenSearchClient::class);

it('throws an error for rebuilding if model does not exists', function () {
    $this->artisan('opensearch:rebuild-indexes')
        ->expectsQuestion('Which search index do you want to rebuild?', 'Ser')
        ->expectsOutputToContain("Model doesn't exists");
});

it('shows a counter of models that are rebuild', function () {
    $series = Series::factory(10)->create();
    $clips = Clip::factory(10)->create();
    $this->mockSingleDocument();
    app(OpenSearchService::class);

    $this->artisan('opensearch:rebuild-indexes')
        ->expectsQuestion('Which search index do you want to rebuild?', 'Series')
        ->expectsOutputToContain('Series Indexes deleted successfully')
        ->expectsOutputToContain("{$series->count()} Series Indexes created successfully");

    $this->artisan('opensearch:rebuild-indexes')
        ->expectsQuestion('Which search index do you want to rebuild?', 'Clip')
        ->expectsOutputToContain('Clip Indexes deleted successfully')
        ->expectsOutputToContain("{$clips->count()} Clips Indexes created successfully");
});
