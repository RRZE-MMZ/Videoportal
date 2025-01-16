<?php

use App\Enums\Acl;
use App\Enums\Role;
use App\Models\Asset;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\FileFactory;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;
use function Pest\Laravel\post;

uses()->group('backend');

beforeEach(function () {
    Storage::fake('assetsSymLinks');
    Storage::fake('local');
    Storage::fake('thumbnails');
    Storage::fake('videos');
});

it('outputs audio and video assets count', function () {
    $clip = ClipFactory::withAssets(3)->create();
    $clip->addAsset(Asset::factory()->create(['type' => 5]));

    artisan('app:update-assets-symbolic-links')->expectsOutputToContain('Processing 3 Audio/Video assets');
});

it('creates a symbolic link if a clip is open', function () {
    $clip = ClipFactory::ownedBy($this->signInRole(Role::ADMIN))->create();
    post(route('admin.clips.asset.transferSingle', $clip), ['asset' => FileFactory::videoFile()]);
    $clip->addAcls(collect(Acl::PUBLIC()));
    artisan('app:update-assets-symbolic-links');

    Storage::disk('assetsSymLinks')
        ->assertExists($clip->assets()->first()->guid.'.'.getFileExtension($clip->assets()->first()));
});

it('deletes a symbolic link if a clip changes from open to protected', function () {
    $clip = ClipFactory::ownedBy($this->signInRole(Role::ADMIN))->create();
    post(route('admin.clips.asset.transferSingle', $clip), ['asset' => FileFactory::videoFile()]);
    $clip->addAcls(collect(Acl::PUBLIC()));
    artisan('app:update-assets-symbolic-links');
    $asset = $clip->assets()->first();

    Storage::disk('assetsSymLinks')->assertExists($asset->guid.'.'.getFileExtension($asset));

    $clip->addAcls(collect([Acl::PORTAL(), Acl::PASSWORD()]));
    $this->artisan('app:update-assets-symbolic-links');

    Storage::disk('assetsSymLinks')->assertMissing($asset->guid.'.'.getFileExtension($asset));
});

it('does not create a symbolic link if a clip is protected', function () {
    $clip = ClipFactory::ownedBy($this->signInRole(Role::ADMIN))->create();
    post(route('admin.clips.asset.transferSingle', $clip), ['asset' => FileFactory::videoFile()]);
    $clip->addAcls(collect(Acl::PORTAL()));
    $asset = $clip->assets()->first();

    artisan('app:update-assets-symbolic-links')
        ->expectsOutputToContain("Clip:{$asset->clips()->first()->title} is protected. Moving to the next one");
    Storage::disk('assetsSymLinks')->assertMissing($asset->guid.'.'.getFileExtension($asset));
});
