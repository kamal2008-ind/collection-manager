<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    protected $fillable = [
        'container_type',
        'container_id',
        'attachable_type',
        'attachable_id',
        'sort_order',
    ];

    public function container(): MorphTo
    {
        return $this->morphTo(
            __FUNCTION__,
            'container_type',
            'container_id'
        );
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo(
            __FUNCTION__,
            'attachable_type',
            'attachable_id'
        );
    }
}
