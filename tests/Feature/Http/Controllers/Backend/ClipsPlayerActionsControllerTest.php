<?php

use App\Enums\Role;
use App\Models\Clip;
use Facades\Tests\Setup\ClipFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

uses()->group('backend');

beforeEach(function () {
    Storage::fake('videos');
    Storage::fake('thumbnails');
    $this->clip = ClipFactory::withAssets(3)->ownedBy(signInRole(Role::MODERATOR))->create();
});

it('hides preview image actions for a clip without assets', function () {
    $clipWithoutAssets = Clip::factory()->create(['owner_id' => auth()->user()->id]);

    get(route('clips.edit', $clipWithoutAssets))->assertDontSee('Preview Image')
        ->assertDontSee(route('clips.generatePreviewImageFromFrame', $clipWithoutAssets));
});

it('displays preview image actions for a clip with assets', function () {
    get(route('clips.edit', $this->clip))->assertSee(__('player.backend.preview image'))
        ->assertSee(route('clips.generatePreviewImageFromFrame', $this->clip));
});

it('denies access to unprivileged user to update clip preview image with a frame number', function () {
    $clip = ClipFactory::withAssets(3)->create();
    signInRole(Role::MODERATOR);

    post(route('clips.generatePreviewImageFromFrame', $clip), ['recentFrame' => '2'])->assertForbidden();
});

it('denies access to unprivileged user to update clip preview image with another image', function () {
    $clip = ClipFactory::withAssets(3)->create();
    signInRole(Role::MODERATOR);

    post(route('clips.generatePreviewImageFromUser', $clip), [
        'image' => UploadedFile::fake()->image('new_banner.png'),
    ])->assertForbidden();
});

it('validates the clip preview image timestamp for a clip', function () {
    post(route('clips.generatePreviewImageFromFrame', $this->clip), [])->assertSessionHasErrors('recentFrame');
});

it('validates the clip preview image new image for a clip', function () {
    post(route('clips.generatePreviewImageFromUser', $this->clip), [])->assertSessionHasErrors('image');
});

it('changes the preview image for a clip based on the user selected video frame timestamp', function () {
    $storagePath = $this->clip->folder_id;
    Storage::disk('videos')
        ->putFileAs(path: $storagePath, file: storage_path().'/tests/Big_Buck_Bunny.mp4', name: 'video.mp4');
    Storage::disk('thumbnails')
        ->putFileAs(path: 'previews-ng', file: UploadedFile::fake()->image('new_banner.png'), name: '1_preview.png');

    post(route('clips.generatePreviewImageFromFrame', $this->clip), ['recentFrame' => '3'])
        ->assertRedirectToRoute('clips.edit', $this->clip);

    // expect that the old preview will be deleted from the storage
    Storage::disk('thumbnails')->assertMissing('previews-ng/1_preview.png');
    // finally expect that the database value for the asset will be updated
    expect($this->clip->assets()->first()->player_review)->not()->toBe('1_preview.png');
});

it('changes the preview image for a clip based on a user uploads image', function () {
    $storagePath = $this->clip->folder_id;
    Storage::disk('videos')
        ->putFileAs(path: $storagePath, file: storage_path().'/tests/Big_Buck_Bunny.mp4', name: 'video.mp4');
    Storage::disk('thumbnails')
        ->putFileAs(path: 'previews-ng', file: UploadedFile::fake()->image('new_banner.png'), name: '1_preview.png');
    // first upload file to filepond tmp directory
    $initialResponse = postJson(route('uploads.process'), [
        'image' => UploadedFile::fake()->image('new_banner.png'),
    ]);
    $initialResponse->assertOk();
    $newPreviewImagePath = $initialResponse->content();

    post(route('clips.generatePreviewImageFromUser', $this->clip), ['image' => $newPreviewImagePath])
        ->assertRedirectToRoute('clips.edit', $this->clip)
        ->assertSessionDoesntHaveErrors();
    // expect that the old preview will be deleted from the storage
    Storage::disk('thumbnails')->assertMissing('previews-ng/1_preview.png');
    // finally expect that the database value for the asset will be updated
    expect($this->clip->assets()->first()->player_review)->not()->toBe('1_preview.png');
});
