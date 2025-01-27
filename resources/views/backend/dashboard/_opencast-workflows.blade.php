@use(App\Enums\OpencastWorkflowState)
@use(Carbon\Carbon)

@if(isset($opencastEvents['recording']) && $opencastEvents['recording']->isNotEmpty())
    <div class="flex flex-col py-2">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <caption
                        class="p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 bg-gray-200 dark:text-white
                    dark:bg-gray-800">
                    {{ __('opencast.backend.recording events',['counter' => $opencastEvents['recording']->count()]) }}
                </caption>
                <thead class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-gray-700 dark:text-white">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 ">
                        {{ __('opencast.common.title') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.series') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.presenter') }}
                    </th>

                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.start time') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.location') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3">
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($opencastEvents['recording'] as $event)
                    <tr class="border-b bg-white font-normal text-gray-900 dark:bg-gray-800 dark:text-white">
                        <td class="px-6 py-4 text-sm ">
                            {{ $event['title'] }}
                        </td>
                        <td class="px-6 py-3 text-sm">
                            @if(!empty($event['series']))
                                <div class="flex items-center">
                                    <div>
                                        {{ $event['series']  }}
                                    </div>
                                    @if ( Request::segment(1) === 'admin')
                                        <div class="pl-2">
                                            <a href="{{ route('series.edit', str($event['series'])->after('courseID:'))}}">
                                                <x-heroicon-o-arrow-right-circle class="h-6" />
                                            </a>
                                        </div>
                                    @endif
                                </div>

                            @else
                                {{ 'EVENTS_WITHOUT_SERIES' }}
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm ">
                            @if (isset($event['presenter'][0]))
                                {{ $event['presenter'][0]  }}
                            @else
                                {{ 'No presenter' }}
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm ">
                            {{ zuluToCEST($event['start']) }}
                        </td>
                        <td class="px-6 py-3 text-sm ">
                            {{ $event['location'] }}
                        </td>
                        <td class="px-6 p-3 text-sm text-red-700 m-3">
                            <div class="flex space-x-2">
                                <span class="bg-red-600 inline-flex items-center px-4 py-2 border uppercase
                                        border-transparent font-medium text-base text-white tracking-wider
                                        active:bg-white-900 focus:outline-hidden focus:border-white-900 focus:ring-3
                                        ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                   {{ OpencastWorkflowState::tryFrom($event['status'])->lower() }}
                                </span>
                                @if(Str::contains(url()->current(),'dashboard'))
                                    @if($activeLivestreams->get()->pluck('opencast_location_name')->contains($event['location']))
                                        <span class="bg-gray-600 inline-flex items-center px-4 py-2 border uppercase
                                        border-transparent font-medium text-base text-white tracking-wider
                                        active:bg-white-900 focus:outline-hidden focus:border-white-900 focus:ring-3
                                        ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                                        >
                                                    {{ __('livestream.common.livestream exists') }}
                                                </span>
                                    @else
                                        <a href="">
                                            <form action="{{ route('livestreams.makeReservation') }}" method="POST">
                                                @csrf
                                                <input id="event_{{$loop->index}}"
                                                       type="text"
                                                       name="event_{{$loop->index}}"
                                                       value="{{$event['identifier']}}"
                                                       class="@error('event_'.$loop->index) is-invalid @enderror hidden">
                                                @error('event_'.$loop->index)
                                                <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                                <x-button type="submit" class="bg-blue-600 hover:bg-blue-700">
                                                    {{ __('livestream.common.reserve livestream') }}
                                                </x-button>
                                            </form>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@if(isset($opencastEvents['scheduled']) && $opencastEvents['scheduled']->isNotEmpty())
    <div class="flex flex-col py-2">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full  text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <caption
                        class="p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 bg-gray-200 dark:text-white
                    dark:bg-gray-800">
                    {{ __('opencast.backend.scheduled events', ['counter' => $opencastEvents['scheduled']->count() ]) }}
                </caption>
                <thead class="text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 ">
                        {{ __('opencast.common.title') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.series') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.presenter') }}
                    </th>

                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.start time') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.location') }}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{__('common.status')}}
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($opencastEvents['scheduled'] as $event)
                    <tr class="border-b bg-white font-normal text-gray-900 dark:bg-gray-800 dark:text-white">
                        <td class="px-6 py-3 text-sm">
                            {{ $event['title'] }}
                        </td>
                        <td class="px-6 py-3">
                            @if(!empty($event['series']))
                                {{ $event['series']  }}
                            @else
                                {{ 'EVENTS_WITHOUT_SERIES' }}
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            @if (isset($event['presenter'][0]))
                                {{ $event['presenter'][0]  }}
                            @else
                                {{ 'No presenter' }}
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            {{ zuluToCEST($event['start']) }}
                        </td>
                        <td class="px-6 py-3">
                            {{ $event['location'] }}
                        </td>
                        <td class="px-6 py-3 text-green-700">
                            <span class="bg-green-600 inline-flex items-center px-4 py-2 border uppercase
                                        border-transparent font-medium text-base text-white tracking-wider
                                        active:bg-white-900 focus:outline-hidden focus:border-white-900 focus:ring-3
                                        ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                   {{ OpencastWorkflowState::tryFrom($event['status'])->lower() }}
                                </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if(isset($opencastEvents['trimming']) && $opencastEvents['trimming']->isNotEmpty())
    <div class="flex flex-col py-2">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <caption
                        class="p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 bg-gray-200 dark:text-white
                    dark:bg-gray-800">
                    {{__('opencast.backend.todo events', ['counter' => $opencastEvents['trimming']->count()])}}
                </caption>
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 ">
                        {{ __('opencast.common.title') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.series') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.presenter') }}
                    </th>

                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.start time') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3">
                        {{ __('opencast.common.location') }}
                    </th>
                    <th scope="col" class="px-6 py-3">
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($opencastEvents['trimming'] as $event)
                    <tr class="border-b bg-white font-normal text-gray-900 dark:bg-gray-800 dark:text-white">
                        <td class="px-6 py-3">
                            {{ $event['title'] }}
                        </td>
                        <td class="px-6 py-3">
                            @if(!empty($event['series']))
                                {{ $event['series']['title']  }}
                            @else
                                {{ 'EVENTS_WITHOUT_SERIES' }}
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            @if (isset($event['presenters']) && !empty($event['presenters']))
                                {{ $event['presenters'][0]  }}
                            @else
                                {{ 'No presenter' }}
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            {{ zuluToCEST($event['start_date']) }}
                        </td>
                        <td class="px-6 py-3">
                            {{ $event['location'] }}
                        </td>
                        @can('administrate-admin-portal-pages')
                            <td class="px-6 py-3 text-sm font-light text-green-700">
                                <a href="{{ $opencastSettings['url'].'/editor-ui/index.html?id='.$event['id'] }}">
                                    <x-heroicon-c-scissors class="h-6" />
                                </a>
                            </td>
                        @endcan
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif


@if($opencastEvents['running']->isNotEmpty())
    <div class="flex flex-col py-2">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <caption
                        class="p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 bg-gray-200 dark:text-white
                    dark:bg-gray-800">
                    {{ __('opencast.backend.running events', ['counter' => $opencastEvents['running']->count()]) }}
                </caption>
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        {{__('opencast.common.title')}}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{__('opencast.common.series')}}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{__('opencast.common.recording date')}}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{__('opencast.common.presenter')}}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{__('common.status')}}
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($opencastEvents['running'] as $events)
                    <tr class="border-b bg-white font-normal text-gray-900 dark:bg-gray-800 dark:text-white">
                        <td class="px-6 py-3">
                            {{ $events['title'] }}
                        </td>
                        <td class="px-6 py-3">
                            @if(!empty($events['series']))
                                {{ $events['series']  }}
                            @else
                                {{ 'EVENTS_WITHOUT_SERIES' }}
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            {{ $events['created'] }}
                        </td>
                        <td class="px-6 py-3">
                            @if (isset($events['mediapackage']['creators']))
                                {{ $events['mediapackage']['creators']['creator']  }}
                            @else
                                {{ 'No presenter' }}
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm font-light text-green-700">
                            {{ $events['processing_state'] }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if($opencastEvents['failed']->isNotEmpty())
    <div class="flex flex-col py-2">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <caption
                        class="p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 bg-gray-200 dark:text-white
                    dark:bg-gray-800">
                    {{ __('opencast.backend.failed events', ['counter' => $opencastEvents['failed']->count()]) }}
                </caption>
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        {{ __('opencast.common.title') }}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{  __('opencast.common.series') }}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{ __('opencast.common.recording date') }}
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($opencastEvents['failed'] as $workflow)
                    <tr class="border-b bg-white font-normal text-gray-900 dark:bg-gray-800 dark:text-white">
                        <td class="whitespace-nowrap px-6 py-3">
                            {{ $workflow['title'] }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3 ">
                            {{ $workflow['series'] }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-3 ">
                            {{ $workflow['start'] }}
                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>
        </div>
    </div>
@endif

@if(isset($opencastEvents['upcoming']) && $opencastEvents['upcoming']->isNotEmpty())
    <div class="flex flex-col py-2">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <caption
                        class="p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 bg-gray-200 dark:text-white
                    dark:bg-gray-800">
                    {{ __('opencast.backend.upcoming events', ['counter' => $opencastEvents['upcoming']->count() ]) }}
                </caption>
                <tr>
                    <th scope="col" class="px-6 py-3">
                        {{__('opencast.common.title')}}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{ __('opencast.common.series') }}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{  __('opencast.common.presenter') }}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{ __('opencast.common.start time') }}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{ __('opencast.common.end time') }}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{ __('opencast.common.location') }}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        {{ __('common.status') }}
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($opencastEvents['upcoming'] as $event)
                    <tr class="border-b bg-white font-normal text-gray-900 dark:bg-gray-800 dark:text-white">
                        <td class="px-6 py-3 ">
                            {{ $event['title'] }}
                        </td>
                        <td class="px-6 py-3 ">
                            @if(!empty($event['series']))
                                {{ $event['series']  }}
                            @else
                                {{ 'EVENTS_WITHOUT_SERIES' }}
                            @endif
                        </td>
                        <td class="px-6 py-3 ">
                            @if (isset($event['presenter'][0]))
                                {{ $event['presenter'][0]  }}
                            @else
                                {{ 'No presenter' }}
                            @endif
                        </td>
                        <td class="px-6 py-3 ">
                            {{ zuluToCEST($event['scheduling']['start']) }}
                        </td>
                        <td class="px-6 py-3 ">
                            {{ zuluToCEST($event['scheduling']['end']) }}
                        </td>
                        <td class="px-6 py-3 ">
                            {{ $event['location'] }}
                        </td>
                        <td class="px-6 py-3 text-sm font-light text-green-300">
                            {{ OpencastWorkflowState::tryFrom($event['status'])->lower() }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

