<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MassDeleteSeriesClipsRequest extends FormRequest
{
    protected $series;

    public function authorize(): bool
    {
        return $this->user()->can('edit-series', $this->series);
    }

    public function rules(): array
    {
        return [
            'clip_ids' => ['required', 'array'],
            'clip_ids.*' => [
                'integer',
                'exists:clips,id',
                function ($attribute, $value, $fail) {
                    $clipExists = $this->series->clips()->whereId($value)->exists();
                    if (! $clipExists) {
                        $fail("The clip ID {$value} does not belong to the series {$this->series->title}.");
                    }
                }],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->series = $this->route('series');
        if (is_string($this->clip_ids)) {
            $this->merge(['clip_ids' => json_decode($this->clip_ids, true)]);
        }
    }
}
