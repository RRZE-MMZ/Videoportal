<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends BaseModel
{
    use Searchable;

    // search columns for searchable trait
    protected array $searchable = ['name'];

    public function series(): MorphToMany
    {
        return $this->morphedByMany(Series::class, 'taggable');
    }

    public function clips(): MorphToMany
    {
        return $this->morphedByMany(Clip::class, 'taggable');
    }

    public function podcasts(): MorphToMany
    {
        return $this->morphedByMany(Podcast::class, 'taggable');
    }

    public function podcastEpisodes(): MorphToMany
    {
        return $this->morphedByMany(PodcastEpisode::class, 'taggable');
    }
}
