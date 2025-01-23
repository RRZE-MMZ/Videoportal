<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;

class ShowSeriesController extends Controller
{
    public function index(): View
    {
        return view('frontend.series.index');
    }

    /**
     * Series public page
     *
     * @throws AuthorizationException
     */
    public function show(Series $series): View
    {
        $this->authorize('view-series', $series);
        $assetsResolutions = [
            0 => 'SD',
            1 => 'HD',
            2 => 'QHD',
            3 => 'Audio',
        ];

        return view('frontend.series.show', compact(['series']));
    }
}
