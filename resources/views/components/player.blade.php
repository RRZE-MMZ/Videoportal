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