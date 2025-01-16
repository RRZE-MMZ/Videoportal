<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateOrganizationSlug extends Command
{
    use Logable;

    protected $signature = 'organizations:slugs';

    protected $description = 'Generate the slugs for all organizations if slug is null';

    public function handle(): int
    {
        $organizations = Organization::all();

        $organizations->each(function ($organization) {
            if (is_null($organization->slug)) {
                $slug = Str::of($organization->name)->slug('-');
                if ($counter = Organization::whereRaw('slug like (?)', ["{$slug}%"])->count()) {
                    $slug = $slug.'-'.$counter + 1;
                }
                $organization->slug = $slug;
                $organization->save();
            }
        });

        $this->commandLog(message: 'Finish organizations slugs');

        return Command::SUCCESS;
    }
}
