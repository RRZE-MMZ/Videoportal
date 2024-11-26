<?php

namespace App\Console\Commands;

use App\Models\Clip;
use App\Services\WowzaService;
use DOMException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class InsertSmilAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smil:insert clip{--override : Override existing Smils}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert SMIL file paths to database,optionally overriding existing ones.';

    /**
     * Execute the console command.
     *
     *
     * @throws DOMException
     */
    public function handle(WowzaService $wowzaService): int
    {
        // Retrieve the 'override' option
        $override = $this->option('override');

        if ($override) {
            $this->info('Override option enabled. Existing SMILs will be replaced.');
        } else {
            $this->info('Override option not enabled. Existing SMILs will be skipped.');
        }
        Cache::put('insert_smil_command', true);
        $this->info('Counting clips...');
        $bar = $this->output->createProgressBar(Clip::count());

        $bar->start();

        Clip::orderBy('id', 'desc')->lazy()->each(function ($clip) use ($wowzaService, $override) {
            //do not generate smil files if a clip has already
            if ($override) {
                if ($clip->assets()
                    ->formatSmil()
                    ->count() > 0) {
                    return;
                }
            }
            $wowzaService->createSmilFile($clip);
            $this->info("Finish clip ID {$clip->id}");
            $this->newLine(2);
        });

        $bar->finish();

        $this->info('All smils generated!');

        Cache::forget('insert_smil_command');

        return Command::SUCCESS;
    }
}
