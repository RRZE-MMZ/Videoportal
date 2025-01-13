<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\Clip;
use App\Services\OpenSearchService;
use Illuminate\View\View;

class ShowSearchResultsController extends Controller
{
    /**
     * Main and basic __invoke using ORM
     */
    public function __invoke(
        SearchRequest $request,
        OpenSearchService $openSearchService,
    ): View {
        $searchResults = collect();
        $health = $openSearchService->getHealth();

        // check whether OpenSearch server is up and running
        if ($health->contains('pass')) {
            $results = [];

            $filters = [];
            if (! auth()->check() || auth()->user()->cannot('administrate-admin-portal-pages')) {
                $filters['is_public'] = 'true';
                if ($request->series) {
                    $filters['has_last_public_clip'] = 'true';
                }
            }

            // keep this order to pass testing search result
            if ($request->clips) {
                $results['clips'] = $openSearchService->searchIndexes('tides_clips', $request->term, $filters);
                $results['clips']['counter'] = ($results['clips']->isNotEmpty())
                    ? $results['clips']['hits']['total']['value'] : [];
                $searchResults =
                    $searchResults->put('clips', $results['clips'])
                        ->put('clips_counter', $results['clips']['counter']);
            }

            if ($request->series) {
                $results['series'] = $openSearchService->searchIndexes('tides_series', $request->term, $filters);
                $results['series']['counter'] = ($results['series']->isNotEmpty())
                    ? $results['series']['hits']['total']['value'] : [];
                $searchResults =
                    $searchResults->put('series', $results['series'])
                        ->put('series_counter', $results['series']['counter']);
            }

            $searchResults = $searchResults->put('searchTerm', $request->term);

            return view('frontend.search.results.opensearch.index', compact('searchResults'));
        } else { // use slow db __invoke if no OpenSearch node is found
            $clips = Clip::with('presenters', 'assets')
                ->search($request->term)
                ->whereHas('assets')
                ->orWhereHas('presenters', function ($q) use ($request) {
                    $q->whereRaw('lower(first_name)  like (?)', ["%{$request->term}%"])
                        ->orWhereRaw('lower(last_name)  like (?)', ["%{$request->term}%"]);
                }) // __invoke for clip presenter
                ->orWhereHas('owner', function ($q) use ($request) {
                    $q->whereRaw('lower(first_name)  like (?)', ["%{$request->term}%"])
                        ->orWhereRaw('lower(last_name)  like (?)', ["%{$request->term}%"]);
                }) // __invoke for clip presenter
                ->orderByDesc('recording_date')
                ->paginate(10)
                ->withQueryString();

            $searchResults->put('clips', $clips);
            $searchResults = $searchResults->put('searchTerm', $request->term);

            return view('frontend.search.results.dbsearch', compact('searchResults'));
        }
    }
}
