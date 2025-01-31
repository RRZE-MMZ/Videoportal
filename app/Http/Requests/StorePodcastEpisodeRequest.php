<?php

namespace App\Http\Requests;

use App\Models\Setting;
use App\Rules\ValidFile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class StorePodcastEpisodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('edit-podcast', $this->route('podcast'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required'],
            'episode_number' => ['required', 'integer'],
            'recording_date' => ['required', 'date'],
            'tags' => ['array'],
            'tags.*' => ['string', 'nullable'],
            'description' => ['nullable', 'string', 'max:40000'],
            'notes' => ['nullable', 'string', 'max:40000'],
            'transcription' => ['nullable', 'string', 'max:40000'],
            'image' => ['string', 'nullable', new ValidFile(['image/png', 'image/jpeg'])],
            'website_url' => ['nullable', 'url'],
            'spotify_url' => ['nullable', 'url'],
            'apple_podcasts_url' => ['nullable', 'url'],
            'hosts' => ['array'],
            'guests' => ['array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $setting = Setting::portal();
        $settingData = $setting->data;
        $this->merge([
            'tags' => $this->tags = $this->tags ?? [], // set empty array if select2 tags is empty
            'image_id' => (isset($this->image_id)) ? $this->image_id : $settingData['default_image_id'],
            'hosts' => $this->hosts = $this->hosts ?? [],
            'guests' => $this->guests = $this->guests ?? [],
            'is_published' => $this->is_public === 'on',
            'slug' => Str::slug($this->title),
        ]);
    }
}
