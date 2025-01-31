<?php

namespace App\Models\Traits;

use App\Models\Tag;
use Illuminate\Support\Collection;

trait Taggable
{
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps();
    }

    /**
     * Assigns a document to a give type
     */
    public function addTags(Collection $tagsCollection): void
    {
        /*
         * Check for tags collection from post request.
         * The closure returns a tag model, where the model is either selected or created.
         * The tag model is synchronized with the clip tags.
         * In case the collection is empty assumed that clip has no tags and delete them
         */
        if ($tagsCollection->isNotEmpty()) {
            $this->tags()->sync($tagsCollection->map(function ($tagName) {
                return tap(Tag::firstOrCreate(['name' => $tagName]))->save();
            })->pluck('id'));
        } else {
            $this->tags()->detach();
        }
    }

    /*
     * avoid php errors in diff between series/clips tags and podcast tags
     */
    private function getNewTags(Collection $collection): Collection
    {
        foreach ($collection->toArray() as $element) {
            if (is_array($element)) {
                return $collection->sort()->keys();
            } else {
                return $collection->sort()->values();
            }
        }
    }
}
