<?php

use App\Enums\ApplicationStatus;
use App\Enums\Role;
use App\Models\Presenter;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

uses()->group('backend');

beforeEach(function () {
    // sign in a user, apply for admin portal and logout
    $this->superadminA = User::factory()->create()->assignRole(Role::SUPERADMIN);
    $this->superadminB = User::factory()->create()->assignRole(Role::SUPERADMIN);
    $this->appliedUser = signInRole(Role::MEMBER);
    acceptUseTerms();
    acceptAdminPortalUseTerms();
    auth()->logout();
});

it('redirect visitors request to login page', function () {
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]))
        ->assertRedirectToRoute('login');
});

it('allows post requests only to superadmin users', function () {
    signInRole(Role::MODERATOR);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]))
        ->assertForbidden();
    auth()->logout();

    signInRole(Role::ASSISTANT);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]))
        ->assertForbidden();
    auth()->logout();

    signInRole(Role::MEMBER);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]))
        ->assertForbidden();
    auth()->logout();

    signInRole(Role::ADMIN);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]))
        ->assertForbidden();
    auth()->logout();

    signIn($this->superadminA);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]))
        ->assertRedirect();
    auth()->logout();
});

test('a username is required for grant a user access to admin portal', function () {
    signIn($this->superadminA);

    post(route('admin.portal.application.grant', ['username' => '']))->assertSessionHasErrors('username');
});

it('shows a username validation error if a user has not applied for admin portal', function () {
    $user = User::factory()->create();
    signIn($this->superadminA);
    post(route('admin.portal.application.grant', ['username' => $user->username]))
        ->assertSessionHasErrors('username');
});

it('shows no errors if user applied for admin portal', function () {
    signIn($this->superadminA);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]))
        ->assertSessionDoesntHaveErrors();
});

it('creates a presenter with the same name as the user', function () {
    signIn($this->superadminA);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]));

    assertDatabaseHas('presenters', [
        'username' => $this->appliedUser->username,
    ]);
});

it('updates a presenter if a user with same username is found', function () {
    signIn($this->superadminA);
    $presenter = Presenter::factory()->create(['username' => $this->appliedUser->username]);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]));

    expect($presenter->refresh()->first_name)->toBe($this->appliedUser->first_name);
    expect($presenter->refresh()->last_name)->toBe($this->appliedUser->last_name);
});

it('applied user get\'s a moderator role if application is accepted ', function () {
    signIn($this->superadminA);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]));
    $this->appliedUser->refresh();

    expect($this->appliedUser->hasRole(Role::MODERATOR))->toBeTrue();
});

it('updates applied user application status', function () {
    signIn($this->superadminA);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]));
    $this->appliedUser->refresh();

    expect($this->appliedUser->settings->data['admin_portal_application_status'])
        ->toBe(ApplicationStatus::COMPLETED());
});

it('updates user notification and mark it as read', function () {
    signIn($this->superadminA);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]));

    $this->superadminA->refresh();

    expect($this->superadminA->unreadNotifications->count())->toBe(0);

});

it('updates all notifications related to the application user when application is processed', function () {
    signIn($this->superadminA);
    post(route('admin.portal.application.grant', ['username' => $this->appliedUser->username]));

    $this->superadminA->refresh();
    $this->superadminB->refresh();

    expect($this->superadminA->notifications->filter(function ($notification) {
        return $notification->data['username_applied_for_admin_portal'] === $this->appliedUser->username;
    })->first()->data['application_status'])->toBe(ApplicationStatus::COMPLETED());

    expect($this->superadminB->notifications->filter(function ($notification) {
        return $notification->data['username_applied_for_admin_portal'] === $this->appliedUser->username;
    })->first()->data['application_status'])->toBe(ApplicationStatus::COMPLETED());
});
