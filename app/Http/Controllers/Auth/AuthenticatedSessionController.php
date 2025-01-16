<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Setting;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Slides\Saml2\Models\Tenant;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View|Factory|Application
    {
        $saml2TenantUUID = (Tenant::count() > 0) ? Tenant::all()->first()->uuid : null;

        return view('auth.select-login', ['saml2TenantUUID' => $saml2TenantUUID]);
    }

    /**
     * Handle an incoming authentication request.
     *
     *
     * @throws ValidationException
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        Setting::firstOrCreate(
            ['name' => auth()->user()->username],
            [
                'data' => config('settings.user'),
            ]
        );
        $lang = auth()->user()->settings->data['language'];
        auth()->user()->logged_in_at = Carbon::now();
        auth()->user()->save();
        $request->session()->put('locale', $lang);

        if (session()->has('url.intended')) {
            return redirect()->intended(session('url.intended'));
        } else {
            return redirect()->intended(RouteServiceProvider::HOME);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        auth()->user()->last_visited_at = Carbon::now();
        auth()->user()->save();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
