@php use App\Enums\Role; @endphp
<div class="grid grid-cols-8">
    <div class="mb-6 flex content-center items-center">
        <label class="mr-6 block py-2 font-bold text-gray-700 text-md dark:text-white"
               for="{{ $fieldName}}"
        >
            {{$label}}
        </label>
    </div>
    <div class="col-start-2 col-end-6">
        <select class="px-4 py-4 h-4 w-full  {{ $selectClass}}
            focus:outline-hidden focus:bg-white focus:border-blue-500"
                name="{{ $fieldName }}[]"
                multiple="multiple"
                style="width: 100%"
        >
            @if($fieldName == 'acls')
                @forelse($items as $item)
                    <option value="{{ $item->id }}"
                    @if($model?->acls->contains($item->id))
                        {{'selected'}}
                        @endif
                    >{{ $item->name }}</option>
                @empty
                    <option value="1"></option>
                @endforelse
            @elseif($fieldName=='presenters' || $fieldName =='guests' || $fieldName =='hosts')
                @foreach($items as $item)
                    <option value="{{$item->id }}" selected
                            class="h-4 p-4">{{$item->getFullNameAttribute() }}</option>
                @endforeach
            @elseif($fieldName=='roles')
                @forelse($items as $item)
                    <option value="{{ $item->id }}"
                    @if($model?->roles->contains($item->id))
                        {{'selected'}}
                        @endif
                    >{{ $item->name }}
                    </option>
                @empty
                    <option value="{{ Role::USER }}"> {{ Role::USER->lower() }}</option>
                @endforelse
            @else
                @foreach($items as $item)
                    <option value="{{$item->name }}" selected>{{$item->name }}</option>
                @endforeach
            @endif
        </select>
    </div>

    @error($fieldName)
    <div class="col-span-8">
        <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
    </div>
    @enderror
</div>


