@use(App\Enums\OpencastWorkflowState)
<div class="flex items-center justify-center text-white dark:text-white">
    <div class="flex flex-col">
        @if($transcodingVideoUrl)
            <div class="flex flex-col space-y-4">
                <div class="flex flex-col content-center justify-center pt-4 sm:pt-6">
                    <div class="w-full max-w-6xl mx-auto">
                        <mediaPlayer id="target"
                                     src="{{ $transcodingVideoUrl }}"
                                     title="{{ $clip->title }}"
                                     streamType="on-demand"
                                     poster="{{ fetchClipPoster($clip->latestAsset()?->player_preview)  }}"
                        >
                        </mediaPlayer>
                    </div>
                </div>
            </div>
        @else
            @can('edit-clips', $clip)
                <div class="flex items-center m-4 p-4 border bg-indigo-700 dark:bg-gray-400 rounded-lg">
                    <div>
                        <x-iconoir-info-circle class="w-6 h-6" />
                    </div>
                    <div>
                    <span class="text-3xl pl-4">
                        {{ __('clip.frontend.clip has no assets warning') }}
                    </span>
                    </div>
                </div>
            @else
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
            @endcan
        @endif
    </div>
</div>