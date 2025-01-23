<?php

namespace App\Models\Traits;

use App\Enums\Content;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Assetable
{
    public function addAsset(Asset $asset): Asset
    {
        $this->assets()->attach($asset);

        if ($this->getTable() === 'clips') {
            $this->has_video_assets = true;
            $this->save();
        }

        return $asset;
    }

    public function assets(): MorphToMany
    {
        return $this->morphToMany(Asset::class, 'assetable')->withTimestamps();
    }

    /**
     * Fetch all assets for a clip by type
     */
    public function getAssetsByType(Content $content): MorphToMany
    {
        return $this->assets()->where(function ($q) use ($content) {
            $q->where('type', $content());
        });
    }

    public function hasVideoAsset(): bool
    {
        return $this->assets->filter(function ($asset) {
            return ($asset->type == Content::PRESENTATION() ||
                    $asset->type == Content::PRESENTER() ||
                    $asset->type == Content::COMPOSITE() ||
                    $asset->type == Content::SLIDES()
            ) && ! $asset->is_deleted;
        })->isNotEmpty();
    }

    public function hasAudioAsset(): bool
    {
        return $this->assets->filter(function ($asset) {
            return $asset->type == Content::AUDIO && ! $asset->is_deleted;
        })->isNotEmpty();
    }

    /**
     * Return caption asset for the clip
     */
    public function getCaptionAsset(): ?Asset
    {
        return $this->assets->filter(function ($asset) {
            return $asset->type == Content::CC() && ! $asset->is_deleted;
        })->first();
    }

    public function latestAsset(): Model|MorphToMany|null
    {
        return $this->assets()
            ->orderByDesc('width')
            ->orderByDesc('type')
            ->limit(1)->first();
        //        return $this->assets()->first();
    }

    public function listAssetsByType()
    {
        return $this->assets()
            ->get()
            ->pluck('type')
            ->unique()
            ->sort()
            ->values();

    }
}
