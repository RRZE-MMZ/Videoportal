<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Enums\Acl;
use App\Models\Asset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UpdateAssetSymbolicLinks extends Command
{
    use Logable;

    protected $signature = 'app:update-assets-symbolic-links';

    protected $description = 'Updates all symbolic links for open clips assets';

    public function handle(): int
    {
        $assets = Asset::formatVideo()
            ->orWhere->formatAudio();

        $bar = $this->output->createProgressBar($count = $assets->count());
        $bar->start();

        $this->commandLog(message: "Processing {$count} Audio/Video assets");

        $assets->each(function ($asset) use ($bar) {
            if (Storage::disk('assetsSymLinks')->exists("{$asset->guid}.".getFileExtension($asset))
                && (
                    ! $asset->clips()->first()->is_public
                    || $asset->clips()->first()->acls->pluck('id')->doesntContain(Acl::PUBLIC())
                )) {
                unlink(Storage::disk('assetsSymLinks')->path("{$asset->guid}.".getFileExtension($asset)));
                $this->commandLog(message: 'Clip Acl changed. Deleting symbolic link...');
                $this->newLine(2);
                $bar->advance();
            } elseif ($asset->clips()->first()->is_public
                && $asset->clips()->first()->acls->pluck('id')->contains(Acl::PUBLIC())) {
                symlink(
                    Storage::disk('videos')->path($asset->path),
                    Storage::disk('assetsSymLinks')->path("{$asset->guid}.".getFileExtension($asset))
                );
                $this->commandLog(message: "Symbolik link for clip:{$asset->clips()->first()->title} created successfully");
                $this->newLine(2);
                $bar->advance();
            } else {
                $this->commandLog(message: "Clip:{$asset->clips()->first()->title} is protected. Moving to the next one");
                $this->newLine(2);
                $bar->advance();
            }
        });
        $bar->finish();

        $this->commandLog(message: 'All links created');

        return Command::SUCCESS;
    }
}
