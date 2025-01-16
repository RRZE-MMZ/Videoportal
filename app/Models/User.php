<?php

namespace App\Models;

use App\Enums\Role;
use App\Models\Traits\RecordsActivity;
use App\Models\Traits\Searchable;
use App\Notifications\MailResetPasswordToken;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use RecordsActivity;
    use Searchable;
    use SoftDeletes;

    protected array $searchable = ['first_name', 'last_name', 'username', 'email'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'logged_in_at' => 'datetime:Y-m-d H:i:s',
        'last_visit_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Series relationship
     */
    public function series(): HasMany
    {
        return $this->hasMany(Series::class, 'owner_id');
    }

    /**
     *  Subscriptions relationship
     */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Series::class, 'series_subscriptions');
    }

    public function accessableSeries(): Builder|Series
    {
        return Series::where('owner_id', $this->id)
            ->orWhereHas('members', function ($query) {
                $query->where('user_id', $this->id);
            })
            ->orderBy('updated_at');
    }

    /**
     * Clip relationship
     */
    public function clips(): HasMany
    {
        return $this->hasMany(Clip::class, 'owner_id');
    }

    public function supervisedClips(): HasMany
    {
        return $this->hasMany(Clip::class, 'supervisor_id');
    }

    public function settings(): BelongsTo
    {
        return $this->belongsTo(Setting::class, 'username', 'name');
    }

    public function presenter(): BelongsTo
    {
        return $this->belongsTo(Presenter::class);
    }

    /**
     * Comments relationship
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'owner_id');
    }

    /**
     * Channels relationship
     */
    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class, 'owner_id');
    }

    public function podcasts(): HasMany
    {
        return $this->hasMany(Podcast::class, 'owner_id');
    }

    public function podcastEpisodes(): HasMany
    {
        return $this->hasMany(PodcastEpisode::class, 'owner_id');
    }

    /*
     * Assign a role to the current use
     *
     * @return User
     */
    public function assignRole(Role $role): static
    {
        // a member cannot be a user or student
        if ($role == Role::MEMBER) {
            // therefore remove the existing user role
            $this->roles()->sync([$role->value]);
        } else {
            $this->roles()->toggle([$role->value]);
        }

        return $this;
    }

    /**
     * Roles relationship
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Role::class, 'role_user');
    }

    /**
     * Assigns multiple roles to user
     */
    public function assignRoles(Collection $rolesCollection): User
    {
        if ($rolesCollection->isNotEmpty()) {
            $this->roles()->sync(($rolesCollection));
        } else {
            $this->roles()->detach();
        }

        return $this;
    }

    /**
     * Check whether the current user is a superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPERADMIN);
    }

    /**
     * Check whether the current user has given role
     */
    public function hasRole(Role $role): bool
    {
        return $this->roles->contains('name', $role->lower());
    }

    /**
     * Check whether the current user is an admin or a superadmin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::SUPERADMIN) || $this->hasRole(Role::ADMIN);
    }

    /**
     * Check whether the current user is an editor
     */
    public function isModerator(): bool
    {
        return $this->hasRole(Role::MODERATOR);
    }

    /**
     * Check whether the current user is an assistant
     */
    public function isAssistant(): bool
    {
        return $this->hasRole(Role::ASSISTANT);
    }

    /**
     * Check whether the current user is part of a series
     */
    public function isMemberOf(Series $series): bool
    {
        return $this->memberships()->get()->contains($series);
    }

    /**
     * Series Membership relationship
     */
    public function memberships(): BelongsToMany
    {
        return $this->belongsToMany(Series::class, 'series_members')->withTimestamps();
    }

    public function getAllActivities(): HasMany
    {
        return $this->hasMany(Activity::class, 'user_id');
    }

    public function getAllSeries(): Builder|Series
    {
        return Series::select('id', 'slug', 'title', 'updated_at', 'owner_id', 'organization_id', 'created_at')
            ->whereHas('clips', function ($q) {
                $q->where('supervisor_id', $this->id);
            })
            ->orWhere('owner_id', $this->id)
            ->orWhereHas('members', function ($query) {
                $query->where('user_id', $this->id);
            });
    }

    /*
     * Fetch User settings
     */
    public function getSetting(): BaseModel
    {
        return Setting::where('name', $this->username)->firstOrCreate(
            ['name' => $this->username],
            ['data' => config('settings.user')]
        );
    }

    /**
     * Override the default mail template
     * and send a custom password reset email
     * to the user
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MailResetPasswordToken($token, $this));
    }

    /**
     * Scope users with admin roles
     *
     * @return mixed
     */
    public function scopeAdmins($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', Role::SUPERADMIN->lower())
                ->orWhere('name', Role::ADMIN->lower())
                ->orWhere('name', Role::ASSISTANT->lower());
        });
    }

    public function scopeModerators($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', Role::MODERATOR->lower());
        });
    }

    public function scopeByRole($query, Role $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role->lower());
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('expired', true);
    }

    /*
     * Only for test purpose!
     *
     */
    public function resetPassword(): JsonResponse
    {
        $this->password = Hash::make('12341234');
        $this->save();

        return response()->json([
            'status' => 200,
            'message' => 'User password reset was successfully ',
            'user' => $this->username,
        ]);
    }

    /**
     * Resets user settings to default values
     */
    public function resetSettings(): BaseModel
    {
        $settings = $this->settings;

        // set user settings to default
        $settings->data = config('settings.user');
        $settings->save();

        return $this->settings;
    }
}
