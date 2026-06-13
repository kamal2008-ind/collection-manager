<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Workspace\Index as WorkspaceIndex;
use App\Livewire\Workspace\Show as WorkspaceShow;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/u/{username}/workspaces/{slug}', WorkspaceShow::class)
    ->name('workspaces.show');
Route::middleware(['auth'])->group(function () {

    Route::get('/workspaces', WorkspaceIndex::class)
        ->name('workspaces.index');
});

require __DIR__ . '/auth.php';
