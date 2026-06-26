<div x-data x-show="$wire.removeItemsDrawerOpen" x-cloak class="fixed inset-0 z-[9999]">
    <div class="absolute inset-0 bg-black/40" wire:click="closeRemoveItemsDrawer"></div>

    <div class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
        <div class="flex items-center justify-between border-b bg-red-50 px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold text-red-700">
                    Remove Items from Collection
                </h2>

                @if ($removeItemsCollectionName ?? null)
                    <p class="mt-1 text-sm text-red-600">
                        {{ $removeItemsCollectionName }}
                    </p>
                @endif
            </div>

            <button type="button" wire:click="closeRemoveItemsDrawer" class="text-2xl text-gray-500 hover:text-gray-800">
                ×
            </button>
        </div>

        <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
            <div>
                <label class="mb-2 block text-sm font-medium">
                    Item Type
                </label>

                <select
                    wire:model.live="removeItemsType"
                    class="w-full rounded-lg border-gray-300 focus:border-red-600 focus:ring-red-600"
                >
                    <option value="movies">Movies</option>
                </select>
            </div>

            @php
                $attachedMovies = collect($removeMovieOptions ?? [])->filter(function ($movie) use ($removeMovieSearch) {
                    if (blank($removeMovieSearch)) {
                        return true;
                    }

                    return str_contains(strtolower($movie['name']), strtolower($removeMovieSearch));
                });

                $visibleAttachedMovies = $attachedMovies->take($drawerPerPage);
                $hasMoreAttachedMovies = $attachedMovies->count() > $drawerPerPage;
                $disableRemoveItems = $attachedMovies->isEmpty();
            @endphp

            <div>
                <h3 class="mb-3 text-base font-semibold">
                    Attached Movies
                </h3>

                <div class="relative mb-3">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="removeMovieSearch"
                        placeholder="Search attached movies..."
                        class="w-full rounded-lg border-gray-300 pr-10 focus:border-red-600 focus:ring-red-600"
                    />

                    @if ($removeMovieSearch)
                        <button
                            type="button"
                            wire:click="$set('removeMovieSearch', '')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700"
                            title="Clear search"
                        >
                            ✕
                        </button>
                    @endif
                </div>

                @if ($visibleAttachedMovies->isNotEmpty())
                    <div class="overflow-hidden rounded-lg border bg-white">
                        @foreach ($visibleAttachedMovies as $movie)
                            <div
                                wire:key="remove-movie-{{ $movie['id'] }}"
                                class="flex items-center justify-between border-b px-4 py-3 last:border-b-0 hover:bg-red-50"
                            >
                                <label class="flex cursor-pointer items-center gap-3">
                                    <input
                                        type="checkbox"
                                        value="{{ $movie['id'] }}"
                                        wire:model.live.number="selectedRemoveMovieIds"
                                        class="rounded border-gray-400"
                                    >

                                    <span class="font-medium">
                                        {{ $movie['name'] }}
                                    </span>
                                </label>

                                <button
                                    type="button"
                                    wire:click="detachMovieFromRemoveDrawer({{ $movie['id'] }})"
                                    wire:confirm="Detach this movie from {{ $removeItemsCollectionName }}? This will only remove the attachment. The movie will not be deleted."
                                    class="rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-100"
                                    title="Detach"
                                >
                                    ⛓️‍💥
                                </button>
                            </div>
                        @endforeach
                    </div>

                    @if ($hasMoreAttachedMovies)
                        <div x-data x-intersect="$wire.loadMoreDrawer()" class="py-4 text-center">
                            <div wire:loading wire:target="loadMoreDrawer" class="text-sm text-gray-500">
                                Loading more...
                            </div>

                            <div wire:loading.remove wire:target="loadMoreDrawer" class="text-sm text-gray-400">
                                Scroll to load more
                            </div>
                        </div>
                    @endif

                    @if (! $hasMoreAttachedMovies && $visibleAttachedMovies->isNotEmpty())
                        <div class="py-4 text-center text-sm text-gray-400">
                            ✓ No more movies
                        </div>
                    @endif
                @else
                    <p class="rounded-lg border border-dashed p-4 text-sm text-gray-500">
                        No attached movies found.
                    </p>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t bg-red-50 px-6 py-5">
            <x-ui.button variant="secondary" wire:click="closeRemoveItemsDrawer">
                Cancel
            </x-ui.button>

            <x-ui.button
                variant="danger"
                wire:click="removeSelectedItems"
                :disabled="$disableRemoveItems"
                wire:confirm="Remove selected movie(s) from this collection? This will only remove attachments. Records will not be deleted."
            >
                Remove Items
                @if (count($selectedRemoveMovieIds))
                    ({{ count($selectedRemoveMovieIds) }})
                @endif
            </x-ui.button>
        </div>
    </div>
</div>
