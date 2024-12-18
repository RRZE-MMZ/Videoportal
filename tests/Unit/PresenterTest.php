<?php

use App\Models\Presenter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

uses()->group('unit');

beforeEach(function () {
    $this->presenter = Presenter::factory()->create();
});

test('to array', function () {
    expect(array_keys($this->presenter->toArray()))->toBe([
        'academic_degree_id', 'first_name', 'last_name', 'slug', 'username', 'email', 'image_id', 'updated_at',
        'created_at', 'id', 'academic_degree',
    ]);
});

it('belongs to many series', function () {
    expect($this->presenter->series())->toBeInstanceOf(BelongsToMany::class);
});

it('belongs to many clips', function () {
    expect($this->presenter->clips())->toBeInstanceOf(BelongsToMany::class);
});

it('has one or none degree title', function () {
    expect($this->presenter->academicDegree())->toBeInstanceOf(BelongsTo::class);
});

it('can return presenter full name', function () {
    expect($this->presenter->academicDegree->title.' '.$this->presenter->first_name.
    ' '.$this->presenter->last_name)->toEqual($this->presenter->getFullNameAttribute());
});

it('return presenters clips without series', function () {
    expect($this->presenter->clips)->toBeInstanceOf(Collection::class);
});
