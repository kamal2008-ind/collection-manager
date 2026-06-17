<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Workspace\Index as WorkspaceIndex;
use App\Livewire\Workspace\Show as WorkspaceShow;
use App\Livewire\Collection\Index as CollectionIndex;
use App\Livewire\Collection\Show as CollectionShow;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/u/{username}/workspaces/{slug}', WorkspaceShow::class)
    ->name('workspaces.show');

Route::get('/u/{username}/collections/{slug}', CollectionShow::class)
    ->name('collections.show');
Route::middleware(['auth'])->group(function () {

    Route::get('/workspaces', WorkspaceIndex::class)
        ->name('workspaces.index');

    Route::get('/collections', CollectionIndex::class)
        ->name('collections.index');
});

require __DIR__ . '/auth.php';
