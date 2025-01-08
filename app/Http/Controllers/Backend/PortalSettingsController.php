<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePortalSettings;
use App\Models\Setting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PortalSettingsController extends Controller
{
    /**
     * Display Portal settings
     */
    public function show(): Application|Factory|View
    {
        $setting = Setting::portal();

        return view('backend.settings.portal', ['setting' => $setting->data]);
    }

    /**
     * Update Portal settings
     *
     * @return RedirectResponse
     */
    public function update(UpdatePortalSettings $request)
    {
        // existing setting
        $setting = Setting::portal();
        $validated = $request->validated();

        $setting->data = $validated;
        $setting->save();

        return to_route('settings.portal.show');
    }
}
