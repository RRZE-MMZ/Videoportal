<div class="grid grid-cols-8 items-center">
    <div class="content-center">
        <div class="flex flex-col">
            <label class="mr-2 flex py-2 font-bold  dark:text-white text-gray-700 text-md"
                   for="{{ $fieldName }}"
            >
            <span>
                {{$label}}
            </span>
            </label>
            @if ($disabled)
                <div class="flex text-xs text-green-500 italic">
                    {{ __('common.forms.you cannot change this field') }}
                </div>
            @endif
        </div>
    </div>
    <div class="{{($fullCol)?'col-start-2 col-end-8':'w-full'}}">
        <input class="py-2 px-4 w-full leading-tight  bg-white rounded border-2
                                            border-gray-200 appearance-none focus:outline-none focus:bg-white
                                            focus:border-blue-500 "
               @if($placeholder!=='')
                   placeholder="{{ $placeholder }}"
               @endif
               type="{{ $inputType }}"
               name="{{ $fieldName }}"
               id="{{ $fieldName }}"
               value="{{ $value }}"
                {{($required)?'required':''}}
                {{($readOnly)?'readonly':''}}
                {{($disabled)?'disabled':''}}
        >
    </div>
    @error($fieldName)
    <div class="col-start-2 col-end-6">
        <p class="mt-2 w-full text-red-500 dark:text-red-200 dark:text-white">{{ $message }}</p>
    </div>
    @enderror
</div>
