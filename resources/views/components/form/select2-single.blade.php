<div class="grid grid-cols-{{$columns}}">
    <div class="mb-6 flex content-center items-center">
        <label class="mr-6 block py-2 font-bold text-gray-700 text-md dark:text-white"
               for="{{ $fieldName}}"
        >
            {{$label}}
        </label>
    </div>
    <div class="col-start-{{ $columnStart }} col-end-{{$columnEnd}}">
        <select class="p-2 w-full {{ $selectClass}}
            focus:outline-hidden focus:bg-white focus:border-blue-500"
                name="{{ $fieldName }}"
                style="width: 100%"
        >
            @if($selectedItem == 0 )
                <option value="0" selected> Select a {{ $label }} if there is one</option>
            @endif
            @forelse($items as $item)
                <option value="{{$item->id }}" {{ $isSelected($item->id) ? 'selected' : '' }}>
                    {{$item->name }}
                </option>
            @empty
            @endforelse
        </select>
    </div>

    @error($fieldName)
    <div class="col-span-{{$columns}}">
        <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
    </div>
    @enderror
</div>
