<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UpdateSeriesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('update-series', $this->route('series'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['string', 'nullable'],
            'organization_id' => ['required ', 'integer'],
            'slug' => ['required'],
            'presenters' => ['array'],
            'presenters.*' => ['integer', 'nullable'],
            'opencast_series_id' => ['null', 'uuid'],
            'password' => ['nullable', Password::min(8)->mixedCase()],
            'is_public' => ['boolean'],
            'allow_comments' => ['boolean'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'slug' => Str::slug($this->title),
            'is_public' => $this->is_public === 'on',
            'allow_comments' => $this->allow_comments === 'on',
            'presenters' => $this->presenters = $this->presenters ?? [], // set empty array if select2 presenters is empty
        ]);
    }
}
