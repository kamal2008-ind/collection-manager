<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'year',
        'description',
        'poster_path',
        'tmdb_id',
        'imdb_id',
        'visibility',
        'is_favorite',
        'sort_order',
    ];

    protected $casts = [
        'year' => 'integer',
        'tmdb_id' => 'integer',
        'is_favorite' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function attachedWorkspaces()
    {
        return $this->hasMany(Attachment::class, 'attachable_id')
            ->where('attachable_type', 'movie')
            ->where('container_type', 'workspace')
            ->whereHas('container', function ($query) {
                $query->whereNull('deleted_at');
            });
    }
    public function attachedCollections()
    {
        return $this->hasMany(Attachment::class, 'attachable_id')
            ->where('attachable_type', 'movie')
            ->where('container_type', 'collection')
            ->whereHas('container', function ($query) {
                $query->whereNull('deleted_at');
            });
    }
    public function shares(): HasMany
    {
        return $this->hasMany(MovieShare::class);
    }

    public function sharedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'movie_shares',
            'movie_id',
            'shared_with_user_id'
        )
            ->withPivot(['shared_by_user_id', 'permission', 'last_accessed_at'])
            ->withTimestamps();
    }

    public function isSharedWith(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->shares()
            ->where('shared_with_user_id', $user->id)
            ->exists();
    }

    public function canBeViewedBy(?User $user): bool
    {
        if ($this->visibility === 'public') {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->shares()
            ->where('shared_with_user_id', $user->id)
            ->exists();
    }
}
