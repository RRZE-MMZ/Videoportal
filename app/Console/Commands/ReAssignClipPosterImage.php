<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Models\Clip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReAssignClipPosterImage extends Command
{
    use Logable;

    protected $signature = 'clip:posterImage';

    protected $description = 'Create player  poster image directories and update DB';

    public function handle(): int
    {
        $this->commandLog(message: 'Counting clips...');
        $bar = $this->output->createProgressBar(Clip::count());
        $bar->start();

        Clip::lazy()->each(function ($clip) use ($bar) {
            $asset = $clip->assets()->orderBy('width', 'desc')->limit(1)->get()->first();

            if ($asset) {
                $clip->posterImage = $asset->player_preview;
                $clip->saveQuietly();

                Log::info("CLIP POSTER for ID :{$clip->id} IS {$clip->posterImage}");
                $bar->advance();
            } else {
                $this->commandLog(message: "Assets not found for Clip ID {$clip->id}! Skipping...");
                $bar->advance();
                $this->newLine(2);
            }

            $this->commandLog(message: "Finish clip ID {$clip->id}");
            $this->newLine(2);
        });

        $bar->finish();

        $this->commandLog(message: 'All rows updated!');

        return Command::SUCCESS;
    }
}
