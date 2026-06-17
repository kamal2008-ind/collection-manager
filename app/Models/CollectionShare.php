<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionShare extends Model
{
    protected $fillable = [
        'collection_id',
        'shared_by_user_id',
        'shared_with_user_id',
        'permission',
        'last_accessed_at',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }

    public function sharedWith(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }
}
