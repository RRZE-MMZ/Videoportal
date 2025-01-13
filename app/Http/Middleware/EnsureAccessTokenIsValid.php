<?php

namespace App\Http\Middleware;

use App\Enums\Acl;
use Closure;
use Illuminate\Http\Request;

class EnsureAccessTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     *
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // this should either be a series (course term is for backward compatibility with old LMS links) or a clip
        $objType = getUrlTokenType((string) $request->segment('3'));
        $objID = (int) $request->segment('4');
        $urlToken = $request->segment('5');
        $tokenTime = $request->segment('6');
        $tokenClient = getUrlClientType((string) $request->segment('7'));

        $model = "App\Models\\".ucfirst($objType);

        $obj = $model::findOrFail($objID);

        if (! $obj->acls()->pluck('id')->contains(Acl::LMS())) {
            return $next($request);
        }

        $objToken = getAccessToken($obj, $tokenTime, $tokenClient, false);

        if ($urlToken !== $objToken) {
            abort(403);
        }

        return $next($request);
    }
}
