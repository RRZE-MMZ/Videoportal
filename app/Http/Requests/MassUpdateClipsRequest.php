<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class MassUpdateClipsRequest extends FormRequest
{
    protected $series;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit-series', $this->series);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required'],
            'slug' => ['required'],
            'semester_id' => ['required', 'integer'],
            'organization_id' => ['required', 'integer'],
            'language_id' => ['required', 'integer'],
            'context_id' => ['required', 'integer'],
            'format_id' => ['required', 'integer'],
            'type_id' => ['required', 'integer'],
            'presenters' => ['array'],
            'presenters.*' => ['integer', 'nullable'],
            'tags' => ['array'],
            'tags.*' => ['string', 'nullable'],
            'acls' => ['array'],
            'acls.*' => ['integer', 'nullable'],
            'password' => ['nullable', Password::min(8)->mixedCase()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->series = $this->route('series');
        $this->merge([
            'slug' => Str::slug($this->title),
            'tags' => $this->tags = $this->tags ?? [], // set empty array if select2 tags is empty
            'acls' => $this->acls = $this->acls ?? [], // set empty array if select2 acls is empty
            'presenters' => $this->presenters = $this->presenters ?? [], // set empty array if select2 is empty
        ]);
    }
}
