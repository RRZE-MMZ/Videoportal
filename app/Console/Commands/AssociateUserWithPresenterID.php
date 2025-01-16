<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Enums\Role;
use App\Models\Presenter;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AssociateUserWithPresenterID extends Command
{
    use Logable;

    protected $signature = 'app:link-user-with-presenter';

    protected $description = 'Links an existing user with an existing presenter';

    public function handle(): int
    {
        $this->commandLog(message: 'Start to iterating over employees');
        $moderators = User::byRole(Role::MEMBER);
        $bar = $this->output->createProgressBar($moderators->count());
        $this->commandLog(message: "Found {$moderators->count()} members");
        $bar->start();
        $moderators->get()->each(function ($moderator) use ($bar) {
            $presenter = Presenter::where(function ($query) use ($moderator) {
                $query->where('username', $moderator->username)
                    ->orWhereRaw('LOWER(email) = ?', [Str::lower($moderator->email)]);
            })->first();
            $this->commandLog(message: "Found a presenter for user:{$moderator->getFullNameAttribute()}");
            if (! is_null($presenter) && is_null($moderator->presenter_id)) {
                $this->newLine();
                $moderator->presenter_id = $presenter->id;
                $moderator->save();
                $this->commandLog(message: "Presenter ID is set for user:{$moderator->getFullNameAttribute()}");
            } else {
                $this->commandLog(message: 'User already has a presenter ID');
            }
            $this->newLine();
            $bar->advance();
        });
        $bar->finish();

        return Command::SUCCESS;
    }
}
