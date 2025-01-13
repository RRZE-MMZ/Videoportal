<?php

use App\Enums\Role;
use Illuminate\Foundation\Testing\WithFaker;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

uses(
    WithFaker::class,
);
uses()->group('frontend');

beforeEach(function () {
    signIn();
    $this->userSettings = auth()->user()->settings;
});

it('shows sidebar user menu for my portal', function () {
    acceptUseTerms();

    get(route('frontend.userSettings.edit'))
        ->assertViewIs('frontend.myPortal.userSettings.edit')
        ->assertSee(__('myPortal.myPortal Settings'))
        ->assertSee(__('myPortal._sidebar_menu.Series subscriptions'));
});

it('shows user settings', function () {
    acceptUseTerms();

    get(route('frontend.userSettings.edit'))
        ->assertViewIs('frontend.myPortal.userSettings.edit')
        ->assertViewHas(['settings'])
        ->assertSee(__('myPortal.Portal language'))
        ->assertSee('Show subscriptions on homepage');
});

it('shows an error if lang code is other than en or de', function () {
    acceptUseTerms();
    $attributes = [
        'language' => 'fr',
        'show_subscriptions_to_home_page' => 'on',
    ];

    put(route('frontend.userSettings.update'), $attributes)->assertSessionHasErrors(['language']);
});

it('update user settings', function () {
    assertDatabaseHas('settings', [
        'name' => auth()->user()->username,
        'data' => json_encode(config('settings.user')), ]);
    acceptUseTerms();
    $this->userSettings->refresh();
    put(route('frontend.userSettings.update'), [
        'language' => 'en',
        'show_subscriptions_to_home_page' => 'on',
    ]);

    assertDatabaseHas('settings', [
        'name' => auth()->user()->username,
        'data' => json_encode([
            'accept_use_terms' => true,
            'language' => 'en',
            'show_subscriptions_to_home_page' => true,
        ]),
    ]);
});

it('updates user lang preferences', function () {
    assertDatabaseHas('settings', [
        'name' => auth()->user()->username,
        'data' => json_encode(config('settings.user')), ]);
    acceptUseTerms();
    $this->userSettings->refresh();
    put(route('frontend.userSettings.update'), [
        'language' => 'en',
        'show_subscriptions_to_home_page' => 'on',
    ]);

    get(route('home'))->assertSee('Logout');

    put(route('frontend.userSettings.update'), [
        'language' => 'de',
        'show_subscriptions_to_home_page' => 'on',
    ]);

    get(route('home'))->assertSee('Abmelden');
});

it('shows an application menu for admin portal if use has a member or affiliate role', function () {
    // current user is a student
    acceptUseTerms();
    get(route('frontend.userSettings.edit'))->assertDontSee('Apply for admin portal');
    auth()->logout();

    // current user has a saml role employee
    signInRole(Role::MEMBER);
    acceptUseTerms();
    get(route('frontend.userSettings.edit'))->assertSee(__('myPortal._sidebar_menu.Apply for admin portal'));
});

it('shows an application status menu item if member applied for admin portal', function () {
    signInRole(Role::MEMBER);
    acceptUseTerms();
    acceptAdminPortalUseTerms();

    get(route('frontend.userSettings.edit'))
        ->assertDontSee(__('myPortal._sidebar_menu.Apply for admin portal'))
        ->assertSee(__('myPortal._sidebar_menu.Application status'))
        ->assertSee(route('frontend.user.applications'));
});
