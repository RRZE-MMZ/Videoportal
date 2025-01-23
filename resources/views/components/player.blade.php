@if($clip->is_livestream)
    <mediaPlayer
            id="target"
            src="{{ $defaultVideoUrl }}"
            streamType="live"
            title="{{ $clip->title }}"
    >
    </mediaPlayer>
@elseif($wowzaStatus->contains('pass') && !empty($defaultVideoUrl))
    <mediaPlayer id="target"

                 src="{{ $defaultVideoUrl }}"
                 title="{{ $clip->title }}"
                 streamType="on-demand"
                 mediaID="{{ $clip->latestAsset()->id  }}"
                 serviceIDs="{{ $clip->acls->pluck('id') }}"
                 poster="{{ fetchClipPoster($clip->latestAsset()?->player_preview)  }}"
    >
        @if($captionAsset = $clip->getCaptionAsset())
            <track
                    id="de-track"
                    kind="captions"
                    label="DE"
                    src="{{getProtectedUrl($captionAsset->path)}}"
                    srclang="de"
                    default
            />
        @endisset
    </mediaPlayer>
@elseif(empty($defaultVideoUrl))
    <div class="flex items-center justify-center text-white dark:text-white">
        <div class="flex flex-col">
            <div class="flex items-center m-4 p-4 border bg-green-700 dark:bg-gray-400 rounded-lg">
                <div>
                    <x-iconoir-info-circle class="w-6 h-6" />
                </div>
                <div class="flex flex-col w-full">
                    <span class="text-3xl pl-4">
                        {!!
                            __('clip.frontend.clip still without assets warning', [
                                    'mail_to' => 'mailto:'.env('SUPPORT_MAIL_ADDRESS'),
                                    'mail_address' => env('SUPPORT_MAIL_ADDRESS')
                                       ])
                       !!}
                    </span>
                </div>
            </div>
        </div>
    </div>
@else
    <mediaPlayer id="target"
                 src="{{ $defaultVideoUrl }}"
                 title="{{ $clip->title }}"
                 streamType="on-demand"
                 mediaID="{{ $clip->latestAsset()->id  }}"
                 serviceIDs="{{ $clip->acls->pluck('id') }}"
                 poster="{{ fetchClipPoster($clip->latestAsset()?->player_preview)  }}"
    >
@endif