<?php

use App\Enums\Role;
use App\Models\Clip;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\get;

uses()->group('backend');

beforeEach(function () {
    signInRole(Role::SUPERADMIN);
    $this->moderator = User::factory()->create();
    $this->moderator->assignRole(Role::MODERATOR);
});

it('renders successfully', function () {
    Clip::factory(2)->create(['owner_id' => $this->moderator->id]);
    get(route('users.edit', $this->moderator))->assertSeeLivewire('user-clips-data-table');
});

it('list and can search all users series', function () {
    $clipA = Clip::factory()->create(['title' => 'Biologie clip', 'owner_id' => $this->moderator->id]);
    $clipB = Clip::factory()->create(['title' => 'Mathematics clip', 'owner_id' => $this->moderator->id]);
    Livewire::test('user-clips-data-table', ['user' => $this->moderator])
        ->assertSee($clipA->title)
        ->assertSee($clipB->title)
        ->set('search', 'biologie')
        ->assertSee($clipA->title)
        ->assertDontSee($clipB->title);
});
