@extends('layouts.backend')

@section('content')
    <div class="flex justify-between border-b border-black text-2xl dark:text-white dark:border-white
                font-normal pb-2">
        <div class="font-semibold">
            {{ __('opencast.backend.processed events') }}
        </div>
    </div>

    <div class="flex py-2">
    </div>
    <form action="{{ route('admin.clips.opencast.transfer', $clip) }}"
          method="POST"
          class="w-3/5 pt-10">
        @csrf
        <div class="mb-8">
            <label class="mb-8 block text-md font-bold text-gray-700 dark:text-white"
                   for="eventID"
            >
                {{ __('common.heading.please select a recording') }}
            </label>

            <select class="w-full border border-gray-400 p-2"
                    type="text"
                    name="eventID"
                    id="listOpencastEvents"
                    required
            >
                @forelse($events as $event)
                    <option value="{{ $event['identifier'] }}">{{ $event['title'].' / '.$event['start'] }}</option>
                @empty
                    <option> {{ __('opencast.backend.no processed events found for this series') }}</option>

                @endforelse
            </select>
            @error('eventID')
            <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex space-x-6">
            <x-button type="submit" class="bg-blue-600 hover:bg-blue-700">
                {{ __('clip.backend.actions.add selected audio/video files to clip') }}
            </x-button>
            <x-back-button :url="route('clips.edit',$clip)"
                           class="bg-green-600 hover:bg-green-700">
                {{ __('common.forms.go back') }}
            </x-back-button>
        </div>

        </div>

    </form>
@endsection
