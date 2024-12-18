<?php

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

uses()->group('unit');

test('to array', function () {
    $role = Role::all()->first();

    expect(array_keys($role->toArray()))->toBe(['id', 'name', 'created_at', 'updated_at']);
});

it('has many users', function () {
    $role = Role::where('name', 'admin')->first();

    expect($role->users())->toBeInstanceOf(BelongsToMany::class);
});
