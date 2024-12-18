<?php

use App\Models\Image;
use Illuminate\Database\Eloquent\Relations\HasMany;

uses()->group('unit');

beforeEach(function () {
    $this->image = Image::factory()->create();
});

test('to array', function () {
    expect(array_keys($this->image->toArray()))->toBe([
        'description', 'file_name', 'file_path', 'thumbnail_path', 'mime_type', 'file_size', 'updated_at', 'created_at',
        'id',
    ]);
});

it('has many series', function () {
    expect($this->image->series())->toBeInstanceOf(HasMany::class);
});

it('has many clips', function () {
    expect($this->image->clips())->toBeInstanceOf(HasMany::class);
});

it('has many presenters', function () {
    expect($this->image->presenters())->toBeInstanceOf(HasMany::class);
});
