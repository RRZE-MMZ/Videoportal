<?php

namespace App\Http\Requests;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRevokeMultipleSeriesOwnershipRequest extends FormRequest
{
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'series_ids' => ['required', 'array'],
            'userID' => ['required', 'integer', Rule::exists('users', 'id'), function ($attibute, $id, $fail) {
                if ($this->user->id === $id) {
                    $fail('New owner is the same with the old owner');
                }
                if (! User::find($id)->hasRole(Role::MODERATOR)) {
                    $fail('The given userID does not have a moderator role');
                }
            }],
            'series_ids.*' => [
                'integer',
                'exists:series,id',
                function ($attribute, $value, $fail) {
                    $seriesExists = $this->user->series()->whereId($value)->exists();
                    if (! $seriesExists) {
                        $fail("The series ID {$value} does not owned by {$this->user->getFullNameAttribute()}.");
                    }
                }],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->user = $this->route('user');
        if (is_string($this->series_ids)) {
            $this->merge(['series_ids' => json_decode($this->series_ids, true)]);
        }
    }
}
