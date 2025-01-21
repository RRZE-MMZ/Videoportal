<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'alpha', 'min:2', 'max:30'],
            'last_name' => ['required', 'alpha', 'min:2', 'max:100'],
            'username' => ['required', 'string', 'max:255', 'unique:users', 'starts_with:local.'],
            'email' => ['email', 'unique:users'],
        ];
    }
}
