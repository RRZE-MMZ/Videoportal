<?php

use App\Enums\Role;
use App\Mail\NewLocalUserCreated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    // TODO: Change the autogenerated stub
    $this->signInRole(Role::SUPERADMIN);
});

test('reset password link screen can be rendered only for superadmins', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested only for superadmins', function () {
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    $this->assertDatabaseHas('password_resets', ['email' => $user->email]);
});

test('reset password screen can be rendered when a local user is created', function () {
    Mail::fake();
    signInRole(Role::SUPERADMIN);
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'username' => 'local.test12',
        'email' => 'john@doe.com',
    ];

    post(route('users.store'), $attributes)->assertStatus(302);

    Mail::assertSent(NewLocalUserCreated::class, $attributes['email']);
});
