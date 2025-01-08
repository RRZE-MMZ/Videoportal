<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePortalSettings extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(Role::SUPERADMIN);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'maintenance_mode' => ['required', 'boolean'],
            'allow_user_registration' => ['required', 'boolean'],
            'feeds_default_owner_name' => ['required', 'string'],
            'protected_files_string' => ['required', 'string'],
            'feeds_default_owner_email' => ['required', 'email'],
            'default_image_id' => ['required', 'exists:images,id'],
            'support_email_address' => ['required', 'email'],
            'admin_main_address' => ['required', 'email'],
            'show_dropbox_files_in_dashboard' => ['required', 'boolean'],
            'player_show_article_link_in_player' => ['required', 'boolean'],
            'player_article_link_url' => ['required', 'url'],
            'player_article_link_text' => ['required', 'string'],
            'player_transcoding_video_file_path' => ['required', 'string'],
            'player_enable_adaptive_streaming' => ['required', 'boolean'],
            'clip_generic_poster_image_name' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'maintenance_mode' => $this->maintenance_mode === 'on',
            'allow_user_registration' => $this->allow_user_registration === 'on',
            'show_dropbox_files_in_dashboard' => $this->show_dropbox_files_in_dashboard == 'on',
            'player_show_article_link_in_player' => $this->player_show_article_link_in_player === 'on',
            'player_enable_adaptive_streaming' => $this->player_enable_adaptive_streaming === 'on',

        ]);
    }
}
