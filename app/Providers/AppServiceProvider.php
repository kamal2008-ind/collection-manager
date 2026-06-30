<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Workspace;
use App\Models\Collection;
use App\Models\Movie;
use App\Models\User;
use App\Models\Book;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'workspace' => Workspace::class,
            'collection' => Collection::class,
            'movie' => Movie::class,
            'user' => User::class,
            'book' => Book::class,
        ]);
    }
}
