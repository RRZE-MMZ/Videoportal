<?php

namespace App\Providers;

use App\Http\Clients\OpencastClient;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class OpencastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(OpencastClient::class, function () {
            $setting = Setting::firstOrCreate(
                ['name' => 'opencast'],
                [
                    'data' => config('settings.opencast'),
                ]
            );
            $settingData = $setting->data;

            return new OpencastClient([
                'base_uri' => $settingData['url'],
                'verify' => config('app.env') === 'production',
                'auth' => [
                    $settingData['username'],
                    $settingData['password'],
                ],
                'timeout' => '10',
                'connect_timeout' => '10',
            ]);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
