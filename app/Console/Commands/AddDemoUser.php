<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Enums\Content;
use App\Enums\Role;
use App\Models\Asset;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AddDemoUser extends Command
{
    use Logable;

    protected $signature = 'app:add-demo-user';

    protected $description = 'Add Dr. Dolitle to the portal';

    public function handle(): int
    {
        $this->commandLog(message: 'Adding Dr. Dolitle to users');
        $user = User::create([
            'username' => 'drdoli',
            'first_name' => 'John',
            'last_name' => 'Dolittle',
            'slug' => 'john-dolittle',
            'password' => Str::password(16, true, true, false, false),
            'email' => 'john.dolittle@tides.edu',
        ]);
        $user->assignRole(Role::MODERATOR);
        $this->commandLog(message: 'Adding Dr. Dolitle to users');
        $user->presenter()->create([
            'academic_degree_id' => '1',
            'first_name' => 'John',
            'last_name' => 'Dolittle',
            'username' => 'drdoli',
            'email' => 'john.dolittle@tides.edu',
        ]);
        $this->commandLog(message: 'Adding Dr. Dolittle series');
        $user->series()->create([
            'title' => 'The story of Dr. Dollitle',
            //            'semester_id' => Semester::current()->get()->first()->id,
        ]);

        $series = $user->series()->first();
        $this->commandLog(message: 'Adding Dr. Dolittle clips to his series');
        $series->clips()->create([
            'episode' => 1,
            'title' => 'Taby cat',
            'folder_id' => 'TIDES_CL_1',
            'recording_date' => Carbon::now()->format('d-m-Y H:i:s'),
            'semester_id' => Semester::current()->get()->first()->id,
            'has_video_assets' => 1,
            'language_id' => '1',
        ]);
        $series->clips()->create([
            'episode' => 2,
            'title' => 'Goat',
            'folder_id' => 'TIDES_CL_2',
            'recording_date' => Carbon::now()->format('d-m-Y H:i:s'),
            'semester_id' => Semester::current()->get()->first()->id,
            'has_video_assets' => 1,
            'language_id' => '1',
        ]);
        $this->commandLog(message: 'Adding Dr. Dolittle assets to his clips');

        $series->clips()->each(function ($clip) {
            if ($clip->episode == 1) {
                $clip->addAsset(Asset::create([
                    'original_file_name' => 'taby_cat.mp4',
                    'disk' => 'videos',
                    'path' => '2024/08/09/TIDES_CL_1/taby_cat.mp4',
                    'width' => '1280',
                    'height' => '720',
                    'duration' => '11',
                    'guid' => Str::uuid(),
                    'type' => Content::PRESENTER,
                ]));
            } else {
                $clip->addAsset(Asset::create([
                    'original_file_name' => 'goat.mp4',
                    'path' => '2024/08/09/TIDES_CL_2/goat.mp4',
                    'disk' => 'videos',
                    'width' => '1920',
                    'height' => '1080',
                    'duration' => '4',
                    'guid' => Str::uuid(),
                    'type' => Content::PRESENTER,
                ]));
            }

        });

        return Command::SUCCESS;
    }
}
