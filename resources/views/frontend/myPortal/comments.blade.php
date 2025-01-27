@extends('layouts.myPortal')

@section('myPortalHeader')
    <div class="dark:text-white">
        {{ __('myPortal.comments.You have X comments', ['counter' => $comments->count() ]) }}
    </div>
@endsection

@section('myPortalContent')
    @foreach ($comments->sortDesc() as $comment)
        <div class="border-b-2 border-black font-extrabold dark:text-white dark:border-white">
            For {{ str($comment->commentable_type)->ucfirst() }} : {{ $comment->commentable->title }}
        </div>
        <div class="my-10 flex justify-between">
            <img class="h-10 w-10 flex-none rounded-full"
                 @if(auth()->user()->presenter)
                     src="{{ asset(auth()->user()->presenter->getImageUrl()) }}"
                 @else
                     src="{{ URL::asset('/images/none.jpg') }}"
                 @endif
                 alt="avatar">

            <div class="ml-4 grow">
                <div class="flex items-center">
                    <div class="dark:text-white"> You</div>
                    <div class="ml-2 text-sm italic dark:text-white">{{ $comment->created_at->diffForHumans() }}</div>
                </div>
                <div class="flex flex-col">
                    <div class="mt-2 w-full text-gray-700 dark:text-white">{{ $comment->content }}</div>
                    <div class="flex">
                        <a href="{{ route('frontend.'.str($comment->commentable_type)->plural().'.show',$comment->commentable) }}"
                           class="pt-2 text-red-800 underline hover:text-red-700">
                            <span>Go to {{ $comment->commentable_type }} </span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    @endforeach
@endsection
