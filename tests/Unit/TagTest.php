<?php

use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

uses()->group('unit');

test('to array', function () {
    $tag = Tag::factory()->create()->fresh();

    expect(array_keys($tag->toArray()))->toBe(['id', 'name', 'created_at', 'updated_at']);
});

it('has many clips', function () {
    $tag = Tag::factory()->create();

    expect($tag->clips())->toBeInstanceOf(BelongsToMany::class);
});
