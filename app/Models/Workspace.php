<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
            ->where('attachable_type', 'collection');
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
}
