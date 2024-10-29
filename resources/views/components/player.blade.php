@php use Barryvdh\Debugbar\Facades\Debugbar;use Barryvdh\Debugbar\Twig\Extension\Debug; @endphp

@if($clip->is_livestream)
    <mediaPlayer
            id="target"
            src="{{ $defaultVideoUrl }}"
            streamType="live"
            title="{{ $clip->title }}"
    >
    </mediaPlayer>
@elseif(Illuminate\Support\Facades\Storage::disk('streamable_videos')
                                            ->exists($clip->assets()->first()->id.'.m3u8'))
    <video id="target" class="w-full" playsinline controls
           data-poster="{{ fetchClipPoster($clip->latestAsset()?->player_preview)  }}">
        <source src="{{ '/streamable_videos/'.$clip->assets()->first()->id . '.m3u8'  }}"
        />
    </video>
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
@else
    <video id="target" class="w-full" playsinline controls
           data-poster="{{ fetchClipPoster($clip->latestAsset()?->player_preview)  }}">
        <source src="{{ '/videos/'.$clip->assets()->first()->path  }}"
                type="video/mp4"
        />
    </video>
@endif