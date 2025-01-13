<?php

use App\Enums\ApplicationStatus;
use App\Enums\Role;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

uses()->group('frontend');

it('is forbidden for a user role to access admin portal use terms page', function () {
    // sign in a simple user
    signIn();

    acceptUseTerms();

    get(route('frontend.admin.portal.use.terms'))->assertForbidden();
});

it('is forbidden for a student role to access admin portal use terms page', function () {
    // sign in a student
    signInRole(Role::STUDENT);

    acceptUseTerms();

    get(route('frontend.admin.portal.use.terms'))->assertForbidden();
});

it('is allowed for a member role to access admin portal use terms page', function () {
    // sign in a member
    signInRole(Role::MEMBER);

    acceptUseTerms();

    get(route('frontend.admin.portal.use.terms'))->assertOk();
});

it('is not allowed for role other than member to post for accepting use terms', function () {
    signIn();

    acceptUseTerms();

    put(route('frontend.admin.portal.accept.use.terms'), ['accept_use_terms' => 'on'])->assertForbidden();
});

it('updated user settings when user accepts admin portal use terms', function () {
    // sign in a member
    $user = signInRole(Role::MEMBER);

    acceptUseTerms();

    assertDatabaseHas('settings', [
        'name' => $user->username,
        'data' => json_encode($user->settings->data), ]);

    put(route('frontend.admin.portal.accept.use.terms'), ['accept_use_terms' => 'on'])
        ->assertSessionDoesntHaveErrors('accept_use_terms');

    assertEquals(ApplicationStatus::IN_PROGRESS->value, $user->settings->data['admin_portal_application_status']);
    assertTrue($user->settings->data['accept_admin_portal_use_terms']);
});

it('redirects to user applications page if a user already accepted admin portal use terms', function () {
    signInRole(Role::MEMBER);
    acceptUseTerms();
    acceptAdminPortalUseTerms();

    get(route('frontend.admin.portal.use.terms'))->assertRedirectToRoute('frontend.user.applications');
});
