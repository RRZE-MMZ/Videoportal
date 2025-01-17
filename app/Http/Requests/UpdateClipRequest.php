<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UpdateClipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('edit-clips', $this->route('clip'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['string', 'nullable'],
            'recording_date' => ['required', 'date'],
            'organization_id' => ['required', 'integer'],
            'slug' => ['required'],
            'semester_id' => ['required', 'integer'],
            'language_id' => ['required', 'integer'],
            'context_id' => ['nullable', 'integer'],
            'format_id' => ['nullable', 'integer'],
            'type_id' => ['nullable', 'integer'],
            'chapter_id' => ['integer', 'nullable', function (string $attribute, mixed $value, Closure $fail) {
                if ($this->route('clip')->series->chapters->pluck('id')->doesntContain($value)) {
                    $fail("The {$attribute} id doesn't belong to the series.");
                }
            }, ], // check whether the chapter id belongs to user series chapters
            'presenters' => ['array'],
            'presenters.*' => ['integer', 'nullable'],
            'tags' => ['array'],
            'opencast_event_id' => ['sometimes', 'uuid'],
            'tags.*' => ['string', 'nullable'],
            'acls' => ['array'],
            'acls.*' => ['integer', 'nullable'],
            'episode' => ['required', 'integer'],
            'allow_comments' => ['boolean'],
            'password' => ['nullable', Password::min(8)->mixedCase()],
            'is_public' => ['boolean'],
            'is_livestream' => ['boolean'],
            'has_time_availability' => ['boolean'],
            'time_availability_start' => ['exclude_if:has_time_availability,false', 'date'],
            'time_availability_end' => [
                'exclude_if:has_time_availability,false',
                'nullable',
                'after_or_equal:time_availability_start',
                'date',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->title),
            'tags' => $this->tags = $this->tags ?? [], // set empty array if select2 tags is empty
            'acls' => $this->acls = $this->acls ?? [], // set empty array if select2 acls is empty
            'presenters' => $this->presenters = $this->presenters ?? [], // set empty array if select2 is empty
            'allow_comments' => $this->allow_comments === 'on',
            'is_public' => $this->is_public === 'on',
            'is_livestream' => $this->is_livestream === 'on',
            'has_time_availability' => $this->has_time_availability === 'on',
        ]);
    }
}
