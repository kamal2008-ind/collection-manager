<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Movie;
use App\Models\Book;

class Collection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'image',
        'visibility',
        'is_favorite',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(
            Attachment::class,
            'container',
            'container_type',
            'container_id'
        );
    }
    public function attachedWorkspaces()
    {
        return $this->hasMany(Attachment::class, 'attachable_id')
            ->where('attachable_type', 'collection')
            ->where('container_type', 'workspace')
            ->whereHas('container', function ($query) {
                $query->whereNull('deleted_at');
            });
    }
    public function shares(): HasMany
    {
        return $this->hasMany(CollectionShare::class);
    }

    public function sharedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'collection_shares',
            'collection_id',
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
    public function attachedMovies()
    {
        return $this->morphedByMany(
            Movie::class,
            'attachable',
            'attachments',
            'container_id',
            'attachable_id'
        )->wherePivot('container_type', 'collection')
            ->wherePivot('attachable_type', 'movie');
    }
    public function likes(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(
            \App\Models\Like::class,
            'likeable'
        );
    }

    public function likedBy(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->likes()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function isLikedBy(?\App\Models\User $user): bool
    {
        return $this->likedBy($user);
    }
    public function attachedBooks()
    {
        return $this->morphedByMany(
            Book::class,
            'attachable',
            'attachments',
            'container_id',
            'attachable_id'
        )->wherePivot('container_type', 'collection')
            ->wherePivot('attachable_type', 'book');
    }
}
