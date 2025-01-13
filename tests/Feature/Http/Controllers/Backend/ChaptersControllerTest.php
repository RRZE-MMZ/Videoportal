<?php

use App\Enums\Role;
use App\Models\Chapter;
use Facades\Tests\Setup\SeriesFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses()->group('backend');

uses()->beforeEach(function () {

    $this->series = SeriesFactory::withClips(2)->ownedBy(signInRole(Role::MODERATOR))->create();
    $this->chapter = Chapter::factory()->create(['series_id' => $this->series->id]);
});

it('displays a forbidden page for unauthorized users ', function () {
    auth()->logout(); // log out all test users
    signInRole(Role::USER);
    get(route('series.chapters.index', $this->series))->assertForbidden();
});

it('displays a forbidden page for unauthorized moderators', function () {
    auth()->logout(); // log out all test users
    signInRole(Role::MODERATOR);
    get(route('series.chapters.index', $this->series))->assertForbidden();
});

it('displays a create chapter button in chapters index page', function () {
    get(route('series.chapters.index', $this->series))->assertSee(__('chapter.backend.actions.create chapter'));
});

it('lists series chapters on the index page if there are any', function () {
    get(route('series.chapters.index', $this->series))->assertSee(__('chapter.backend.series chapters'));
    get(route('series.chapters.index', $this->series))->assertSee($this->series->chapters()->first()->title);
});

it('displays a manage series button on the series edit page', function () {
    get(route('series.edit', $this->series))->assertSee(__('series.backend.actions.manage chapters'));
});

it('has an index page for series chapters', function () {
    get(route('series.chapters.index', $this->series))
        ->assertOk()
        ->assertSee($this->series->title);
});

it('set the first chapter as default if series has no chapters', function () {
    post(route('series.chapters.create', $this->series), [
        'position' => 1,
        'title' => fake()->sentence(),
    ])->assertRedirect(route('series.chapters.index', $this->series));

    expect($this->series->chapters()->first()->default)->toBe(1);
});
it('can store a new chapter for a series', function () {
    post(route('series.chapters.create', $this->series), [
        'position' => 1,
        'title' => fake()->sentence(),
    ])->assertRedirect(route('series.chapters.index', $this->series));

    assertDatabaseHas('chapters', [
        'series_id' => $this->series->id,
        'position' => 1,
    ]);
});

it('requires a chapter position to create a chapter', function () {
    $attributes = [
        'position' => '',
        'title' => fake()->sentence(),
    ];

    post(route('series.chapters.create', $this->series), $attributes)
        ->assertSessionHasErrors('position');
});

it('requires a title to create a chapter', function () {
    $attributes = [
        'position' => '1',
        'title' => '',
    ];

    post(route('series.chapters.create', $this->series), $attributes)
        ->assertSessionHasErrors('title');
});

it('displays a forbidden page for unauthorized users when attempting to edit series chapters', function () {
    auth()->logout();
    signInRole(Role::MODERATOR);

    get(route('series.chapters.edit', [$this->series, $this->chapter]))->assertForbidden();
});

it('has an edit chapter page', function () {
    get(route('series.chapters.edit', [$this->series, $this->chapter]))->assertOk();
});

it('lists all clips on the chapter edit page', function () {
    get(route('series.chapters.edit', [$this->series, $this->chapter]))
        ->assertSee($this->series->clips()->first()->title)
        ->assertSee($this->series->latestClip->title);
});

it('requires an array of clip ids to assign them to a chapter', function () {
    $attributes = [
        'ids' => '62',
    ];

    patch(route('series.chapters.addClips', [
        $this->series,
        $this->chapter, ]), $attributes)->assertSessionHasErrors('ids');
});

it('can add a clip to a chapter', function () {
    $attributes = [
        'ids' => [$this->series->clips()->first()->id],
    ];
    patch(route('series.chapters.addClips', [
        $this->series,
        $this->chapter, ]), $attributes);

    expect($this->chapter->clips()->count())->toBe(1);
});

it('requires an array of clip ids to remove them from a chapter', function () {
    $attributes = [
        'ids' => '62',
    ];

    patch(route('series.chapters.removeClips', [
        $this->series,
        $this->chapter,
    ]), $attributes)
        ->assertSessionHasErrors('ids');
});

it('can remove a clip from a chapter', function () {
    expect($this->chapter->clips()->count())->toBe(0);
    $clip = $this->series->clips()->first();
    $clip->chapter_id = $this->chapter->id;
    $clip->save();

    expect($this->chapter->clips()->count())->toBe(1);
    $attributes = [
        'ids' => [$this->series->clips()->first()->id],
    ];
    patch(route('series.chapters.removeClips', [$this->series, $this->chapter]), $attributes);
    expect($this->chapter->clips()->count())->toBe(0);
});

it('can edit chapters', function () {
    $attributes = [
        'chapters' => [
            $this->chapter->id => [
                'position' => '3',
                'title' => 'changed',
            ],
        ],
    ];

    put(route('series.chapters.update', $this->series), $attributes);
    $this->chapter->refresh();

    expect($this->chapter->title)->toBe('changed');
});

it('can delete a chapter', function () {
    delete(route('series.chapters.delete', [$this->series, $this->chapter]));
    assertDatabaseMissing('chapters', ['id' => $this->chapter->id]);
});

it('sets a clip chapter id to null if chapter is deleted', function () {
    $clip = $this->series->clips()->first();
    $clip->chapter_id = $this->chapter->id;
    $clip->save();

    delete(route('series.chapters.delete', [$this->series, $this->chapter]));
    $clip->refresh();

    expect($this->series->clips()->first()->chapter_id)->toBeNull();

});

it('records an activity on the series when a chapter is updated', function () {
    $attributes = [
        'chapters' => [
            $this->chapter->id => [
                'position' => '3',
                'title' => 'changed',
            ],
        ],
    ];

    put(route('series.chapters.update', $this->series), $attributes);
    $this->chapter->refresh();

    assertDatabaseHas('activities', [
        'content_type' => 'series',
        'change_message' => 'Updated chapter:'.$this->chapter->title,
    ]);
});
