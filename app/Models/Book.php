<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'year',
        'author',
        'description',
        'cover_image',
        'isbn',
        'publisher',
        'visibility',
        'is_favorite',
        'sort_order',
    ];

    protected $casts = [
        'year' => 'integer',
        'is_favorite' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Book $book) {
            if (blank($book->slug)) {
                $book->slug = static::makeUniqueSlug($book->title, $book->user_id);
            }
        });

        static::updating(function (Book $book) {
            if ($book->isDirty('title')) {
                $book->slug = static::makeUniqueSlug($book->title, $book->user_id, $book->id);
            }
        });
    }

    public static function makeUniqueSlug(string $title, int $userId, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 2;

        while (
            static::where('user_id', $userId)
            ->where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // public function attachments()
    // {
    //     return $this->morphMany(Attachment::class, 'attachable');
    // }

    // public function scopeSearch(Builder $query, ?string $search): Builder
    // {
    //     return $query->when($search, function ($query) use ($search) {
    //         $query->where(function ($query) use ($search) {
    //             $query->where('title', 'like', "%{$search}%")
    //                 ->orWhere('author', 'like', "%{$search}%")
    //                 ->orWhere('isbn', 'like', "%{$search}%")
    //                 ->orWhere('publisher', 'like', "%{$search}%");
    //         });
    //     });
    // }

    public function attachedWorkspaces()
    {
        return $this->hasMany(Attachment::class, 'attachable_id')
            ->where('attachable_type', 'book')
            ->where('container_type', 'workspace')
            ->whereHas('container', function ($query) {
                $query->whereNull('deleted_at');
            });
    }
    public function attachedCollections()
    {
        return $this->hasMany(Attachment::class, 'attachable_id')
            ->where('attachable_type', 'book')
            ->where('container_type', 'collection')
            ->whereHas('container', function ($query) {
                $query->whereNull('deleted_at');
            });
    }
    public function shares(): HasMany
    {
        return $this->hasMany(BookShare::class);
    }

    public function sharedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'book_shares',
            'book_id',
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
}
