<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateVideoWorkflowSettings extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('administrate-superadmin-portal-pages');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'url' => ['required', 'url'],
            'username' => ['required', 'string'],
            'password' => ['required', Password::min(6)],
            'default_workflow_id' => ['required', 'string'],
            'upload_workflow_id' => ['required', 'string'],
            'archive_path' => ['required', 'string'],
            'assistants_group_name' => ['required', 'string'],
            'opencast_purge_end_date' => ['required', 'date'],
            'opencast_purge_events_per_minute' => ['required', 'numeric'],
            'enable_themes_support' => ['required', 'boolean'],
            'available_themes' => ['nullable', 'array'],  // Allow empty array
            'available_themes.*.id' => ['required_with:enable_themes_support', 'numeric'],
            'available_themes.*.name' => ['required_with:enable_themes_support', 'string'],
            'available_themes.*.watermarkPosition' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Only set available_themes to an empty array if it's null or not an array
        if (! $this->has('available_themes') || ! is_array($this->input('available_themes'))) {
            $this->merge([
                'available_themes' => [],
                'enable_themes_support' => $this->enable_themes_support === 'on',
            ]);
        } else {
            $this->merge([
                'enable_themes_support' => $this->enable_themes_support === 'on',
            ]);
        }
    }
}
