<div class="mb-2 space-y-5 font-normal" id="comments-section">
    <x-message
            :messageText="$messageText"
            :messageType="$messageType" />

    <form wire:submit="postComment" action="#" method="PATCH" class="my-12 w-full">
        @csrf
        <div class="flex">

            <img class="h-10 w-10 rounded-full"
                 @if(auth()->user()->presenter)
                     src="{{ asset(auth()->user()->presenter->getImageUrl()) }}"
                 @else
                     src="{{ URL::asset('/images/none.jpg') }}"
                 @endif
                 alt="avatar">
            <div class="ml-4 flex-1">
                <textarea wire:model="content"
                          name="content"
                          id="content"
                          rows="4"
                          placeholder="{{ __('comment.common.type your comment here') }}"
                          class=" w-full rounded-md border px-4 py-2 shadow dark:text-black text-sm"
                ></textarea>

                @error('content')
                <p class="mt-1 text-red-500">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-base leading-6
                                font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none
                                focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition
                                 ease-in-out duration-150 mt-2 disabled:opacity-50">
                    <svg wire:loading wire:target="postComment"
                         class="mr-3 -ml-1 h-5 w-5 animate-spin text-white"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                        ></circle>
                        <path class="opacity-75"
                              fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962
                                    7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>
                        {{ __('comment.common.actions.Post comment') }}
                    </span>
                </button>

            </div>
        </div>
    </form>

    <div wire:poll.10s="fetchComments">
        @if($comments->count() > 0)
            @foreach ($comments->sortDesc() as $comment)
                <div class="my-10 flex justify-between">
                    <img class="h-10 w-10 flex-none rounded-full"
                         @if($comment->owner->presenter)
                             src="{{ asset($comment->owner->presenter->getImageUrl()) }}"
                         @else
                             src="{{ URL::asset('/images/none.jpg') }}"
                         @endif
                         alt="avatar">
                    <div class="ml-4 flex-grow">
                        <div class="flex items-center dark:text-white">
                            <div class="font-semibold">
                                {{ $comment->owner->getFullNameAttribute() }}
                            </div>
                            <div
                                    class="ml-2 text-gray-500 dark:text-gray-50">
                                {{ $comment->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <div
                                    class="mt-2 w-full text-gray-700 dark:text-gray-50 text-sm">
                                {{ $comment->content }}
                            </div>
                            @can('delete-comment', $comment)
                                <div class="flex">
                                    <a href="#comments-section"
                                       class="pt-2 text-sm text-red-800 dark:text-red-500 underline hover:text-red-700"
                                       wire:click="deleteComment({{ $comment }})">
                                        <span>
                                            {{ __('common.actions.delete') }}
                                        </span>
                                    </a>
                                </div>
                            @endcan
                        </div>

                    </div>
                </div>
            @endforeach
        @endif
    </div>

</div>
