<?php

namespace App\Models\Traits;

/*
 *  Trait copied from here
 *  https://www.twilio.com/blog/build-live-search-box-laravel-livewire-mysql
 */

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    protected function scopeSearch(Builder $query, string $terms): Builder
    {
        $terms = $this->lowerCaseTerm($terms);
        $columns = collect($this->searchable);

        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query, $columns) {
            $term = '%'.$term.'%';
            $query->where(function ($query) use ($term, $columns) {
                $columns->each(function ($column) use ($term, $query) {
                    $query->orWhereRaw('lower('.$column.') like (?)', [$term]);
                });
            });
        });

        return $query;
    }

    private function lowerCaseTerm($term): array|string
    {
        // '-' was removed cause of the livestreams opencast rooms search containing also '-'
        $term = str_replace(['+', '<', '>', '(', ')', '~'], '', $term);

        return trim(str($term)->lower());
    }
}
