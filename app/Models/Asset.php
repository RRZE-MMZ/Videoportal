<?php

namespace App\Models;

use App\Enums\Content;
use App\Events\AssetDeleted;
use App\Models\Stats\AssetGeoCount;
use App\Models\Stats\AssetViewCount;
use App\Models\Stats\AssetViewLog;
use App\Models\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class Asset extends BaseModel
{
    use HasFactory;
    use RecordsActivity;

    protected $guarded = [];

    //this will update clips timestamp
    protected $touches = ['clips'];

    //remove asset from disk on delete
    protected $dispatchesEvents = [
        'deleted' => AssetDeleted::class,
    ];

    protected $dates = [
        'converted_for_downloading_at',
        'converted_for_streaming_at',
    ];

    /**
     * Return asset duration in hh:mm:ss format
     */
    public function durationToHours(): string
    {
        return gmdate('H:i:s', $this->duration);
    }

    /**
     *  Clip relationship
     */
    public function clips(): MorphToMany
    {
        return $this->morphedByMany(Clip::class, 'assetable');
    }

    public function podcastEpisodes(): MorphToMany
    {
        return $this->morphedByMany(PodcastEpisode::class, 'assetable');
    }

    public function viewLog(): HasMany
    {
        return $this->hasMany(AssetViewLog::class, 'id', 'resource_id');
    }

    public function viewCount(): HasMany
    {
        return $this->hasMany(AssetViewCount::class, 'resourceid');
    }

    public function geoCount(): HasMany
    {
        return $this->hasMany(AssetGeoCount::class, 'resourceid');
    }

    public function downloadPath(): string
    {
        return Storage::disk('videos')->path($this->path);
    }

    public function scopeFormatVideo($query): mixed
    {
        return $query->where('type', Content::PRESENTER())
            ->orWhere('type', Content::PRESENTATION())
            ->orWhere('type', Content::COMPOSITE());
    }

    public function scopeFormatAudio($query): mixed
    {
        return $query->where('type', Content::AUDIO());
    }

    public function scopeFormatSmil($query): mixed
    {
        return $query->where('type', Content::SMIL);
    }
}
