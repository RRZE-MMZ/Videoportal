<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StoreClipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create-clips', $this->route('clip'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required'],
            'description' => ['string', 'nullable'],
            'recording_date' => ['required', 'date'],
            'organization_id' => ['required', 'integer'],
            'slug' => ['required'],
            'semester_id' => ['required', 'integer'],
            'language_id' => ['required', 'integer'],
            'context_id' => ['nullable', 'integer'],
            'format_id' => ['nullable', 'integer'],
            'type_id' => ['nullable',  'integer'],
            'presenters' => ['array'],
            'presenters.*' => ['integer', 'nullable'],
            'tags' => ['array'],
            'tags.*' => ['string', 'nullable'],
            'acls' => ['array'],
            'acls.*' => ['integer', 'nullable'],
            'episode' => ['required', 'integer'],
            'allow_comments' => ['boolean'],
            'password' => ['nullable', Password::min(8)->mixedCase()],
            'is_public' => ['boolean'],
            'image_id' => ['required', 'exists:App\Models\Image,id'],
            'has_time_availability' => ['boolean'],
            'time_availability_start' => ['exclude_if:has_time_availability,false', 'date'],
            'time_availability_end' => [
                'exclude_if:has_time_availability,false',
                'after_or_equal:time_availability_start',
                'date',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $setting = Setting::portal();
        $settingData = $setting->data;

        $this->merge([
            'slug' => Str::slug($this->title),
            'tags' => $this->tags = $this->tags ?? [], // set empty array if select2 tags is empty
            'acls' => $this->acls = $this->acls ?? [], // set empty array if select2 acls is empty
            'presenters' => $this->presenters = $this->presenters ?? [], // set empty array if presenters array is empty
            'allow_comments' => $this->allow_comments === 'on',
            'is_public' => $this->is_public === 'on',
            'image_id' => (isset($this->image_id)) ? $this->image_id : $settingData['default_image_id'],
            'has_time_availability' => $this->has_time_availability === 'on',
            'context_id' => ($this->context_id > 0) ? $this->context_id : null,
            'format_id' => ($this->format_id > 0) ? $this->format_id : null,
            'type_id' => ($this->type_id > 0) ? $this->type_id : null,
        ]);
    }
}
