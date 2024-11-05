<?php

namespace App\Providers;

use App\Http\Clients\OpenSearchClient;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use OpenSearch\ClientBuilder;

class OpenSearchServiceProvider extends ServiceProvider
{
    private array $settingsData;

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ClientBuilder::class, function () {
            $this->settingsData = $this->getConfig();

            $builder = new ClientBuilder;

            $builder->setHosts([$this->settingsData['url'].':'.$this->settingsData['port']])
                ->setBasicAuthentication($this->settingsData['username'], $this->settingsData['password'])
                ->setSSLVerification(config('app.env') === 'production')
                ->build();

            return $builder;
        });

        $this->app->singleton(OpenSearchClient::class, function () {
            $this->settingsData = $this->getConfig();

            return new OpenSearchClient([
                'base_uri' => "{$this->settingsData['url']}:{$this->settingsData['port']}",
                'verify' => config('app.env') === 'production',
                'auth' => [
                    $this->settingsData['username'],
                    $this->settingsData['password'],
                ],
                'timeout' => '10',
                'connect_timeout' => '10',
            ]);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    private function getConfig()
    {
        $setting = Setting::firstOrCreate(
            ['name' => 'openSearch'],
            ['data' => config('settings.openSearch')]
        );

        return $setting->data;
    }
}
