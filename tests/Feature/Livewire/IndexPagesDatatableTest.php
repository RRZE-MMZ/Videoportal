<?php

use App\Enums\Role;
use App\Livewire\IndexPagesDatatable;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\SeriesFactory;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    SeriesFactory::withClips(2)->withAssets(2)->create(5);
});

it('renders successfully in series index page', function () {
    get(route('frontend.series.index'))
        ->assertOk()->assertSeeLivewire(IndexPagesDatatable::class);
});

it('renders successfully in clip index page', function () {
    get(route('frontend.clips.index'))
        ->assertOk()->assertSeeLivewire(IndexPagesDatatable::class);
});

it('renders successfully in dashboard for user series', function () {
    SeriesFactory::withClips(2)->withAssets(2)->ownedBy(signInRole(Role::MODERATOR))->create(5);

    get(route('dashboard'))->assertSeeLivewire(IndexPagesDatatable::class);
});

it('renders successfully in dashboard for user clips', function () {
    ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();

    get(route('dashboard'))->assertSeeLivewire(IndexPagesDatatable::class);
});

it('can search for series title in frontpage series index', function () {
    $chemistrySeries = SeriesFactory::withClips(2)->withAssets(2)->create(['title' => 'Chemistry series']);
    $biologieSeries = SeriesFactory::withClips(2)->withAssets(2)->create(['title' => 'Biologie series']);
    Livewire::test(IndexPagesDatatable::class, ['type' => 'series'])
        ->assertSee($chemistrySeries->title)
        ->assertSee($biologieSeries->title)
        ->set('search', 'chemistry')
        ->assertSee($chemistrySeries->title)
        ->assertDontSee($biologieSeries->title);
});

it('can search for clip title in frontpage clips index', function () {
    $chemistryClip = ClipFactory::withAssets(2)->create(['title' => 'Chemistry series']);
    $biologieClip = ClipFactory::withAssets(2)->create(['title' => 'Biologie series']);
    Livewire::test(IndexPagesDatatable::class, ['type' => 'clips'])
        ->assertSee($chemistryClip->title)
        ->assertSee($biologieClip->title)
        ->set('search', 'chemistry')
        ->assertSee($chemistryClip->title)
        ->assertDontSee($biologieClip->title);
});
