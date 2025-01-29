<?php

use App\Enums\Role;
use App\Models\Clip;
use App\Models\Series;
use App\Models\User;
use App\Notifications\MassOwnershipAssignment;
use App\Notifications\MassOwnershipRevoke;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\post;

uses()->group('backend');

beforeEach(function () {
    [$this->moderatorA, $this->moderatorB] = User::factory(2)->create();
    $this->moderatorA->assignRole(Role::MODERATOR);
    $this->moderatorB->assignRole(Role::MODERATOR);

});

it('not allow a visitor to change multiple series ownership', function () {
    post(route('users.revokeMultipleSeriesOwnerShip', $this->moderatorA), [
        'series_ids' => ['1', '2'],
        'userID' => $this->moderatorB->id])->assertRedirectToRoute('login');
});

it('not allow a visitor to change multiple clips ownership', function () {
    post(route('users.revokeMultipleClipsOwnerShip', $this->moderatorA), [
        'clip_ids' => ['1', '2'],
        'userID' => $this->moderatorB->id])->assertRedirectToRoute('login');
});

it('not allow any user other than superadmin to change multiple series ownership', function () {
    signIn($this->moderatorB);
    post(route('users.revokeMultipleSeriesOwnerShip', $this->moderatorA), [
        'series_ids' => ['1', '2'],
        'userID' => $this->moderatorB->id])->assertForbidden();
    auth()->logout();

    $this->signInRole(Role::STUDENT);
    post(route('users.revokeMultipleSeriesOwnerShip', $this->moderatorA), [
        'series_ids' => ['1', '2'],
        'userID' => $this->moderatorB->id])->assertForbidden();
    auth()->logout();

    $this->signInRole(Role::ASSISTANT);
    post(route('users.revokeMultipleSeriesOwnerShip', $this->moderatorA), [
        'series_ids' => ['1', '2'],
        'userID' => $this->moderatorB->id])->assertForbidden();
    auth()->logout();

    $this->signInRole(Role::ADMIN);
    post(route('users.revokeMultipleSeriesOwnerShip', $this->moderatorA), [
        'series_ids' => ['1', '2'],
        'userID' => $this->moderatorB->id])->assertForbidden();
    auth()->logout();
});

it('not allow any user other than superadmin to change multiple clips ownership', function () {
    signIn($this->moderatorB);
    post(route('users.revokeMultipleClipsOwnerShip', $this->moderatorA), [
        'clip_ids' => ['1', '2'],
        'userID' => $this->moderatorB->id])->assertForbidden();
    auth()->logout();

    $this->signInRole(Role::STUDENT);
    post(route('users.revokeMultipleClipsOwnerShip', $this->moderatorA), [
        'clip_ids' => ['1', '2'],
        'userID' => $this->moderatorB->id])->assertForbidden();
    auth()->logout();

    $this->signInRole(Role::ASSISTANT);
    post(route('users.revokeMultipleClipsOwnerShip', $this->moderatorA), [
        'clip_ids' => ['1', '2'],
        'userID' => $this->moderatorB->id])->assertForbidden();
    auth()->logout();

    $this->signInRole(Role::ADMIN);
    post(route('users.revokeMultipleClipsOwnerShip', $this->moderatorA), [
        'clip_ids' => ['1', '2'],
        'userID' => $this->moderatorB->id])->assertForbidden();
    auth()->logout();
});

it('validated input to change multiple series ownership', function () {
    $this->signInRole(Role::SUPERADMIN);

    post(route('users.revokeMultipleSeriesOwnerShip', $this->moderatorA), [])->assertSessionHasErrors();

});

it('validated input to change multiple clips ownership', function () {
    $this->signInRole(Role::SUPERADMIN);

    post(route('users.revokeMultipleClipsOwnerShip', $this->moderatorA), [])->assertSessionHasErrors();

});

it('changes multiple series ownership', function () {
    [$seriesA, $seriesB] = Series::factory(2)->create(['owner_id' => $this->moderatorA->id]);
    $clipA = Clip::factory()->create(['owner_id' => $this->moderatorA->id, 'series_id' => $seriesA->id]);
    $this->signInRole(Role::SUPERADMIN);

    post(route('users.revokeMultipleSeriesOwnerShip', $this->moderatorA), [
        'series_ids' => json_encode([$seriesA->id, $seriesB->id]),
        'userID' => $this->moderatorB->id,
    ]);

    expect($seriesA->refresh()->owner_id)->toBe($this->moderatorB->id);
    expect($seriesB->refresh()->owner_id)->toBe($this->moderatorB->id);
    expect($clipA->refresh()->owner_id)->toBe($this->moderatorB->id);
});

it('changes multiple single clips ownership', function () {
    [$clipA, $clipB] = Clip::factory(2)->create(['owner_id' => $this->moderatorA->id]);
    $this->signInRole(Role::SUPERADMIN);

    post(route('users.revokeMultipleClipsOwnerShip', $this->moderatorA), [
        'clip_ids' => json_encode([$clipA->id, $clipB->id]),
        'userID' => $this->moderatorB->id,
    ]);

    expect($clipA->refresh()->owner_id)->toBe($this->moderatorB->id);
    expect($clipB->refresh()->owner_id)->toBe($this->moderatorB->id);
});

it('notifies users on multiple series ownership change', function () {
    Notification::fake();

    [$seriesA, $seriesB] = Series::factory(2)->create(['owner_id' => $this->moderatorA->id]);
    $this->signInRole(Role::SUPERADMIN);

    post(route('users.revokeMultipleSeriesOwnerShip', $this->moderatorA), [
        'series_ids' => json_encode([$seriesA->id, $seriesB->id]),
        'userID' => $this->moderatorB->id,
    ]);

    Notification::assertSentTo($this->moderatorA, MassOwnershipRevoke::class);
    Notification::assertSentTo($this->moderatorB, MassOwnershipAssignment::class);
});

it('notifies users on multiple clips ownership change', function () {
    Notification::fake();

    [$clipA, $clipB] = Clip::factory(2)->create(['owner_id' => $this->moderatorA->id]);
    $this->signInRole(Role::SUPERADMIN);

    post(route('users.revokeMultipleClipsOwnerShip', $this->moderatorA), [
        'clip_ids' => json_encode([$clipA->id, $clipB->id]),
        'userID' => $this->moderatorB->id,
    ]);

    Notification::assertSentTo($this->moderatorA, MassOwnershipRevoke::class);
    Notification::assertSentTo($this->moderatorB, MassOwnershipAssignment::class);
});
