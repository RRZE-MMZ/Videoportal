<div class="grid grid-cols-8">
    <div class="content-center items-center">
        <label class="mr-2 flex py-2 font-bold  dark:text-white text-gray-700 text-md"
               for="{{ $fieldName }}"
        >
            {{$label}}
        </label>
    </div>
    <div class="{{($fullCol)?'col-start-2 col-end-8':'w-full'}}">
        <x-pikaday name="{{$fieldName}}"
                   format="YYYY-MM-DD"
                   value="{{$value}}"
                   class="py-2 px-4 w-full leading-tight text-gray-700 bg-white rounded border-2
                                            border-gray-200 appearance-none focus:outline-hidden focus:bg-white
                                            focus:border-blue-500"
        />
    </div>
    @error($fieldName)
    <div class="col-start-2 col-end-6">
        <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
    </div>
    @enderror
</div>
