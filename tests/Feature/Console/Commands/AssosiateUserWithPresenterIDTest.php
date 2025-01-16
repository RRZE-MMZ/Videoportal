<?php

use App\Enums\Role;
use App\Models\Presenter;
use App\Models\User;

use function Pest\Laravel\artisan;

uses()->group('backend');

it('outputs a message that the command is starting', function () {
    artisan('app:link-user-with-presenter')->expectsOutputToContain('Start to iterating over employees');
});

it('outputs a message with members counter', function () {
    User::factory(4)->create()->each(function ($user) {
        $user->assignRole(Role::MEMBER);
    });

    artisan('app:link-user-with-presenter')->expectsOutputToContain('Found 4 members');
});

it('assigns a presenter_id to a user if user and presenter have the same username or same email address', function () {
    $userA = tap(User::factory()->create(['username' => 'usr001']))->assignRole(Role::MEMBER);
    $userB = tap(User::factory()->create(['email' => 'userb@test.com']))->assignRole(Role::MEMBER);
    $presenterA = Presenter::factory()->create([
        'first_name' => $userA->first_name,
        'last_name' => $userA->last_name,
        'email' => $userA->email,
        'username' => $userA->username,
    ]);
    $presenterB = Presenter::factory()->create([
        'first_name' => $userB->first_name,
        'last_name' => $userB->last_name,
        'email' => $userB->email,
        'username' => '',
    ]);

    $userC = tap(User::factory()->create(['username' => 'usr002']))->assignRole(Role::MEMBER);

    artisan('app:link-user-with-presenter')
        ->expectsOutputToContain('Found 3 members')
        ->expectsOutputToContain('Presenter ID is set for user:'.$userA->getFullNameAttribute());

    $userA->refresh();
    $userB->refresh();
    $userC->refresh();

    expect($userA->presenter_id)->toEqual($presenterA->id);
    expect($userB->presenter_id)->toEqual($presenterB->id);
    expect($userC->presenter_id)->toBeNull();
});
