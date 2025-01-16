<?php

use App\Models\Image;
use Facades\Tests\Setup\FileFactory;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

uses()->group('commands');

it('has a scan and update images command', function () {
    artisan('images:update-file-size')->expectsOutputToContain('Starting to update images file size in database');
});

it('updated the file size value on database', function () {
    $disk = Storage::fake('images');
    $disk->putFileAs('', FileFactory::imageFile(), 'avatar.png');

    $image = Image::factory()->create([
        'description' => 'Image of an avatar',
        'file_name' => 'avatar.png',
        'file_path' => '/data/Thumbnails/avatar.jpg.png',
        'thumbnail_path' => 'avatar.png',
        'mime_type' => null,
        'file_size' => null,
        'created_at' => '2023-03-02T12:20:01.000000Z',
        'updated_at' => '2023-03-02T12:20:01.000000Z',
    ]);

    artisan('images:update-file-size');

    $image->refresh();

    expect($image->file_size)->toBe('78040');
});
