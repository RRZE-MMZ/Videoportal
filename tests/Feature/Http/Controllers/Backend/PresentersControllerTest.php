<?php

use App\Enums\Role;
use App\Livewire\PresenterDataTable;
use App\Models\Clip;
use App\Models\Presenter;
use App\Models\Series;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses()->group('backend');
uses(WithFaker::class);

beforeEach(function () {
    signInRole(Role::ADMIN);
});

it('denies access to manage presenters for user with moderator role', function () {
    auth()->logout();
    signInRole(Role::MODERATOR);

    $presenter = Presenter::factory()->create();

    get(route('presenters.index'))->assertForbidden();
    get(route('presenters.create'))->assertForbidden();
    post(route('presenters.store'), [])->assertForbidden();
    get(route('presenters.edit', $presenter))->assertForbidden();
    delete(route('presenters.destroy', $presenter))->assertForbidden();
});

it('allows access to manage presenters for user with assistant role', function () {
    auth()->logout();
    signInRole(Role::ASSISTANT);

    $presenter = Presenter::factory()->create();

    get(route('presenters.index'))->assertOk();
    get(route('presenters.create'))->assertOk();
    post(route('presenters.store'), [])->assertRedirect(route('presenters.create'));
    delete(route('presenters.destroy', $presenter))->assertRedirect(route('presenters.index'));
});

it('allows access to manage presenters for user with admin role', function () {

    $presenter = Presenter::factory()->create();

    get(route('presenters.index'))->assertOk();
    get(route('presenters.create'))->assertOk();
    post(route('presenters.store'), [])->assertRedirect(route('presenters.create'));
    get(route('presenters.edit', $presenter))->assertOk();
    delete(route('presenters.destroy', $presenter))->assertRedirect(route('presenters.index'));
});

it('renders presenters datatable component for an assistant', function () {
    auth()->logout();
    signInRole(Role::ASSISTANT);

    get(route('presenters.index'))->assertSeeLivewire(PresenterDataTable::class);
});

it('renders presenters datatable component for an admin', function () {
    auth()->logout();
    signInRole(Role::ADMIN);

    get(route('presenters.index'))->assertSeeLivewire(PresenterDataTable::class);
});

it('can search for a presenter name in presenters index datatable', function () {
    $bob = Presenter::factory()->create(['first_name' => 'Bob', 'last_name' => 'Tester']);
    $alice = Presenter::factory()->create(['first_name' => 'Alice', 'last_name' => 'Tester']);

    Livewire::test(PresenterDataTable::class)
        ->set('search', 'bob')
        ->assertSee($bob->username)
        ->assertDontSee($alice->username);
});

it('can search for a presenters email in presenters index datatable', function () {
    $bob = Presenter::factory()->create(['email' => 'bob@example.org']);
    $alice = Presenter::factory()->create(['email' => 'alice@example.org']);

    Livewire::test(PresenterDataTable::class)
        ->set('search', 'bob@example.org')
        ->assertSee($bob->username)
        ->assertDontSee($alice->username);
});

it('can sorts by presenters user name in ascending order in presenters index datatable', function () {
    $bob = Presenter::factory()->create([
        'first_name' => 'Bob',
        'last_name' => 'Tester',
        'username' => 'bob01',
    ]);
    $alice = Presenter::factory()->create([
        'first_name' => 'Alice',
        'last_name' => 'Tester',
        'username' => 'alice01',
    ]);
    $gregor = Presenter::factory()->create([
        'first_name' => 'Gregor',
        'last_name' => 'Tester',
        'username' => 'gregor01',
    ]);

    Livewire::test(PresenterDataTable::class)
        ->call('sortBy', 'username')
        ->assertSeeInOrder([$alice->username, $bob->username, $gregor->username]);
});

it('can sorts by presenters user name in descending order in presenters index datatable', function () {
    $bob = Presenter::factory()->create([
        'first_name' => 'Bob',
        'last_name' => 'Tester',
        'username' => 'bob01',
    ]);
    $alice = Presenter::factory()->create([
        'first_name' => 'Alice',
        'last_name' => 'Tester',
        'username' => 'alice01',
    ]);
    $gregor = Presenter::factory()->create([
        'first_name' => 'Gregor',
        'last_name' => 'Tester',
        'username' => 'gregor01',
    ]);

    Livewire::test(PresenterDataTable::class)
        ->call('sortBy', 'username')
        ->call('sortBy', 'username')
        ->assertSeeInOrder([$gregor->username, $bob->username, $alice->username]);
});

it('can sorts by presenters email in ascending order in presenters index datatable', function () {
    $bob = Presenter::factory()->create(['email' => 'bob@example.org']);
    $alice = Presenter::factory()->create(['email' => 'alice@example.org']);
    $gregor = Presenter::factory()->create(['email' => 'gregor@example.org']);

    Livewire::test(PresenterDataTable::class)
        ->call('sortBy', 'email')
        ->assertSeeInOrder([$alice->username, $bob->username, $gregor->username]);
});

it('can sorts by presenters email in descending order in presenters index datatable', function () {
    $bob = Presenter::factory()->create(['email' => 'bob@example.org']);
    $alice = Presenter::factory()->create(['email' => 'alice@example.org']);
    $gregor = Presenter::factory()->create(['email' => 'gregor@example.org']);

    Livewire::test(PresenterDataTable::class)
        ->call('sortBy', 'email')
        ->call('sortBy', 'email')
        ->assertSeeInOrder([$gregor->username, $bob->username, $alice->username]);
});

it('allow viewing add presenter form to an assistant', function () {

    auth()->logout();
    signInRole(Role::ASSISTANT);

    get(route('presenters.create'))->assertOk();
});

it('allow viewing add presenter form to an admin', function () {
    get(route('presenters.create'))->assertOk();
});

it('requires a first and a last name to create a new presenter', function () {
    $attributes = Presenter::factory()->raw(['first_name' => '', 'last_name' => '']);

    post(route('presenters.store', $attributes))
        ->assertSessionHasErrors('first_name')->assertSessionHasErrors('last_name');
});

it('presenters username must be unique to create a new presenter', function () {
    $presenter = Presenter::factory()->create();
    $attributes = Presenter::factory()->raw(['username' => $presenter->username]);

    post(route('presenters.store', $attributes))->assertSessionHasErrors('username');
});

it('presenters email must be unique to create a new presenter', function () {
    $presenter = Presenter::factory()->create();
    $attributes = Presenter::factory()->raw(['email' => $presenter->email]);

    post(route('presenters.store', $attributes))->assertSessionHasErrors('email');
});

it('should remember old values on validation error', function () {
    $attributes = [
        'degree_title' => 'Dr.',
        'first_name' => 'John',
        'last_name' => '',
        'username' => 'johndoe13',
        'email' => '',
    ];

    post(route('presenters.store'), $attributes)->assertSessionHasErrors(['last_name']);

    followingRedirects();
    get(route('presenters.create'))->assertSee($attributes);
});

it('allows creating a presenter to an admin', function () {
    $attributes = [
        'degree_title' => 'Dr. Ing-',
        'first_name' => $this->faker->firstNameFemale(),
        'last_name' => $this->faker->lastName(),
        'username' => 'johndoe13',
        'email' => 'john.doe@test.com',
    ];

    post(route('presenters.store'), $attributes);

    assertDatabaseHas('presenters', ['username' => $attributes['username']]);
});

it('shows all presenter series in presenter edit page', function () {
    $presenter = Presenter::factory()->create();
    $series = Series::factory()->create();
    $series->addPresenters(collect($presenter->id));
    session()->flush();

    get(route('presenters.edit', $presenter))
        ->assertSee(route('series.edit', $series));
});

it('shows all presenter clips in presenter edit page', function () {
    $presenter = Presenter::factory()->create();
    $clip = Clip::factory()->create();

    $clip->addPresenters(collect($presenter->id));

    // flush session data to remove the update clip model message
    session()->flush();

    get(route('presenters.edit', $presenter))
        ->assertSee(route('clips.edit', $clip));
});

it('updates a presenter information', function () {
    $presenter = Presenter::factory()->create();

    patch(route('presenters.update', $presenter), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'username' => 'johndoe13',
        'email' => 'john.doe@test.com',
    ]);

    $presenter->refresh();
    assertDatabaseHas('presenters', ['first_name' => 'John']);

});

it('should display an error if trying to update a presenter username with an existing one', function () {
    $john = Presenter::factory()->create();
    $alice = Presenter::factory()->create();

    patch(route('presenters.update', $alice), [
        'first_name' => $alice->first_name,
        'last_name' => $alice->last_name,
        'username' => $john->username,
        'email' => $alice->email,
    ])->assertSessionHasErrors(['username']);

    $alice->refresh();
    expect($alice->username)->not()->toBe($john->username);
});

it('should display an error if trying to update a presenter email with an existing one', function () {
    $john = Presenter::factory()->create();
    $alice = Presenter::factory()->create();

    patch(route('presenters.update', $alice), [
        'first_name' => $alice->first_name,
        'last_name' => $alice->last_name,
        'username' => $alice->username,
        'email' => $john->email,
    ])->assertSessionHasErrors(['email']);

    $alice->refresh();
    expect($alice->email)->not()->toBe($john->email);
});

it('deletes a presenter from the database', function () {
    $presenter = Presenter::factory()->create();
    delete(route('presenters.destroy', $presenter));

    assertDatabaseMissing('presenters', ['id' => $presenter->id]);

});
