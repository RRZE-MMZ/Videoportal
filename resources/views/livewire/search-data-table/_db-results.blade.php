@foreach ($searchResults['series'] as $series)
    <tr class="text-sm leading-5 dark:text-white font-normal">
        <td class="w-4/12 px-6 py-4 whitespace-no-wrap">
            <div class="flex items-center">
                <div class="h-12 w-24 shrink-0">
                    <img class="h-12 w-24 "
                         src="{{ ($series->lastPublicClip)
                    ? fetchClipPoster($series->lastPublicClip?->latestAsset()?->player_preview)
                    : "/images/generic_clip_poster_image.png" }}"
                         alt="">
                </div>
                <div class="ml-4">
                    <div>
                        {{ $series->title. ' |'.$semester = 'SEMESTER' .' |  SeriesID:'.$series->id }}
                    </div>
                </div>
            </div>
        </td>
        <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
            <div class="flex items-center">
                <div class="ml-4">
                    <div>
                        {{ $series->fetchClipsSemester()  }}
                    </div>
                </div>
            </div>
        </td>
        <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
            <div class="flex items-center">
                <div class="ml-4">
                    <div>
                        <div class="flex items-center justify-content-between">
                            <div class="pr-2">
                                @if($seriesAcls = $series->getSeriesACLSUpdated())
                                    @if($seriesAcls!== 'public')
                                        <div class="flex items-center justify-content-between">
                                            <div class="pr-2">
                                                @if($series->checkClipAcls($series->clips))
                                                    <x-heroicon-o-lock-open class="w-4 h-4 text-green-500" />
                                                    <span class="sr-only">Unlock clip</span>
                                                @else
                                                    <x-heroicon-o-lock-closed class="w-4 h-4 text-red-700" />
                                                    <span class="sr-only">Lock clip</span>
                                                @endif
                                            </div>
                                            <div class="text-sm">
                                                <p class="italic text-gray-900">
                                                    {{ $seriesAcls}}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <div class="text-sm">
                                <p class="italic text-gray-900">
                                    {{ $seriesAcls}}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </td>
        <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
            <div class="flex items-center">
                <div class="ml-4">
                    <div>
                        {{ ($series->organization->name)}}
                    </div>
                </div>
            </div>
        </td>
        <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
            <div class="flex items-center">
                <div class="ml-4">
                    <div>
                        @if($series->presenters->isNotEmpty())
                            <div class="flex items-center">
                                <div class="flex pr-2 items-center">
                                    <div class="pr-2">
                                        <x-heroicon-o-user class="h-4" />
                                    </div>
                                    <div class="flex items-center align-middle">
                                        {{ $series->presenters
                                      ->map(function($presenter){
                                          return $presenter->getFullNameAttribute();
                                      })->implode(',') }}
                                    </div>

                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </td>
        <td class="w-2/12 px-6 py-4 text-right text-sm font-medium leading-5 whitespace-no-wrap">
            <div class="flex space-x-2">
                <a href="{{route('series.edit', $series->slug)}}">
                    <x-button type="button" class="bg-green-600 hover:bg-green-700">
                        {{__('common.actions.edit')}}
                    </x-button>
                </a>
            </div>
        </td>
    </tr>
@endforeach
