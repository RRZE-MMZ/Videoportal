<?php

use App\Enums\OpencastWorkflowState;
use App\Enums\Role;
use App\Models\Livestream;
use App\Services\OpencastService;
use App\Services\WowzaService;
use Tests\Setup\WorksWithOpencastClient;
use Tests\Setup\WorksWithWowzaClient;

use function Pest\Laravel\post;

uses(WorksWithWowzaClient::class);
uses(WorksWithOpencastClient::class);

beforeEach(function () {
    $this->mockWowzaHandler = $this->swapWowzaClient();

    $this->wowzaService = app(WowzaService::class);
});
it('denies post requests to simple users', function () {
    post(route('livestreams.makeReservation', ['event' => 'test-lecture-hall']))->assertRedirectToRoute('login');
});

it('denies post requests to students or moderators', function () {
    $this->signInRole(Role::STUDENT);
    post(route('livestreams.makeReservation', ['event' => 'test-lecture-hall']))->assertForbidden();
    auth()->logout();

    $this->signInRole(Role::MODERATOR);
    post(route('livestreams.makeReservation', ['event' => 'test-lecture-hall']))->assertForbidden();
});

it('validates the request to reserve a livestream room', function () {
    $this->signInRole(Role::ASSISTANT);

    post(route('livestreams.makeReservation', ['event_0' => '']))->assertSessionHasErrors('event_0');
});

it('reserves a livestream room for a clip', function () {
    $this->signInRole(Role::ASSISTANT);
    $opencastEventID = '99b90f09-c753-4b57-8e16-9b8277349e21';
    $livestream = Livestream::factory()->create([
        'name' => 'test-location',
        'opencast_location_name' => 'test-lecture-hall',
    ]);

    $this->mockHandler = $this->swapOpencastClient();
    $this->opencastService = app(OpencastService::class);
    $this->mockHandler->append(
        $this->mockEventByEventID($opencastEventID, OpencastWorkflowState::RUNNING)
    );
    post(route('livestreams.makeReservation', ['event_0' => $opencastEventID]));

    $livestream->refresh();

    expect($livestream->active)->toBe(1);
});

it('cancels a livestream room reservation', function () {
    $this->signInRole(Role::ASSISTANT);
    $livestream = Livestream::factory()->create([
        'name' => 'test-location',
        'opencast_location_name' => 'test-location',
        'active' => true,
    ]);

    post(route('livestreams.cancelReservation', $livestream));

    $livestream->refresh();
    expect($livestream->active)->toBe(0);
});
