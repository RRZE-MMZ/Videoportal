<?php

use App\Enums\Role;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Storage::fake('local');
    $this->moderatorWithChannel = User::factory()->create();
    $this->moderatorWithChannel->assignRole(Role::MODERATOR);
    $this->moderatorChannel = Channel::create([
        'url_handle' => '@'.Str::before($this->moderatorWithChannel->email, '@'),
        'name' => $this->moderatorWithChannel->getFullNameAttribute(),
        'description' => 'this is a test channel',
        'owner_id' => $this->moderatorWithChannel->id,
        'banner_url' => null,
    ]);
});

it('not allowing to userA to upload a banner image to userB channel', function () {
    signInRole(Role::MODERATOR);

    post(route('channels.uploadBannerImage', $this->moderatorChannel), [
        'image' => UploadedFile::fake()->image('new_banner.png'),
    ])->assertForbidden();
});

it('validates files for images', function () {
    signIn($this->moderatorWithChannel);

    post(route('channels.uploadBannerImage', $this->moderatorChannel), [
        'image' => UploadedFile::fake()->create('document.pdf', 100),
    ])->assertSessionHasErrors('image');
});

it('uploads a new banner image to user\'s channel', function () {
    signIn($this->moderatorWithChannel);

    expect($this->moderatorChannel->banner_url)->toBeNull();

    // first upload file to filepond tmp directory
    $initialResponse = postJson(route('uploads.process'), [
        'image' => UploadedFile::fake()->image('test.jpg'),
    ]);
    $initialResponse->assertOk();
    $bannerPath = $initialResponse->content();
    // Simulate form submission with the file identifier
    post(route('channels.uploadBannerImage', $this->moderatorChannel), ['image' => $bannerPath])
        ->assertRedirect()
        ->assertSessionDoesntHaveErrors();

    $this->moderatorChannel->refresh();
    expect($this->moderatorChannel->banner_url)->not()->toBeNull();
    Storage::disk('local')->assertExists($this->moderatorChannel->banner_url);
});

it('deletes the old banner from images folder', function () {
    signIn($this->moderatorWithChannel);

    // first upload file to filepond tmp directory
    $initialResponse = postJson(route('uploads.process'), [
        'image' => UploadedFile::fake()->image('test.jpg'),
    ]);
    $bannerPath = $initialResponse->content();
    $fistBanner = $this->moderatorChannel->banner_url;
    // Simulate form submission with the file identifier
    post(route('channels.uploadBannerImage', $this->moderatorChannel), ['image' => $bannerPath]);
    $this->moderatorChannel->refresh();
    Storage::disk('local')->assertExists($this->moderatorChannel->banner_url);

    // Upload another file to filepond tmp directory
    $initialResponse2 = postJson(route('uploads.process'), [
        'image' => UploadedFile::fake()->image('test2.jpg'),
    ]);
    $bannerPath2 = $initialResponse2->content();
    post(route('channels.uploadBannerImage', $this->moderatorChannel), ['image' => $bannerPath2]);
    $this->moderatorChannel->refresh();
    Storage::disk('local')->assertMissing($fistBanner);
});
