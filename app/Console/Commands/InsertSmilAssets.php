<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Models\Clip;
use App\Services\WowzaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class InsertSmilAssets extends Command
{
    use Logable;

    protected $signature = 'smil:insert clip{--override : Override existing Smils}';

    protected $description = 'Insert SMIL file paths to database,optionally overriding existing ones.';

    public function handle(WowzaService $wowzaService): int
    {
        // Retrieve the 'override' option
        $override = $this->option('override');

        if ($override) {
            $this->commandLog(message: 'Override option enabled. Existing SMILs will be replaced.');
        } else {
            $this->commandLog(message: 'Override option not enabled. Existing SMILs will be skipped.');
        }
        Cache::put('insert_smil_command', true);
        $this->commandLog(message: 'Counting clips...');
        $bar = $this->output->createProgressBar(Clip::count());

        $bar->start();

        Clip::orderBy('id', 'desc')->lazy()->each(function ($clip) use ($wowzaService, $override) {
            // do not generate smil files if a clip has already
            if ($override) {
                if ($clip->assets()
                    ->formatSmil()
                    ->count() > 0) {
                    return;
                }
            }
            $wowzaService->createSmilFile($clip);
            $this->commandLog(message: "Finish clip ID {$clip->id}");
            $this->newLine(2);
        });

        $bar->finish();

        $this->commandLog(message: 'All smils generated!');

        Cache::forget('insert_smil_command');

        return Command::SUCCESS;
    }
}
