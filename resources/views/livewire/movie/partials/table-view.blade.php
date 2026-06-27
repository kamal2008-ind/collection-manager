<div class="relative rounded-xl border bg-white overflow-visible">
    <table class="w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left"></th>
                <th class="p-3 text-left w-[28%]">Movie</th>
                <th class="p-3 text-center w-[12%]">Owner</th>
                <th class="p-3 text-center w-[10%]">Year</th>
                <th class="p-3 text-center w-[12%]">TMDb</th>
                <th class="p-3 text-center w-[12%]">IMDb</th>
                <th class="p-3 text-center w-[14%]">Status</th>
                <th class="p-3 text-right w-[12%]">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($movies as $movie)
                @php
                    $isOwner = auth()->id() === $movie->user_id;
                    $movieUrl = url('/u/' . $movie->user->username . '/movies/' . $movie->slug);
                @endphp

                <tr wire:key="movie-table-{{ $movie->id }}" class="border-t hover:bg-gray-50">
                    <td class="p-3">
                        @if ($isOwner)
                            <input type="checkbox" value="{{ $movie->id }}" wire:model.live="selected">
                        @endif
                    </td>

                    <td class="p-3 font-medium" title="{{ $movie->title }}">
                        <div class="flex items-center gap-3">
                            @if ($movie->poster_path)
                                <img src="{{ asset('storage/' . $movie->poster_path) }}"
                                    class="h-10 w-10 rounded object-cover border" alt="{{ $movie->title }}">
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded border bg-gray-50">
                                    🎬
                                </div>
                            @endif

                            <span class="truncate max-w-[220px]">
                                {{ $movie->title }}
                            </span>
                        </div>
                    </td>

                    <td class="p-3 text-center">
                        <x-owner-badge :userid="$movie->user_id" :username="$movie->user->username" :view="$view" />
                    </td>

                    <td class="p-3 text-center">
                        {{ $movie->year ?: '—' }}
                    </td>

                    <td class="p-3 text-center">
                        {{ $movie->tmdb_id ?: '—' }}
                    </td>

                    <td class="p-3 text-center">
                        {{ $movie->imdb_id ?: '—' }}
                    </td>

                    <td class="p-3">
                        <div class="flex items-center justify-center gap-3">
                            <x-status-badge :visibility="$movie->visibility" :shared="($movie->shares_count ?? 0) > 0" :view="$view" />

                            <x-card-footer-meta :visibility="$movie->visibility" :assetId="$movie->id" :isOwner="$isOwner" :likeCount="$movie->likes_count ?? 0"
                                :likedByUser="$movie->isLikedBy(auth()->user())" :assetUrl="$movieUrl" />
                        </div>
                    </td>

                    <td class="p-3">
                        <div class="flex justify-end items-center gap-3">

                            @if ($isOwner)
                                <button title="{{ $movie->is_favorite ? 'Remove Favorite' : 'Add Favorite' }}"
                                    wire:click="toggleFavorite({{ $movie->id }})">
                                    @if ($movie->is_favorite)
                                        ⭐
                                    @else
                                        <span class="text-2xl">☆</span>
                                    @endif
                                </button>

                                <button title="Edit" wire:click="editMovie({{ $movie->id }})">
                                    ✏️
                                </button>

                                <button title="Move to Trash" wire:click="confirmDelete({{ $movie->id }})">
                                    🗑️
                                </button>

                                <div class="relative">
                                    <button type="button"
                                        @click.stop="activeMenu = activeMenu === 'movie-{{ $movie->id }}' ? null : 'movie-{{ $movie->id }}'"
                                        class="rounded p-1 hover:bg-gray-100" title="More actions">
                                        ⋮
                                    </button>

                                    <div x-show="activeMenu === 'movie-{{ $movie->id }}'"
                                        @click.outside="activeMenu = null" x-transition
                                        class="absolute right-0 z-[9999] mt-2 w-56 overflow-hidden rounded-xl border bg-white shadow-lg">

                                        <button type="button" wire:click="openAttachToDrawer({{ $movie->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                            <span>📎</span>
                                            <span>Attach To</span>
                                        </button>

                                        <button type="button" wire:click="openDetachFromDrawer({{ $movie->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                            <span>⛓️‍💥</span>
                                            <span>Detach From</span>
                                        </button>

                                        <div class="my-1 border-t"></div>

                                        <button type="button" wire:click="copyMovieUrl({{ $movie->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                            <span>🔗</span>
                                            <span>Copy link</span>
                                        </button>

                                        <button type="button" wire:click="movieStatistics({{ $movie->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                            <span>📊</span>
                                            <span>Statistics</span>
                                        </button>

                                        <button type="button" wire:click="movieSettings({{ $movie->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                            <span>⚙️</span>
                                            <span>Settings</span>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <span class="rounded bg-blue-50 px-2 py-1 text-xs text-blue-700">
                                    View only
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="p-6 text-center">
                        <x-empty-state icon="🎬" title="No movies found"
                            message="Try changing your search/filter/access mode or create a new movie." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
