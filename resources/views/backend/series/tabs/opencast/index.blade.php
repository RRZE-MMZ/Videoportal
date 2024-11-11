@use('App\Enums\OpencastWorkflowState')
<div class="py-4">
    @if($opencastSettings->data['enable_themes_support'])
        @php
            $themes = collect($opencastSettings->data["available_themes"]);
            $themesCollection = $themes->pluck("id", "name");
        @endphp
        @include('backend.series.tabs.opencast._themes-actions', [
        'facultiesWithPositions' =>
        getUniqueFacultiesWithPositionsFromOpencastThemes($themesCollection,'Intro-Outro'),
        'themeID' => $opencastSeriesInfo->get('theme')->keys()->first() //get the default themeID for the form
])
    @endif
    @if($opencastSeriesInfo->get('metadata')?->isNotEmpty())
        @if($opencastSeriesInfo->get('upcoming')->isNotEmpty() > 0)
            @include('backend.series.tabs.opencast._actions')
        @endif
        @include('backend.series.tabs.opencast._metadata')
        @include('backend.series.tabs.opencast._editors')
        @include('backend.dashboard._opencast-workflows',[
                    'opencastEvents' => $opencastSeriesInfo])
    @else
        @include('backend.series.tabs.opencast._create-series-button')
    @endif
</div>
