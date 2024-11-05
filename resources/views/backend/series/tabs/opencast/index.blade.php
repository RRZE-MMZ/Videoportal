@use('App\Enums\OpencastWorkflowState')
<div class="py-4">
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
    @include('backend.series.tabs.opencast._themes-actions')
</div>
