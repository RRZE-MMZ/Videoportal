<?php

use App\Services\OpenSearchService;
use Tests\Setup\WorksWithOpenSearchClient;

use function Pest\Laravel\artisan;

uses(WorksWithOpenSearchClient::class);

beforeEach(function () {
    $this->mockHandler = $this->swapOpenSearchGuzzleClient();
    $this->openSearchService = app(OpenSearchService::class);
});

it('deletes all OpenSearch indexes for a certain model', function () {
    $this->mockHandler->append($this->mockClusterHealthResponse());
    artisan('opensearch:delete-indexes series')
        ->expectsOutputToContain('series Indexes deleted successfully');
});
