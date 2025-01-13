<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AcceptUseTerms
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        // if user doesn't have accepted the use terms then show use terms page
        if (auth()->user()->settings->data['accept_use_terms']) {
            return $next($request);
        } else {
            return \response()->view('frontend.myPortal.useTerms');
        }
    }
}
