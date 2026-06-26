<div x-data x-show="$wire.addItemsDrawerOpen" x-cloak class="fixed inset-0 z-[9999]">
    <div class="absolute inset-0 bg-black/40" wire:click="closeAddItemsDrawer"></div>

    <div class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
        <div class="flex items-center justify-between border-b bg-green-50 px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold text-green-700">
                    Add Items to Collection
                </h2>

                @if ($addItemsCollectionName ?? null)
                    <p class="mt-1 text-sm text-green-600">
                        {{ $addItemsCollectionName }}
                    </p>
                @endif
            </div>

            <button type="button" wire:click="closeAddItemsDrawer" class="text-2xl text-gray-500 hover:text-gray-800">
                ×
            </button>
        </div>

        <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
            <div>
                <label class="mb-2 block text-sm font-medium">
                    Item Type
                </label>

                <select
                    wire:model.live="addItemsType"
                    class="w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600"
                >
                    <option value="movies">Movies</option>
                </select>
            </div>

            @php
                // $addedMovies = collect($movieOptions ?? [])->whereIn('id', $addedMovieIds ?? []);

                $availableMovies = collect($movieOptions ?? [])
                    ->whereNotIn('id', $addedMovieIds ?? [])
                    ->filter(function ($movie) use ($movieSearch) {
                        if (blank($movieSearch)) {
                            return true;
                        }

                        return str_contains(strtolower($movie['name']), strtolower($movieSearch));
                    });

                $visibleMovies = $availableMovies->take($drawerPerPage);
                $hasMoreMovies = $availableMovies->count() > $drawerPerPage;
                $disableAddItems = $availableMovies->isEmpty();
            @endphp

            {{-- @if ($addedMovies->isNotEmpty())
                <div>
                    <h3 class="mb-3 text-base font-semibold">
                        Already Added Movies
                    </h3>

                    <div class="overflow-hidden rounded-lg border bg-white">
                        @foreach ($addedMovies as $movie)
                            <div
                                wire:key="already-added-movie-{{ $movie['id'] }}"
                                class="flex items-center justify-between border-b px-4 py-3 last:border-b-0 bg-green-50"
                            >
                                <span class="font-medium">
                                    {{ $movie['name'] }}
                                </span>

                                <button
                                    type="button"
                                    wire:click="detachMovieItem({{ $movie['id'] }})"
                                    wire:confirm="Detach this movie from {{ $addItemsCollectionName }}? This will only remove the attachment. The movie will not be deleted."
                                    class="rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-100"
                                    title="Detach"
                                >
                                    ⛓️‍💥
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif --}}

            <div>
                <h3 class="mb-3 text-base font-semibold">
                    Available Movies
                </h3>

                <div class="relative mb-3">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="movieSearch"
                        placeholder="Search available movies..."
                        class="w-full rounded-lg border-gray-300 pr-10 focus:border-green-600 focus:ring-green-600"
                    />

                    @if ($movieSearch)
                        <button
                            type="button"
                            wire:click="$set('movieSearch', '')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700"
                            title="Clear search"
                        >
                            ✕
                        </button>
                    @endif
                </div>

                @if ($availableMovies->isNotEmpty())
                    <div class="overflow-hidden rounded-lg border bg-white">
                        @foreach ($visibleMovies as $movie)
                            <label
                                wire:key="available-movie-{{ $movie['id'] }}"
                                class="flex cursor-pointer items-center gap-3 border-b px-4 py-3 last:border-b-0 hover:bg-gray-50"
                            >
                                <input
                                    type="checkbox"
                                    value="{{ $movie['id'] }}"
                                    wire:model.live="selectedMovieIds"
                                    class="rounded border-gray-400"
                                >

                                <span class="font-medium">
                                    {{ $movie['name'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @if ($hasMoreMovies)
                        <div x-data x-intersect="$wire.loadMoreDrawer()" class="py-4 text-center">
                            <div wire:loading wire:target="loadMoreDrawer" class="text-sm text-gray-500">
                                Loading more...
                            </div>

                            <div wire:loading.remove wire:target="loadMoreDrawer" class="text-sm text-gray-400">
                                Scroll down to load more
                            </div>
                        </div>
                    @endif

                    @if (! $hasMoreMovies && $visibleMovies->isNotEmpty())
                        <div class="py-4 text-center text-sm text-gray-400">
                            ✓ No more movies
                        </div>
                    @endif
                @else
                    <p class="rounded-lg border border-dashed p-4 text-sm text-gray-500">
                        No available movies to add.
                    </p>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t bg-green-50 px-6 py-5">
            <x-ui.button variant="secondary" wire:click="closeAddItemsDrawer">
                Cancel
            </x-ui.button>

            <x-ui.button
                variant="success"
                wire:click="addSelectedItems"
                :disabled="$disableAddItems"
                class="bg-green-600 hover:bg-green-700"
            >
                Add Items
                @if (count($selectedMovieIds))
                    ({{ count($selectedMovieIds) }})
                @endif
            </x-ui.button>
        </div>
    </div>
</div>
