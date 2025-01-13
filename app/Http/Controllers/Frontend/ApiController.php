<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiRequest;
use App\Models\Clip;
use App\Models\Image;
use App\Models\Organization;
use App\Models\Presenter;
use App\Models\Role;
use App\Models\Stats\AssetViewLog;
use App\Models\Tag;
use App\Models\User;
use http\Env\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ApiController extends Controller
{
    public function clips(ApiRequest $request): JsonResponse
    {
        $clips = Clip::search($request->validated(['query']))->get();

        return response()->json($clips->map(function ($clip) {
            return [
                'id' => $clip->id,
                'name' => $clip->title,
            ];
        }));
    }

    /**
     * Tags json response for select2 component
     */
    public function tags(ApiRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return response()->json(
            Tag::select(['id', 'name'])
                ->search($validated['query'])
                ->get(),
        );
    }

    /**
     * Tags json response for select2 component
     */
    public function roles(ApiRequest $request): JsonResponse
    {
        $this->authorize('administrate-superadmin-portal-pages');
        $validated = $request->validated();

        return response()->json(
            Role::select(['id', 'name'])
                ->search($validated['query'])
                ->get(),
        );
    }

    /**
     * Organizations json response for select2 component
     */
    public function organizations(ApiRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return response()->json(
            Organization::select(['org_id as id', 'name'])
                ->search($validated['query'])
                ->get(),
        );
    }

    /**
     * Presenters json response for select2 component
     */
    public function presenters(ApiRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $presenters = Presenter::search($validated['query'])->get();

        $names = $presenters->map(function ($presenter) {
            return [
                'id' => $presenter->id,
                'name' => $presenter->getFullNameAttribute(),
            ];
        });

        return response()->json($names);
    }

    /**
     * Presenters json response for select2 component
     *
     *
     * @throws AuthorizationException
     */
    public function users(ApiRequest $request): JsonResponse
    {
        $this->authorize('dashboard', auth()->user());

        $validated = $request->validated();

        $users = User::search($validated['query'])->moderators()->get();

        $names = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => "{$user->getFullNameAttribute()}/".str()->mask($user->username, '*', 3),
            ];
        });

        return response()->json($names);
    }

    public function images(ApiRequest $request): JsonResponse
    {
        $this->authorize('dashboard', auth()->user());

        $validated = $request->validated();

        $images = Image::search($validated['query'])->get();

        return response()->json($images->map(function ($image) {
            return [
                'id' => $image->id,
                'name' => $image->file_name,
            ];
        }));
    }

    public function logPlayEvent(Request $request): JsonResponse
    {
        if (! $request->isJson()) {
            return abort(404);
        }

        $remote_addr = array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : '';
        if (empty($remote_addr)) {
            return abort(404);
        }

        // get the assetID and clip acls from html page,
        // instead of using always eloquent queries to find the data for each request
        $validated = $request->validate([
            'mediaID' => 'required|integer',
            'serviceIDs' => 'required|array',
        ]);

        $requestMethod = array_key_exists('REQUEST_METHOD', $_SERVER) ? $_SERVER['REQUEST_METHOD'] : '';
        $userAgent = array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $ipString = @getenv('HTTP_X_FORWARDED_FOR');
        $addr = explode(',', $ipString);
        $ip_client = $addr[0] ?? '';
        $ip_addr = ($ip_client != '') ? $ip_client : $remote_addr;
        $ip_byte = explode('.', $ip_addr);
        $ip_num =
            (16777216 * (int) $ip_byte[0]) + (65536 * (int) $ip_byte[1]) + (256 * (int) $ip_byte[2]) + ((int) $ip_byte[3]);
        if (check_valid_statistic_insert($ip_num, $requestMethod, $userAgent)) {
            AssetViewLog::create([
                'resource_id' => $validated['mediaID'],
                'service_id' => collect($validated['serviceIDs'])->contains(1) ? 2 : 6,
                'access_date' => Carbon::now()->format('Y-m-d'),
                'access_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'remote_addr' => $remote_addr,
                'remote_host' => $userAgent,
                'remote_user' => $requestMethod,
                'script_name' => array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : '',
                'is_counted' => '1',
                'is_valid' => '1',
                'referer' => array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : '',
                'query' => array_key_exists('QUERY_STRING', $_SERVER) ? $_SERVER['QUERY_STRING'] : '',
                'is_akami' => '0',
                'in_range' => '0',
                'server' => env('APP_ENV'),
                'range' => $_SERVER['HTTP_RANGE'] ?? '',
                'response' => '200 OK',
                'real_ip' => $ip_addr,
                'num_ip' => $ip_num,
                'is_bot' => '0',
                'is_get' => '0',
            ]);

            return response()->json();
        } else {
            return response()->json('Not valid IP address', 400);
        }
    }
}
