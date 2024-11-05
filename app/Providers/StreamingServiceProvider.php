<?php

namespace App\Providers;

use App\Http\Clients\StreamingClient;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class StreamingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StreamingClient::class, function () {
            $setting = Setting::streaming();

            $settingsData = $setting->data;

            $authType = config('app.env') === 'local' ? 'basic' : 'digest';

            return new StreamingClient([
                'base_uri' => $settingsData['wowza']['server1']['api_url'],
                'verify' => config('app.env') === 'production',
                'auth' => [
                    $settingsData['wowza']['server1']['api_username'],
                    $settingsData['wowza']['server1']['api_password'],
                    $authType,
                ],
                'timeout' => '5',
                'connect_timeout' => '5',
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
