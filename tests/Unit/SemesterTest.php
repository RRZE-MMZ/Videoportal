<?php

use App\Models\Semester;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

uses()->group('unit');

test('to array', function () {
    $semester = Semester::factory()->create()->fresh();

    expect(array_keys($semester->toArray()))->toBe([
        'id', 'name', 'acronym', 'short_title', 'start_date', 'stop_date', 'created_at', 'updated_at',
    ]);
});

it('has many clips', function () {
    expect(Semester::find(1)->clips())->toBeInstanceOf(HasMany::class);
});

it('has a current semester scope', function () {
    expect(Semester::current())->toBeInstanceOf(Builder::class);
});
