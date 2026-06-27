<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'avatar',
        'email',
        'password',
        'view_mode',
        'pagination_mode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sharedWorkspaces(): BelongsToMany
    {
        return $this->belongsToMany(
            Workspace::class,
            'workspace_shares',
            'shared_with_user_id',
            'workspace_id'
        )
            ->withPivot(['shared_by_user_id', 'permission', 'last_accessed_at'])
            ->withTimestamps();
    }

    public function workspaceSharesCreated(): HasMany
    {
        return $this->hasMany(WorkspaceShare::class, 'shared_by_user_id');
    }
}
