<?php

use App\Enums\Acl;
use App\Models\Asset;
use App\Models\Livestream;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Returns poster image relative file path of a clip or default
 */
function fetchClipPoster(?string $player_preview): string
{
    if (is_null($player_preview)) {
        $portalSettings = Setting::portal();
        $img = $portalSettings->data['clip_generic_poster_image_name'] ?? 'generic_poster_image.png';

        return "/images/$img";
    } else {
        return "/thumbnails/previews-ng/$player_preview";
    }
}

/**
 * Return file dir for a clip based on created date
 */
function getClipStoragePath(Model $model): string
{
    return '/'.Carbon::createFromFormat('Y-m-d', $model->created_at->format('Y-m-d'))->year.
        '/'.str_pad(
            Carbon::createFromFormat('Y-m-d', $model->created_at->format('Y-m-d'))->month,
            2,
            '0',
            STR_PAD_LEFT
        ).
        '/'.str_pad(Carbon::createFromFormat('Y-m-d', $model->created_at->format('Y-m-d'))->day, 2, '0', STR_PAD_LEFT).
        "/{$model->folder_id}/";
}

/*
 * Fetch all files in the dropzone with sha1 hash
 * @param bool $ffmpegCheck
 * @return Collection
 */
function fetchDropZoneFiles($ffmpegCheck = true): Collection
{
    /*
     *  Use Storage instead of File Facade. That way although hidden files will be fetched,
     * it's easy to mock and create the test. The hidden files will be filtered.
     */
    $files = collect(Storage::disk('video_dropzone')->files())
        ->filter(function ($file) {
            /*
             * filter hidden files
             */
            return $file[0] !== '.';
        })
        ->mapWithKeys(function ($file) use ($ffmpegCheck) {
            return prepareFileForUpload($file, true, $ffmpegCheck);
        })->sortByDesc('date_modified');

    return $files;

}

/**
 * @return array[]
 */
function prepareFileForUpload($file, bool $isDropZoneFile, bool $ffmpegCheck = true): array
{
    $video = null;
    $mime = null;

    $dateModified = Carbon::createFromTimestamp(now())->format('Y-m-d H:i:s');

    if ($isDropZoneFile) {
        $lastModified = Carbon::createFromTimestamp(Storage::disk('video_dropzone')->lastModified($file))
            ->format('Y-m-d H:i:s');
        $tag = 'dropzone/file';
    } else {
        /*
         * Uploaded file is not allowed to pass to an instance of a job instead save it to disk first
         */
        $path = $file->store('/', 'local');
        $tag = 'single/file';
        $file = $path;
    }

    // Check whether is file is at the moment written at the disk
    if ($isDropZoneFile) {
        if ((Carbon::now()->diffInMinutes($lastModified) > 2 || App::environment('testing')) && $ffmpegCheck) {
            $video = FFMpeg::fromDisk('video_dropzone')->open($file)->getVideoStream();
            $mime = mime_content_type(Storage::disk('video_dropzone')->path($file));
            $dateModified = Carbon::createFromTimestamp(Storage::disk('video_dropzone')
                ->lastModified($file))
                ->format('Y-m-d H:i:s');
        }
    } else {
        $video = FFMpeg::open($file)->getVideoStream();
        $mime = mime_content_type(Storage::disk('local')->path($file));
    }

    return [
        sha1($file) => [
            'tag' => $tag,
            'type' => $mime,
            'video' => ($video !== null)
                ? "{$video->get('width')}x{$video->get('height')}"
                : null,
            'version' => '1',
            'date_modified' => $dateModified,
            'name' => $file,
        ],
    ];
}

/**
 * Returns tailwind menu active link css rule
 */
function setActiveLink(string $route): string
{
    return (Str::contains(url()->current(), $route))
        ? 'active-nav-link opacity-100 font-extrabold mx-2 rounded italic'
        : '';
}

/**
 * Generates an LMS token based on the given object (series|clip)
 *
 * @param  false  $withURL
 */
function getAccessToken($obj, $time, string $client, bool $withURL = false): string
{
    $type = lcfirst(class_basename($obj::class));

    $token = md5($type.$obj->id.$obj->password.request()->ip().$time.$client);

    return ($withURL) ? "/protector/link/{$type}/{$obj->id}/{$token}/{$time}/{$client}" : $token;
}

function getUrlTokenType(string $type): string
{
    return match ($type) {
        default => abort(404),
        'series', 'course' => 'series',
        'clip' => 'clip',
    };
}

function getUrlClientType(string $client): string
{
    return match ($client) {
        default => abort(404),
        'studon', 'lms' => Acl::LMS->lower(),
        'password' => Acl::PASSWORD->lower(),
    };
}

function setSessionAccessToken($obj, $token, $time, $client): void
{
    $objType = str(class_basename($obj))->lcfirst();

    session()->put([
        "{$objType}_{$obj->id}_token" => $token,
        "{$objType}_{$obj->id}_time" => $time,
        "{$objType}_{$obj->id}_client" => $client,
    ]);
}

/**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */
function checkAccessToken($obj): bool
{
    //Type can be either series or clip
    $tokenType = lcfirst(class_basename($obj::class));

    if (session()->exists("{$tokenType}_{$obj->id}_token")) {
        $cookieTokenHash = session()->get("{$tokenType}_{$obj->id}_token");
        $cookieTokenTime = session()->get("{$tokenType}_{$obj->id}_time");
        $cookieTokenClient = session()->get("{$tokenType}_{$obj->id}_client");

        return $cookieTokenHash === getAccessToken($obj, $cookieTokenTime, $cookieTokenClient);
    } elseif ($tokenType === 'clip' && session()->exists("series_{$obj->series->id}_token")) {
        //check whether a series token for this clip exists
        $cookieTokenHash = session()->get("series_{$obj->series->id}_token");
        $cookieTokenTime = session()->get("series_{$obj->series->id}_time");
        $cookieTokenClient = session()->get("series_{$obj->series->id}_client");

        return $cookieTokenHash === getAccessToken($obj->series, $cookieTokenTime, $cookieTokenClient);
    } else {
        return false;
    }
}

function getFileExtension(Asset $asset): string
{
    return Str::afterLast($asset->original_file_name, '.');
}

function findUserByOpencastRole(string $opencastRole): User|string
{
    if (Str::of($opencastRole)->contains('ROLE_USER_')) {
        $username = Str::lower(Str::after($opencastRole, 'ROLE_USER_'));

        return User::search($username)->get()->first() ?? $opencastRole;
    } else {
        return $opencastRole;
    }
}

function getProtectedUrl(?string $filePath): string
{
    if (is_null($filePath)) {
        return '';
    }
    $settingsData = Setting::streaming();
    $filePath = '/'.$filePath;
    $secret = $settingsData->data['cdn']['server1']['secret'];
    $cdn = $settingsData->data['cdn']['server1']['url'];
    $hexTime = dechex(time());
    $userIP = (App::environment(['testing', 'local'])) ? env('FAUTV_USER_IP') : $_SERVER['REMOTE_ADDR'];
    $token = md5($secret.$filePath.$hexTime.$userIP);

    return $cdn.$token.'/'.$hexTime.$filePath;
}

function humanFileSizeFormat(?string $bytes, $dec = 2): string
{
    if ($bytes === 'null' || is_null($bytes)) {
        return '0 B';
    }

    $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    if ($factor == 0) {
        $dec = 0;
    }

    return sprintf("%.{$dec}f %s", $bytes / (1024 ** $factor), $size[$factor]);
}

function zuluToCEST($zuluTime): string
{
    $carbon = Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $zuluTime)
        ->setTimezone('Europe/Berlin');

    if ($carbon->isDST()) {
        $carbon->addHours(2); // Summer time (DST), add 2 hours
    } else {
        $carbon->addHour();   // Winter time (standard time), add 1 hour
    }

    return $carbon->format('Y-m-d H:i:s');
}

function removeTrailingNumbers($string)
{
    // This regex matches a trailing space followed by numbers at the end of the string
    $pattern = '/\s+\d+$/';

    // Replace the matched pattern with an empty string
    return preg_replace($pattern, '', $string);
}

function getCurrentGitBranch()
{
    try {
        $branch = exec('git rev-parse --abbrev-ref HEAD');

        return $branch;
    } catch (Exception $e) {
        // Handle exceptions or errors as needed
        return 'unknown';
    }
}

function checkOpencastLivestreamRoom(string $opencastLocation): ?Livestream
{
    //find the exact livestream with the given opencast location name
    //use squish to remove any empty chars from opencast agent till the bug in opencast api is fixed
    return Livestream::where('opencast_location_name', '=', Str::squish($opencastLocation))->get()->first();
}

function removeHtmlElements(?string $text): string
{
    if (is_null($text)) {
        return '';
    }
    $decodedText = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $decodedText = html_entity_decode($decodedText, ENT_QUOTES, 'UTF-8');

    // Ensure any remaining entities are decoded
    $plainText = strip_tags($decodedText);

    return html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');
}

function check_valid_statistic_insert($numIP, $requestMethod, $userAgent): bool
{
    if (is_null($numIP) || is_null($requestMethod) || is_null($userAgent)) {
        return false;
    }

    return match (true) {
        $requestMethod !== 'POST' => false,
        str_contains(strtolower($userAgent), 'bot') => false,
        default => true,
    };
}
