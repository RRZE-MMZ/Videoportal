<?php

use App\Livewire\ActivitiesDataTable;
use App\Models\Activity;
use App\Models\Asset;
use App\Models\Clip;
use App\Models\Presenter;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

uses(WithFaker::class);

it('triggers an activity on series create event', function () {
    $series = Series::factory()->create();

    assertDatabaseHas('activities', ['object_id' => $series->id]);
});

it('triggers an activity on series update', function () {
    $series = Series::factory()->create();
    assertDatabaseHas('activities', ['change_message' => 'created series']);

    $series->title = 'Test';
    $series->save();
    assertDatabaseHas('activities', ['change_message' => 'updated series']);
});

it('triggers an activity on series delete', function () {
    $series = Series::factory()->create();
    assertDatabaseHas('activities', ['change_message' => 'created series']);

    $series->delete();
    assertDatabaseHas('activities', ['change_message' => 'deleted series']);
});

it('returns all activities for a given series', function () {
    $series = Series::factory()->create();
    $series->title = 'Test';
    $series->save();
    $anotherSeries = Series::factory()->create();

    expect($series->activities()->count())->toEqual(2);
    expect($anotherSeries->activities()->count())->toEqual(1);
});

it('triggers an activity on clip create', function () {
    Clip::factory()->create();

    assertDatabaseHas('activities', ['change_message' => 'created clip']);
});

it('triggers an activity on clip update', function () {
    $clip = Clip::factory()->create();
    assertDatabaseHas('activities', ['change_message' => 'created clip']);

    $clip->title = 'Test';
    $clip->save();
    assertDatabaseHas('activities', ['change_message' => 'updated clip']);
});

it('triggers an activity on clip delete', function () {
    $clip = Clip::factory()->create();
    assertDatabaseHas('activities', ['change_message' => 'created clip']);

    $clip->delete();
    assertDatabaseHas('activities', ['change_message' => 'deleted clip']);
});

it('returns all activities for a given clip', function () {
    $clip = Clip::factory()->create();
    $clip->title = 'Test';
    $clip->save();
    $anotherClip = Clip::factory()->create();

    expect($clip->activities()->count())->toEqual(2);
    expect($anotherClip->activities()->count())->toEqual(1);
});

it('triggers an activity on presenter create', function () {
    Presenter::factory()->create();

    assertDatabaseHas('activities', ['change_message' => 'created presenter']);
});

it('triggers an activity on user create', function () {
    User::factory()->create();

    assertDatabaseHas('activities', ['change_message' => 'created user']);
});

it('triggers an activity on asset create', function () {
    Asset::factory()->create();

    assertDatabaseHas('activities', ['change_message' => 'created asset']);
});

function it_renders_a_datatable_for_activities(): void
{
    get(route('activities.index'))->assertOk();
}

function it_contains_activities_livewire_component_on_index_page(): void
{
    get(route('activities.index'))->assertSeeLivewire('activities-data-table');
}

it('has a series checkbox in index activities data table that filter series', function () {
    $seriesActivity = Activity::factory()->create([
        'content_type' => 'series',
        'change_message' => 'create series',
    ]);
    $clipActivity = Activity::factory()->create([
        'content_type' => 'clip',
        'change_message' => 'create clip',
    ]);

    Livewire::test(ActivitiesDataTable::class)
        ->assertSee($seriesActivity->change_message)
        ->assertSee($clipActivity->change_message)
        ->set('series', true)
        ->assertSee($seriesActivity->change_message)
        ->assertDontSee($clipActivity->change_message);
});

it('can search for a change message in index activities data table', function () {
    $seriesActivity = Activity::factory()->create([
        'content_type' => 'series',
        'change_message' => 'create series',
    ]);
    $clipActivity = Activity::factory()->create([
        'content_type' => 'clip',
        'change_message' => 'update clip',
    ]);
    Livewire::test(ActivitiesDataTable::class)
        ->set('search', 'update')
        ->assertSee($clipActivity->change_message)
        ->assertDontSee($seriesActivity->change_message);
});

it('can sort by content type in index activities data table', function () {
    $articleActivity = Activity::factory()->create([
        'content_type' => 'article',
        'change_message' => 'updates article',
    ]);
    $seriesActivity = Activity::factory()->create([
        'content_type' => 'series',
        'change_message' => 'create series',
    ]);
    $clipActivity = Activity::factory()->create([
        'content_type' => 'clip',
        'change_message' => 'update clip',
    ]);

    Livewire::test(ActivitiesDataTable::class)
        ->call('sortBy', 'content_type')
        ->call('sortBy', 'content_type')
        ->assertSeeInOrder([
            $seriesActivity->content_type, $clipActivity->content_type, $articleActivity->content_type,
        ]);
});
