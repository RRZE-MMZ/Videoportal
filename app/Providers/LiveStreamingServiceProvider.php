<?php

namespace App\Providers;

use App\Http\Clients\LiveStreamingClient;
use App\Http\Clients\StreamingClient;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class LiveStreamingServiceProvider extends ServiceProvider
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

            return new LiveStreamingClient([
                'base_uri' => $settingsData['wowza']['server2']['api_url'],
                'verify' => config('app.env') === 'production',
                'auth' => [
                    $settingsData['wowza']['server2']['api_username'],
                    $settingsData['wowza']['server2']['api_password'],
                    $authType,
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
