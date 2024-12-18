<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\WithFaker;

uses(WithFaker::class);
uses()->group('unit');

test('to array', function () {
    $setting = Setting::factory()->create(['name' => $this->faker()->userName]);

    expect(array_keys($setting->toArray()))->toBe(['name', 'data', 'updated_at', 'created_at', 'id']);
});

it('has an opencast scope', function () {
    expect(Setting::opencast())->toBeInstanceOf(Setting::class);
});

it('has a portal scope', function () {
    expect(Setting::portal())->toBeInstanceOf(Setting::class);
});

it('has a streaming scope', function () {
    expect(Setting::streaming())->toBeInstanceOf(Setting::class);
});

it('has a user scope', function () {
    expect(Setting::user(User::factory()->create()))->toBeInstanceOf(Builder::class);
});

it('has an Opensearch scope', function () {
    expect(Setting::openSearch())->toBeInstanceOf(Setting::class);
});
