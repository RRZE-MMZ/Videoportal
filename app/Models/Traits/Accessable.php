<?php

namespace App\Models\Traits;

use App\Enums\Acl as AclEnum;
use App\Models\Acl;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function auth;

trait Accessable
{
    /**
     * Assign an acl collection to the give type
     */
    public function addAcls(Collection $aclsCollection): void
    {
        $existingACLS = $this->acls()->get()->pluck('id')->sort()->values();
        $newACLS = $aclsCollection->sort()->values();
        if ($aclsCollection->isEmpty()) {
            $this->acls()->detach();
            // do not record if nothing changed
            if ($existingACLS->diff($newACLS)->isNotEmpty()) {
                $this->recordActivity('All ACLs detached.');
            }

            return;
        }
        if (! $existingACLS->diff($newACLS)->isEmpty() || ! $newACLS->diff($existingACLS)->isEmpty()) {
            $this->acls()->sync($aclsCollection);
            $this->recordActivity('ACL changed! ', [
                'before' => $existingACLS->map(function ($value) {
                    return AclEnum::from($value)->lower();
                })->toArray(),
                'after' => $newACLS->map(function ($value) {
                    return AclEnum::from($value)->lower();
                })->toArray(),
            ]);
        }
    }

    /**
     * Given model acls relationship
     */
    public function acls(): MorphToMany
    {
        return $this->morphToMany(Acl::class, 'accessable')->withTimestamps();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function checkAcls(): bool
    {
        $acls = $this->acls;

        if ($acls->isEmpty() || $acls->pluck('id')->contains(AclEnum::PUBLIC())) {
            return true;
        }

        if (auth()->user()?->isAdmin()) {
            return true;
        }

        if ($acls->pluck('id')->contains(AclEnum::PORTAL()) && auth()->check()) {
            return ($this->assets()->count() > 0 && $this->is_public)
                || auth()->user()->can('view-video', $this);
        }
        if ($acls->pluck('id')->contains(AclEnum::LMS())
            || $acls->pluck('id')->contains(AclEnum::PASSWORD())) {
            return
                 checkAccessToken($this)
                 || ((auth()->check() && auth()->user()->can('view-video', $this)))
                 || (auth()->check() && auth()->user()->isAdmin());
        }

        return false;
    }
}
