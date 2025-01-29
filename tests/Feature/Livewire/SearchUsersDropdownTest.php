<?php

use App\Enums\Role;
use App\Livewire\SearchUsersDropdown;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->userA = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'username' => 'jo1234',
    ]);
    $this->userB = User::factory()->create([
        'first_name' => 'Alice',
        'last_name' => 'Cooper',
        'username' => 'al5678',
    ]);
    $this->userC = User::factory()->create([
        'first_name' => 'Max',
        'last_name' => 'Musterman',
        'username' => 'ma9101',
    ]);
    $this->userA->assignRole(Role::MODERATOR);
    $this->userB->assignRole(Role::MODERATOR);
    $this->userC->assignRole(Role::MODERATOR);
});

it('renders successfully', function () {
    Livewire::test(SearchUsersDropdown::class)
        ->assertSee('Search users...')
        ->assertDontSee('No user found.');
});

it('updates the search results when a user types', function () {

    Livewire::test(SearchUsersDropdown::class)
        ->set('search', '1234')
        ->assertSee($this->userA->getFullNameAttribute())
        ->assertDontSee($this->userB->getFullNameAttribute())
        ->assertDontSee($this->userC->getFullNameAttribute());
});

it('selects a user and updates selectedUserId', function () {
    Livewire::test(SearchUsersDropdown::class)
        ->call('selectUser', $this->userA->id)
        ->assertSet('selectedUserId', $this->userA->id)
        ->assertSet('search', $this->userA->getFullNameAttribute().'['.$this->userA->username.']')
        ->assertDontSee($this->userA->getFullNameAttribute());
});

it('emits userSelected event when a user is selected', function () {
    Livewire::test(SearchUsersDropdown::class)
        ->call('selectUser', $this->userA->id)
        ->assertDispatched('userSelected', $this->userA->id);
});
