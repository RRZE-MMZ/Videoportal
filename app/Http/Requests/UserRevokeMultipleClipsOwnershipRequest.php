<?php

namespace App\Http\Requests;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRevokeMultipleClipsOwnershipRequest extends FormRequest
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
            'clip_ids' => ['required', 'array'],
            'userID' => ['required', 'integer', Rule::exists('users', 'id'), function ($attibute, $id, $fail) {
                if ($this->user->id === $id) {
                    $fail('New owner is the same with the old owner');
                }
                if (! User::find($id)->hasRole(Role::MODERATOR)) {
                    $fail('The given userID does not have a moderator role');
                }
            }],
            'clip_ids.*' => [
                'integer',
                'exists:clips,id',
                function ($attribute, $value, $fail) {
                    $clipExists = $this->user->clips()->whereId($value)->exists();
                    if (! $clipExists) {
                        $fail("The clip ID {$value} does not belong to the series {$this->series->title}.");
                    }
                }],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->user = $this->route('user');
        if (is_string($this->clip_ids)) {
            $this->merge(['clip_ids' => json_decode($this->clip_ids, true)]);
        }
    }
}
