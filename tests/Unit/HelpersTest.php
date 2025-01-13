<?php

use App\Enums\Acl;
use App\Models\Asset;
use App\Models\Clip;
use App\Models\Livestream;
use App\Models\Series;
use App\Models\User;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\FileFactory;
use Illuminate\Support\Facades\Storage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function Pest\Laravel\get;

uses()->group('unit');

it('fetches a generic poster image when poster file path is null', function () {
    expect(fetchClipPoster(null))->toBe('/images/generic_clip_poster_image.png');
});

it('fetches a clip poster image when poster file path is not null', function () {
    expect(fetchClipPoster('preview_image.png'))->toBe('/thumbnails/previews-ng/preview_image.png');
});

it('has a getClipStoragePath helper function for returning clip\'s date path', function () {
    expect(getClipStoragePath(Clip::factory()->create(['created_at' => '2021-01-13 15:38:51'])))
        ->toBe('/2021/01/13/TIDES_ClipID_1/');
    expect(getClipStoragePath(Clip::factory()->create(['created_at' => '2021-01-01 15:38:51'])))
        ->toBe('/2021/01/01/TIDES_ClipID_2/');
    expect(getClipStoragePath(Clip::factory()->create(['created_at' => '2021-12-27 15:38:51'])))
        ->toBe('/2021/12/27/TIDES_ClipID_3/');
});

it('has a dropzone files function with returns a collection of all files inside the dropzone folder', function () {
    $disk = Storage::fake('video_dropzone');
    $disk->putFileAs('', FileFactory::videoFile(), 'export_video_1080.mp4');

    expect(fetchDropZoneFiles())->toBeCollection();
    expect(fetchDropZoneFiles()->contains('name', 'export_video_1080.mp4'))->toBeTrue();
});

it('ignores hidden files in collection result for dropzone files ', function () {
    $disk = Storage::fake('video_dropzone');

    $disk->putFileAs('', FileFactory::videoFile(), 'export_video_1080.mp4');
    $disk->putFileAs('', FileFactory::simpleFile(), '.DS_Store');

    expect(fetchDropZoneFiles()->count())->toBeInt()->toBe(1);
    expect(fetchDropZoneFiles()->doesntContain(['name', '.DS_Store']))->toBeTrue();
});

it('has an active link function', function () {
    get(route('dashboard'));

    expect(setActiveLink(route('dashboard')))->toBe('active-nav-link opacity-100 font-extrabold mx-2 rounded italic');
});

it('has a getAccessToken function for url tokens', function () {
    $time = dechex(time());
    $client = Acl::LMS->lower();
    $token = md5('clip'.'1'.'1234qwER'.'127.0.0.1'.$time.$client);

    expect(getAccessToken(ClipFactory::create(['password' => '1234qwER']), $time, $client))->toBe($token);
});

it('has a getUrlTokenType function for url tokens', function () {
    expect(getUrlTokenType('course'))->toBe('series');
    expect(getUrlTokenType('series'))->toBe('series');
    expect(getUrlTokenType('clip'))->toBe('clip');

    $this->expectException(NotFoundHttpException::class);
    getUrlTokenType('test');
});

it('has a getUrlClientType function for url tokens', function () {
    expect(getUrlClientType('studon'))->toBe(Acl::LMS->lower());
    expect(getUrlClientType('lms'))->toBe(Acl::LMS->lower());
    expect(getUrlClientType('password'))->toBe(Acl::PASSWORD->lower());

    $this->expectException(NotFoundHttpException::class);
    getUrlClientType('test');
});

it('has as setSessionAccessToken function for url tokens', function () {
    $time = dechex(time());
    $client = Acl::LMS->lower();
    $token = md5('clip'.'1'.'1234qwER'.'127.0.0.1'.$time.$client);
    $clip = Clip::factory()->create();
    setSessionAccessToken($clip, $token, $time, $client);

    get(route('frontend.clips.show', $clip))
        ->assertSessionHas([
            "clip_{$clip->id}_token" => $token,
            "clip_{$clip->id}_time" => $time,
            "clip_{$clip->id}_client" => $client,
        ]);
});

it(/**
 * @throws NotFoundExceptionInterface
 * @throws ContainerExceptionInterface
 */ 'has a checkAccessToken for url tokens',
    function () {
        $time = dechex(time());
        $client = Acl::LMS->lower();
        $token = md5('clip'.'1'.'1234qwER'.'127.0.0.1'.$time.$client);
        $clip = Clip::factory()->create();

        expect(checkAccessToken($clip))->toBeFalse();

        session()->put([
            "clip_{$clip->id}_token" => $token,
            "clip_{$clip->id}_time" => $time,
            "clip_{$clip->id}_client" => $client,
        ]);
        expect(checkAccessToken($clip))->toBeFalse();

        $clip->password = '1234qwER';
        $clip->save();
        expect(checkAccessToken($clip))->toBeTrue();

        session()->flush();

        // test again clip series token
        $time = dechex(time());
        $client = Acl::LMS->lower();
        $seriesToken = md5('series'.'1'.'1234QWer'.'127.0.0.1'.$time.$client);
        $series = Series::factory()->create(['password' => '1234QWer']);
        session()->put([
            "series_{$series->id}_token" => $seriesToken,
            "series_{$series->id}_time" => $time,
            "series_{$series->id}_client" => $client,
        ]);
        expect(checkAccessToken($clip))->toBeFalse();

        $clip->series_id = $series->id;
        $clip->save();
        $clip->refresh();
        expect(checkAccessToken($clip))->toBeTrue();
    }
);

it('has a getAccessToken function for url option parameters', function () {
    $time = dechex(time());
    $client = Acl::LMS->lower();
    $token = md5('clip'.'1'.'1234qwER'.'127.0.0.1'.$time.$client);
    $clip = ClipFactory::create(['password' => '1234qwER']);

    $url = "/protector/link/clip/1/{$token}/{$time}/{$client}";

    expect(getAccessToken($clip, $time, $client))->not()->toEqual($url);
    expect(getAccessToken($clip, $time, $client, true))->toEqual($url);
});

it('has a getFileExtension function', function () {
    expect(getFileExtension(Asset::factory()->create(['original_file_name' => 'test.mp4'])))->toBe('mp4');
});

it('has a findUserByOpencastRole function', function () {
    $user = User::factory()->create();
    $opencastUserRole = 'ROLE_USER_'.strtoupper($user->username);
    expect(findUserByOpencastRole($opencastUserRole)->getFullNameAttribute())->toBe($user->getFullNameAttribute());

    $opencastUserRole = 'ROLE_ADMIN';
    expect(findUserByOpencastRole($opencastUserRole))->toBe($opencastUserRole);

    $opencastUserRole = 'ROLE_USER_NOTEXISTINGUSER';
    expect(findUserByOpencastRole($opencastUserRole))->toBe($opencastUserRole);
});

it('returns the protected url for a clip file path', function () {
    expect(getProtectedUrl('/data/2022/10/12/test.mp4'))->toBeString()->toContain('/data/2022/10/12/test.mp4');
});

it('has a humanFileSizeFormat function ', function () {
    expect(humanFileSizeFormat('null'))->toBe('0 B');
    expect(humanFileSizeFormat('1024'))->toBe('1.00 kB');
});

it('has a removeTrailNumbers function', function () {
    $string = 'Mathematics for engineering 01';
    $newString = removeTrailingNumbers($string);

    expect($newString)->toBe('Mathematics for engineering');

    $string = 'Mathematics for engineering 1 01';
    $newString = removeTrailingNumbers($string);

    expect($newString)->toBe('Mathematics for engineering 1');
});

it('has a check opencast livestream room function', function () {
    $location = 'test-location';
    expect(checkOpencastLivestreamRoom($location))->toBeNull();
    Livestream::factory()->create(['name' => $location, 'opencast_location_name' => 'test-location']);

    expect(checkOpencastLivestreamRoom($location))->toBeInstanceOf(Livestream::class);
});

it('has a check for a valid insert for statistics', function () {
    // null values
    expect(check_valid_statistic_insert(null, 'POST', 'Firefox'))->toBeFalse();
    // method is not POST
    expect(check_valid_statistic_insert(23311872, 'GET', 'Firefox'))->toBeFalse();
    // user agent contains the string 'bot'
    expect(check_valid_statistic_insert(23311872, 'POST', 'bot'))->toBeFalse();

    expect(check_valid_statistic_insert(23311872, 'POST', 'Firefox'))->toBeTrue();
});
