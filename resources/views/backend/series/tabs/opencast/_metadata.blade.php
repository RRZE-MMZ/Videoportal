@php use App\Models\Semester; @endphp
@can('administrate-assistant-pages')
    <div id="opencast-metadata" class="flex flex-col font-normal">
        <h4 class="mb-4 text-green-700 dark:text-green-400">
        <span class="text-xl font-bold">
            {{ __('opencast.backend.Opencast metadata') }}
        </span>
            <span class="text-sm italic">
            {{ __('series.common.created at') }} {{ zuluToCEST($opencastSeriesInfo['metadata']['created']) }}
        </span>
        </h4>
        <div class="dark:text-white">
        <span class="font-bold pr-4">
          {{ __('opencast.common.title') }} :
        </span>
            <span class="italic">
            {{ $series->title }}
        </span>
        </div>
        <div class="dark:text-white">
    <span class="font-bold pr-4">
                {{ __('opencast.common.subject') }} :
            </span>
            <span class="italic">
            @if($series->clips()->count() > 0)
                    {{ $series->latestClip->semester->name }}
                @else
                    {{ Semester::current()->first()->name }}
                @endif
        </span>
        </div>
        <div class="dark:text-white">
          <span class="font-bold pr-4">
            {{ __('opencast.common.presenter') }} :
        </span>
            <span class="italic">
            {{ $series->presenters->first()?->getFullNameAttribute() }}
        </span>
        </div>
        <div class="dark:text-white">
        <span class="font-bold pr-4">
            {{ __('opencast.common.contributor') }} :
        </span>
            <span class="italic">
            {{ $series->organization->name }}
        </span>
        </div>
    </div>
@endcan