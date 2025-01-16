<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Models\Clip;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckTimeAvailabilityClips extends Command
{
    use Logable;

    protected $signature = 'app:check-time-availability-clips';

    protected $description = 'Check and toggle time availability for clips';

    public function handle(): int
    {
        $now = Carbon::now();
        $clips = Clip::where('has_time_availability', true)->get();

        $this->commandLog(message: 'Starting command to check time availability for clips.');

        if ($clips->isEmpty()) {
            $this->commandLog(message: "No clips with time availability found as of {$now}");

            return Command::SUCCESS;
        }

        $this->commandLog(message: "Processing {$clips->count()} clips with time availability.");

        $bar = $this->output->createProgressBar($clips->count());
        $bar->start();

        $clips->each(function (Clip $clip) use ($bar, $now) {
            $this->processClip($clip, $now);
            $bar->advance();
            $this->newLine();
        });

        $bar->finish();
        $this->commandLog(message: 'Time availability check completed.');

        return Command::SUCCESS;
    }

    /**
     * Process each clip based on time availability rules.
     */
    private function processClip(Clip $clip, Carbon $now): void
    {
        if ($now->lessThan($clip->time_availability_start)) {
            $clip->is_public = false;
            $message = 'will remain offline until its start time.';
        } elseif ($now->between($clip->time_availability_start, $clip->time_availability_end ?? $now)) {
            if (is_null($clip->time_availability_end)) {
                $clip->has_time_availability = false;
            }
            $clip->is_public = true;
            $message = 'is now available.';
        } else {
            $clip->is_public = false;
            $clip->has_time_availability = false;
            $message = 'time availability has expired and it has been taken offline.';
        }

        $clip->save();
        $this->commandLog(message: "ClipID: {$clip->id} / Title: {$clip->episode} {$clip->title} {$message}");
    }
}
