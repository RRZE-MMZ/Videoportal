<?php

use App\Enums\Role;
use App\Models\Series;
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
    Series::factory(2)->create(['owner_id' => $this->moderator->id]);
    get(route('users.edit', $this->moderator))->assertSeeLivewire('user-series-data-table');
});

it('list and can search all users series', function () {
    $seriesA = Series::factory()->create(['title' => 'Biologie video series', 'owner_id' => $this->moderator->id]);
    $seriesB = Series::factory()->create(['title' => 'Mathematics video series', 'owner_id' => $this->moderator->id]);
    Livewire::test('user-series-data-table', ['user' => $this->moderator])
        ->assertSee($seriesA->title)
        ->assertSee($seriesB->title)
        ->set('search', 'biologie')
        ->assertSee($seriesA->title)
        ->assertDontSee($seriesB->title);
});
