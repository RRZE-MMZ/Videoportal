<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\artisan;

beforeEach(function () {
    $this->settingsType = [
        0 => 'all',
        1 => 'openSearch',
        2 => 'opencast',
        3 => 'portal',
        4 => 'streaming',
    ];
});

it('allows user to select a certain setting or all', function () {
    $this->artisan('app:check-and-create-settings')
        ->expectsChoice('First select the setting you want to update:', 'all', $this->settingsType)
        ->expectsOutputToContain('Start migrating all settings');
});

it('checks for not existing portal settings', function () {
    artisan('app:check-and-create-settings')
        ->expectsChoice('First select the setting you want to update:', 'portal', $this->settingsType)
        ->expectsOutputToContain('Starting with portal settings');
    Config::set('settings.portal.second_one', 'this is the second one');
    expect(count(Setting::portal()->data))->not()->toBe(count(config('settings.portal')));
    artisan('app:check-and-create-settings')
        ->expectsChoice('First select the setting you want to update:', 'portal', $this->settingsType);
    expect(count(Setting::portal()->data))->toBe(count(config('settings.portal')));
});

it('checks for not existing opencast settings', function () {
    artisan('app:check-and-create-settings')
        ->expectsChoice('First select the setting you want to update:', 'opencast', $this->settingsType)
        ->expectsOutputToContain('Starting with opencast settings');
    Config::set('settings.opencast.second_one', 'this is the second one');
    expect(count(Setting::opencast()->data))->not()->toBe(count(config('settings.opencast')));
});
