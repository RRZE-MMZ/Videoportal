<?php

namespace App\Models\Traits;

use App\Models\Presenter;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait Presentable
{
    public function presenters(): MorphToMany
    {
        return $this->morphToMany(Presenter::class, 'presentable')->withTimestamps();
    }

    public function addPresenters(Collection $presentersCollection, string $action = 'edit'): void
    {
        $existingPresenters = $this->presenters->pluck('id')->sort()->values();
        if ($presentersCollection->isEmpty()) {
            $this->presenters()->detach();
            if ($existingPresenters->isNotEmpty() && $presentersCollection->isNotEmpty()) {
                $this->recordActivity('All Presenters removed.', [
                    'before' => $this->presenters->pluck('last_name')->flatten(),
                    'after' => []]);
            }

            return;
        }

        $newPresenters = $this->getNewPresenters($presentersCollection);

        if (! $existingPresenters->diff($newPresenters)->isEmpty() ||
            ! $newPresenters->diff($existingPresenters)->isEmpty()) {
            $this->presenters()->sync($presentersCollection);
            $this->recordActivity('Presenters changed! ', [
                'before' => $existingPresenters->map(function ($value) {
                    return Presenter::find($value)->getFullNameAttribute();
                })->toArray(),
                'after' => $newPresenters->map(function ($value) {
                    return Presenter::find($value)->getFullNameAttribute();
                })->toArray(),
            ]);
        }
    }

    public function prepareAndSyncPodcastPresenters(?array $hosts, ?array $guests): void
    {
        $presenters = [];
        if (! is_null($hosts)) {
            // Assign the nearest timestamp to the host
            collect($hosts)->each(function ($host) use (&$presenters) {
                $presenters[$host] = [
                    'primary' => true,
                ];
            });
        }

        if (! is_null($guests)) {
            // Assign a later timestamp to each guest
            collect($guests)->each(function ($guest) use (&$presenters) {
                $presenters[$guest] = [
                    'primary' => false,
                ];
            });
        }

        $this->addPresenters(collect($presenters));
    }

    public function getPrimaryPresenters(bool $primary = true): Collection
    {
        return $this->presenters()->where('primary', $primary)->get();
    }

    /*
     * avoid php errors in diff between series/clips presenters and podcast presenters
     * In podcast presenters there is also a timestamp as another array to distinguish between host and guests
     */
    private function getNewPresenters(Collection $collection): Collection
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
