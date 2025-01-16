<?php

use App\Events\UserExpired;
use App\Mail\ExpiredUsersFound;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    User::factory(20)->create(); // not expired users
    User::factory(12)->create(['expired' => true]);
});

it('outputs a console output with the number of expired users found', function () {
    Event::fake();
    $expectedOutput = '[COMMAND]:App\Console\Commands\CheckForExpiredUsers: 12 expired users found';
    $this->artisan('app:check-for-expired-users')
        ->expectsOutput($expectedOutput);
});

it('dispatches a user expired event if an expired user is found', function () {
    Event::fake();
    $this->artisan('app:check-for-expired-users');
    Event::assertDispatched(UserExpired::class, 12);
});

it('notifies admins with info about the deleted users', function () {
    Event::fake();
    Mail::fake();
    $this->artisan('app:check-for-expired-users');

    Mail::assertSent(ExpiredUsersFound::class, 'admin@test.com');
});
