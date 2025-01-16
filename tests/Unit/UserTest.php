<?php

use App\Enums\Role;
use App\Models\Series;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

uses()->group('unit');

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('to array', function () {
    expect(array_keys($this->user->toArray()))->toBe([
        'first_name', 'last_name', 'username', 'email', 'email_verified_at', 'presenter_id', 'expired', 'logged_in_at',
        'last_visited_at', 'override_role', 'updated_at', 'created_at', 'id',
    ]);
});
it('has many series', function () {
    expect($this->user->series())->toBeInstanceOf(HasMany::class);
});

it('has many clips', function () {
    expect($this->user->clips())->toBeInstanceOf(HasMany::class);
});

it('has many channels', function () {
    expect($this->user->channels())->toBeInstanceOf(HasMany::class);
});

it('has many podcasts', function () {
    expect($this->user->podcasts())->toBeInstanceOf(HasMany::class);
});

it('has many podcast episodes', function () {
    expect($this->user->podcastEpisodes())->toBeInstanceOf(HasMany::class);
});

it('has many supervised clips', function () {
    expect($this->user->supervisedClips())->toBeInstanceOf(HasMany::class);
});

it('has many subscriptions', function () {
    expect($this->user->subscriptions())->toBeInstanceOf(BelongsToMany::class);
});

it('fetch all user series', function () {
    expect($this->user->getAllSeries())->toBeInstanceOf(Builder::class);
});

it('has many roles', function () {
    expect($this->user->roles())->toBeInstanceOf(BelongsToMany::class);
});

it('has many memberships', function () {
    expect($this->user->memberships())->toBeInstanceOf(BelongsToMany::class);
});

it('checks whether a user is member of a series', function () {
    expect($this->user->isMemberOf(Series::factory()->create()))->toBeFalse();
});

it('can assign a role', function () {
    $this->user->assignRole(Role::MEMBER);

    expect($this->user->hasRole(Role::USER))->toBeFalse();
    expect($this->user->assignRole(Role::ADMIN))->toBeInstanceOf(User::class);
    expect($this->user->roles()->first()->name)->toEqual('admin');
});

it('can assign multiple roles', function () {
    $this->user->assignRoles(collect([0 => Role::STUDENT(), 1 => Role::USER()]));
    expect($this->user->hasRole(Role::STUDENT))->toBeTrue();

    expect($this->user->assignRoles(collect([0 => Role::ADMIN()])))->toBeInstanceOf(User::class);
});

it('can check for a role', function () {
    $this->user->assignRole(Role::ADMIN);

    expect($this->user->hasRole(Role::ADMIN))->toBeTrue();
    expect($this->user->hasRole(Role::STUDENT))->toBeFalse();
});

it('check for superadmin role', function () {
    signInRole(Role::SUPERADMIN);

    expect(auth()->user()->isSuperAdmin())->toBeTrue();
});

it('check for admin role', function () {
    signInRole(Role::ADMIN);

    expect(auth()->user()->isAdmin())->toBeTrue();
});

it('check for moderator role', function () {
    signInRole(Role::MODERATOR);

    expect(auth()->user()->isModerator())->toBeTrue();
});

it('check for assistant role', function () {
    signInRole(Role::ASSISTANT);

    expect(auth()->user()->isAssistant())->toBeTrue();
});

it('has an admins scope', function () {
    expect(User::admins())->toBeInstanceOf(Builder::class);
});

it('has an expired users scope', function () {
    expect(User::expired())->toBeInstanceOf(Builder::class);
});

it('has a scope to search by role', function () {
    expect(User::byRole(Role::MODERATOR))->toBeInstanceOf(Builder::class);
});
