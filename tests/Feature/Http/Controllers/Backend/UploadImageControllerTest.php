<?php

uses()->group('backend');

use App\Enums\Role;
use App\Models\Image;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\post;

beforeEach(function () {
    Storage::fake('local');
    // create an image in the database with an existing image name
    $this->image = Image::factory()->create(['file_name' => 'image.jpg']);
    $oldFile = UploadedFile::fake()->image('image.jpg', 1200, 1200);
    $file = UploadedFile::fake()->image('avatar.jpg', 1200, 1200);
    $randomString = Str::random(10); // Use Laravel's Str helper
    $this->filePath = "/tmp/{$randomString}/avatar.jpg";
    $this->oldFilePath = '/images/image.jpg';

    // create two test images in the disks
    Storage::disk('local')->put($this->filePath, $file->getContent());
    Storage::disk('local')->put($this->oldFilePath, $oldFile->getContent());

});
test('upload image route is forbidden for visitors', function () {
    post(route('images.import'))->assertRedirectToRoute('login');
});

test('upload image route is forbidden for logged-in users', function () {
    signInRole(Role::STUDENT);
    post(route('images.import'))->assertForbidden();
});

test('upload image route is allowed for logged-in moderator ', function () {
    signInRole(Role::MODERATOR);

    post(route('images.import')."?{$this->image->id}", ['image' => $this->filePath])
        ->assertRedirect();
});

test('image upload succeeds', function () {
    $this->signInRole(Role::MODERATOR);
    $oldFileSize = $this->image->file_size;

    post(route('images.import')."?{$this->image->id}", ['image' => $this->filePath])
        ->assertRedirectToRoute('images.edit', $this->image);

    expect($this->image->refresh()->file_size)->not()->toBe($oldFileSize);
});
