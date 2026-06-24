<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Movie;

class Workspace extends Model
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
    public function collections()
    {
        return $this->attachments()
            ->where('attachable_type', 'collection')
            ->whereHas('attachable', function ($query) {
                $query->whereNull('deleted_at');
            });
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

    public function shares(): HasMany
    {
        return $this->hasMany(WorkspaceShare::class);
    }

    public function sharedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'workspace_shares',
            'workspace_id',
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
        )->wherePivot('container_type', 'workspace')
            ->wherePivot('attachable_type', 'movie');
    }
}
