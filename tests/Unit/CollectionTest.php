<?php

use App\Models\Clip;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

uses()->group('unit');

beforeEach(function () {
    $this->collection = Collection::factory()->create();
});

test('to array', function () {
    expect(array_keys($this->collection->toArray()))->toBe([
        'title', 'description', 'position', 'is_public', 'updated_at', 'created_at', 'id',
    ]);
});
it('has many clips', function () {
    expect($this->collection->clips())->toBeInstanceOf(BelongsToMany::class);
});

it('can toggle clips', function () {
    Clip::factory(2)->create();
    $this->collection->toggleClips(collect(['1', '2']));

    expect($this->collection->clips()->count())->toEqual(2);

    $this->collection->toggleClips(collect(['1', '2']));
    expect($this->collection->clips()->count())->toEqual(0);
});
