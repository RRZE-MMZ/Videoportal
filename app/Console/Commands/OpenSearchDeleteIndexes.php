<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Services\OpenSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class OpenSearchDeleteIndexes extends Command
{
    use Logable;

    protected $signature = 'opensearch:delete-indexes {model : The model index}';

    protected $description = 'Delete indexes for a given model';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(OpenSearchService $openSearchService): int
    {
        $modelName = Str::singular($this->argument('model'));

        $openSearchService->deleteIndexes(Str::plural($this->argument('model')));

        $this->commandLog(message: "{$modelName} Indexes deleted successfully");

        return Command::SUCCESS;
    }
}
