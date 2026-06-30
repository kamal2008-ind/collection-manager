<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Workspace\Index as WorkspaceIndex;
use App\Livewire\Workspace\Show as WorkspaceShow;
use App\Livewire\Collection\Index as CollectionIndex;
use App\Livewire\Collection\Show as CollectionShow;
use App\Livewire\Movie\Index as MovieIndex;
use App\Livewire\Movie\Show as MovieShow;
use App\Livewire\Book\Index as BookIndex;
use App\Livewire\Book\Show as BookShow;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');
Route::view('profile', 'profile')->middleware(['auth'])->name('profile');
Route::get('/u/{username}/workspaces/{slug}', WorkspaceShow::class)->name('workspaces.show');
Route::get('/u/{username}/collections/{slug}', CollectionShow::class)->name('collections.show');
Route::get('/u/{username}/movies/{slug}', MovieShow::class)->name('movies.show');
Route::get('/u/{username}/books/{slug}', BookShow::class)->name('books.show');
Route::middleware(['auth'])->group(function () {
    Route::get('/workspaces', WorkspaceIndex::class)->name('workspaces.index');
    Route::get('/collections', CollectionIndex::class)->name('collections.index');
    Route::get('/movies', MovieIndex::class)->name('movies.index');
    Route::get('/books', BookIndex::class)->name('books.index');
});

require __DIR__ . '/auth.php';
