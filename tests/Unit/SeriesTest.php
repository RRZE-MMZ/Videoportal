<?php

use App\Enums\Acl;
use App\Models\Clip;
use App\Models\Semester;
use App\Models\Series;
use App\Models\User;
use Facades\Tests\Setup\SeriesFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use Tests\Setup\WorksWithOpencastClient;

use function Pest\Laravel\get;

uses(WorksWithOpencastClient::class);
uses()->group('unit');

beforeEach(function () {
    $this->series = Series::factory()->create();
});

test('to array', function () {
    $this->series->fresh();
    expect(array_keys($this->series->toArray()))->toBe([
        'title', 'description', 'organization_id', 'slug', 'owner_id', 'opencast_series_id', 'is_public', 'image_id',
        'updated_at', 'created_at', 'id', 'owner', 'organization', 'image',
    ]);
});

it('has a slug route', function () {
    expect(route('frontend.series.show', $this->series))
        ->toEqual(
            route('home').'/series/'.Str::slug($this->series->title.'-'.Semester::current()->get()->first()->acronym)
        );
});

it('has a unique slug', function () {
    $seriesA = Series::factory()->create(['title' => 'A test title', 'slug' => 'A test title']);
    $seriesB = Series::factory()->create(['title' => 'A test title', 'slug' => 'A test title']);

    expect($seriesA->slug)->not->toEqual($seriesB->slug);
});

it('has many clips', function () {
    expect($this->series->clips())->toBeInstanceOf(HasMany::class);
});

it('has many chapters', function () {
    expect($this->series->chapters())->toBeInstanceOf(HasMany::class);
});

it('has many members', function () {
    expect($this->series->members())->toBeInstanceOf(BelongsToMany::class);
});

it('has many subscribers', function () {
    expect($this->series->subscribers())->toBeInstanceOf(BelongsToMany::class);
});

it('has an add member function for membership', function () {
    expect($this->series->addMember(User::factory()->create()))->toBeInstanceOf(User::class);
});

it('han a remove member function for membership', function () {
    expect($this->series->removeMember(User::factory()->create()))->toBeInstanceOf(User::class);
});

it('has many presenters using presentable trait', function () {
    expect($this->series->presenters())->toBeInstanceOf(MorphToMany::class);
});

it('has many documents using documentable trait', function () {
    expect($this->series->documents())->toBeInstanceOf(MorphToMany::class);
});

it('has many comments', function () {
    expect($this->series->comments())->toBeInstanceOf(MorphMany::class);
});

it('fetches the latest clip', function () {
    expect($this->series->latestClip())->toBeInstanceOf(HasOne::class);
});

it('belongs to an organization unit', function () {
    expect($this->series->organization())->toBeInstanceOf(BelongsTo::class);
});

it('belongs to an image', function () {
    expect($this->series->image())->toBeInstanceOf(BelongsTo::class);
});

it('can add a clip', function () {
    signIn();

    expect($this->series->addClip([
        'title' => 'a clip',
        'slug' => 'a-clip',
        'tags' => [],
        'description' => 'clip description',
        'semester_id' => '1',
    ]))->toBeInstanceOf(Clip::class);
});

it('can reorder clips based on an array of episodes', function () {
    Clip::factory(2)->create(['series_id' => $this->series->id]);

    expect($this->series->reorderClips(collect([
        1 => '3',
        2 => '1',
    ])))->toBeInstanceOf(Series::class);
});

it('updates opencast series id', function () {
    $series = SeriesFactory::create();
    $series->updateOpencastSeriesId($this->mockCreateSeriesResponse());

    expect($series->opencast_series_id)->not->toBeNull();
});

it('has a views method for series views returning the number of the views', function () {
    expect($this->series->views())->toBeInt();
});

it('has a public scope', function () {
    expect(Series::isPublic())->toBeInstanceOf(Builder::class);
});

it('has a current semester scope', function () {
    expect(Series::currentSemester())->toBeInstanceOf(Builder::class);
});

it('has a scope to fetch clips with assets', function () {
    expect(Series::hasClipsWithAssets())->toBeInstanceOf(Builder::class);
});

it('has a scope to fetch clips with certain acl', function () {
    expect(Series::hasClipsWithAssets(Acl::PUBLIC))->toBeInstanceOf(Builder::class);
});

test('series owner can be null', function () {
    $user = User::factory()->create();
    $series = $user->series()->create(['title' => 'test', 'slug' => 'test']);
    $user->delete();
    $series = Series::find($series->id);

    expect($series->owner_id)->toBeNull();
});

it('has an opencast series id scope', function () {
    expect(Series::hasOpencastSeriesID())->toBeInstanceOf(Builder::class);
});

it('resolves also id in route', function () {
    get('series/'.$this->series->id)->assertStatus(403);
    get(route('frontend.series.show', $this->series->id))->assertStatus(403);
    get('/series/535')->assertStatus(404);
});

it('fetches clip acls as comma seperated string', function () {
    expect($this->series->fetchClipsAcls())->toBeString();
});

it('returns bool for clips acls check', function () {
    expect($this->series->checkAcls())->toBeBool();
});

it('returns an integer with the counter of the clips views', function () {
    expect($this->series->views())->toBeInt();
});

it('returns an array with geolocation views and counters', function () {
    expect($this->series->sumGeoLocationDataGroupedByMonth())->toBeArray();
});
