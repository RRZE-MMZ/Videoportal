<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVideoWorkflowSettings;
use App\Models\Setting;
use App\Services\OpencastService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class VideoWorkflowSettingsController extends Controller
{
    /**
     * Display Opencast settings
     */
    public function show(): Application|Factory|View
    {
        $setting = Setting::opencast();

        return view('backend.settings.workflow', ['setting' => $setting->data]);
    }

    /**
     * Update Opencast settings
     */
    public function update(UpdateVideoWorkflowSettings $request): RedirectResponse
    {

        $setting = Setting::opencast();

        $setting->data = $request->validated();
        $setting->save();

        return to_route('settings.workflow.show');
    }

    public function fetchAndSaveOpencastThemes(OpencastService $opencastService)
    {
        $setting = Setting::opencast();
        $settings = $setting->data;

        // Initialize available_themes as an empty array if it's not set
        $settings['available_themes'] = [];

        // Fetch themes and append each theme to the available_themes array
        $opencastThemes = $opencastService->getThemes();
        $opencastThemes->each(function ($theme) use (&$settings) {
            $settings['available_themes'][] = [
                'id' => $theme['id'],
                'name' => $theme['name'],
                'watermarkPosition' => $theme['watermarkPosition'],
            ];
        });

        // Save the updated settings back to the database
        $setting->data = $settings;
        $setting->save();

        return to_route('settings.workflow.show');
    }
}
