<?php

use App\Models\Organization;
use Illuminate\Support\Str;

it('generates organization slug', function () {
    $organization = Organization::find(1);

    $organization->slug = null;
    $organization->save();
    $organization->refresh();

    $this->artisan('organizations:slugs')->expectsOutputToContain('Finish organizations slugs');

    $organization->refresh();

    expect(Str::slug($organization->name))->toEqual($organization->slug);
});

it('generates a slug with an asc number if two organizations have the same name', function () {
    Organization::factory()->create();
    $this->artisan('organizations:slugs');

    $organizationA = Organization::find(1);
    $organizationB = Organization::find(2);

    expect($organizationA->name)->toBe($organizationB->name);
    expect($organizationA->slug)->not()->toBe($organizationB->slug);

});
