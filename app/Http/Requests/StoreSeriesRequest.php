<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StoreSeriesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isModerator() || auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => ['required'],
            'description' => ['string', 'nullable'],
            'organization_id' => ['required', 'integer'],
            'presenters' => ['array'],
            'presenters.*' => ['integer', 'nullable'],
            'slug' => ['required'],
            'password' => ['nullable', Password::min(8)->mixedCase()],
            'is_public' => ['boolean'],
            'allow_comments' => ['boolean'],
            'image_id' => ['required', 'exists:App\Models\Image,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $setting = Setting::portal();
        $settingData = $setting->data;
        $this->merge([
            'slug' => Str::slug($this->title),
            'is_public' => $this->is_public === 'on',
            'allow_comments' => $this->allow_comments === 'on',
            'presenters' => $this->presenters = $this->presenters ?? [], //set empty array if presenters array is empty
            'image_id' => (isset($this->image_id)) ? $this->image_id : $settingData['default_image_id'],
        ]);
    }
}
