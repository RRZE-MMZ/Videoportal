<?php

use App\Enums\Acl;
use App\Enums\Role;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\FileFactory;
use Facades\Tests\Setup\PodcastFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(WithFaker::class);
uses()->group('frontend');

beforeEach(function () {
    Storage::fake('videos');
    Storage::fake('local');
    $this->clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    post(route('admin.clips.asset.transferSingle', $this->clip), ['asset' => FileFactory::videoFile()]);
    $this->asset = $this->clip->assets()->first();
});

it('allows to a non-authorize user to download an asset from a public clip', function () {
    $this->clip->addAcls(collect([Acl::PUBLIC()]));
    get(route('assets.download', $this->asset))->assertDownload($this->asset->original_name);
    expect(response()->download(Storage::disk('videos')->path($this->asset->path)))
        ->toBeInstanceOf(BinaryFileResponse::class);
});

it('allows to a non-authorize user to download an asset from podcast episode', function () {
    $podcast = PodcastFactory::ownedBy(signInRole(Role::MODERATOR))->withEpisodes(1)->create();
    $episode = $podcast->episodes()->first();

    $audioFile = FileFactory::audioFile();
    $randomString = Str::random(10); // Use Laravel's Str helper
    $this->filePath = "/tmp/{$randomString}/Sample_Audio_file.mp3";
    // create two test images in the disks
    Storage::disk('local')->put($this->filePath, $audioFile->getContent());
    post(route('admin.podcasts.episode.transferPodcastAudioFile', compact('podcast', 'episode')), [
        'asset' => $this->filePath,
    ])
        ->assertStatus(302);

    logoutAllUsers();
    get(route('assets.download', $episode->assets()->first()))->assertForbidden();
});

it('allows to an authorized user to download an asset from protected clip', function () {
    $this->clip->addAcls(collect([Acl::PORTAL()]));
    get(route('assets.download', $this->asset))->assertDownload($this->asset->original_name);
    expect(response()->download(Storage::disk('videos')->path($this->asset->path)))
        ->toBeInstanceOf(BinaryFileResponse::class);
});

it('denies to a non-authorize user to download an asset from a clip with portal acl', function () {
    logoutAllUsers();
    $this->clip->addAcls(collect([Acl::PORTAL()]));

    get(route('assets.download', $this->asset))->assertForbidden();
});

it('denies to a non-authorize user to download an asset from a clip with lms acl', function () {
    logoutAllUsers();
    $this->clip->addAcls(collect([Acl::LMS()]));

    get(route('assets.download', $this->asset))->assertForbidden();
});

it('denies to a non-authorize user to download an asset from a clip with password acl', function () {
    logoutAllUsers();
    $this->clip->addAcls(collect([Acl::PASSWORD()]));

    get(route('assets.download', $this->asset))->assertForbidden();
});
