<?php

use App\Enums\Role;
use App\Livewire\ListSeriesClips;
use App\Models\Chapter;
use App\Models\Presenter;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\SeriesFactory;

use function Pest\Laravel\get;

beforeEach(function () {
    $this->series = SeriesFactory::withClips(2)->withAssets(2)->create();
});

it('renders successfully in the frontend', function () {
    get(route('frontend.series.show', $this->series))->assertOk()->assertSeeLivewire(ListSeriesClips::class);
});

it('renders successfully in the backend for clips without chapters', function () {
    $this->signInRole(Role::SUPERADMIN);
    get(route('series.edit', $this->series))->assertOk()->assertSeeLivewire(ListSeriesClips::class);
});

it('renders successfully created in the backend for clips with chapters', function () {
    $chapter = Chapter::factory()->create();
    $seriesClip = $this->series->clips()->first();
    $seriesClip->chapter_id = $chapter->id;
    $seriesClip->save();

    $this->signInRole(Role::SUPERADMIN);

    get(route('series.edit', $this->series))
        ->assertOk()
        ->assertSeeLivewire(ListSeriesClips::class)
        ->assertSee($chapter->name);
});

it('searches for clip title for series without chapters', function () {
    $clipA = ClipFactory::withAssets(2)->create(['title' => 'Bob goes for walking', 'series_id' => $this->series->id]);
    $clipB = ClipFactory::withAssets(2)->create(['title' => 'Tom goes for running', 'series_id' => $this->series->id]);

    Livewire::test(ListSeriesClips::class, ['series' => $this->series])
        ->assertSee($clipA->title)
        ->assertSee($clipB->title)
        ->set('search', 'running')
        ->assertDontSee($clipA->title)
        ->assertSee($clipB->title);
});

it('searches for clip title for series with chapters', function () {
    $chapterA = Chapter::factory()->create();
    $clipA = ClipFactory::withAssets(2)->create([
        'title' => 'Bob goes for walking',
        'series_id' => $this->series->id,
        'chapter_id' => $chapterA->id,
    ]);
    $chapterB = Chapter::factory()->create();
    $clipB = ClipFactory::withAssets(2)->create([
        'title' => 'Tom goes for running',
        'series_id' => $this->series->id,
        'chapter_id' => $chapterB->id,
    ]);

    Livewire::test(ListSeriesClips::class, ['series' => $this->series])
        ->assertSee($clipA->title)
        ->assertSee($clipB->title)
        ->set('search', 'running')
        ->assertDontSee($clipA->title)
        ->assertSee($clipB->title)
        ->assertSee($chapterB->title)
        ->assertDontSee($chapterA->title);
});

it('searches for clip presenter for series without chapters', function () {
    $clipA = ClipFactory::withAssets(2)->create(['title' => 'Bob goes for walking', 'series_id' => $this->series->id]);
    $clipB = ClipFactory::withAssets(2)->create(['title' => 'Tom goes for running', 'series_id' => $this->series->id]);
    $clipA->presenters()->save(Presenter::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']));
    $clipB->presenters()->save(Presenter::factory()->create(['first_name' => 'Jane', 'last_name' => 'Mustermann']));

    Livewire::test(ListSeriesClips::class, ['series' => $this->series])
        ->assertSee($clipA->title)
        ->assertSee($clipB->title)
        ->set('search', 'Doe')
        ->assertSee($clipA->title)
        ->assertDontSee($clipB->title);
});

it('searches for clip presenter for series with chapters', function () {
    $chapterA = Chapter::factory()->create();
    $clipA = ClipFactory::withAssets(2)->create([
        'title' => 'Bob goes for walking',
        'series_id' => $this->series->id,
        'chapter_id' => $chapterA->id,
    ]);
    $chapterB = Chapter::factory()->create();
    $clipB = ClipFactory::withAssets(2)->create([
        'title' => 'Tom goes for running',
        'series_id' => $this->series->id,
        'chapter_id' => $chapterB->id,
    ]);
    $clipA->presenters()->save(Presenter::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']));
    $clipB->presenters()->save(Presenter::factory()->create(['first_name' => 'Jane', 'last_name' => 'Mustermann']));

    Livewire::test(ListSeriesClips::class, ['series' => $this->series])
        ->assertSee($clipA->title)
        ->assertSee($clipB->title)
        ->set('search', 'mustermann')
        ->assertDontSee($clipA->title)
        ->assertSee($clipB->title)
        ->assertSee($chapterB->title)
        ->assertDontSee($chapterA->title);
});
