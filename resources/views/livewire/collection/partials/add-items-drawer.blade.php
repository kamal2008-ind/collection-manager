<div x-data x-show="$wire.addItemsDrawerOpen" x-cloak class="fixed inset-0 z-[9999]">
    <div class="absolute inset-0 bg-black/40" wire:click="closeAddItemsDrawer"></div>

    <div class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
        <div class="flex items-center justify-between border-b bg-green-50 px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold text-green-700">
                    Add Items to Collection
                </h2>

                @if ($addItemsCollectionName)
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

                <select wire:model.live="addItemsType"
                    class="w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600">
                    <option value="movies">Movies</option>
                    <option value="books">Books</option>
                </select>
            </div>

            @php
                $isMovieType = $addItemsType === 'movies';
                $isBookType = $addItemsType === 'books';

                $itemOptions = $isBookType
                    ? collect($bookOptions ?? [])
                    : ($isMovieType
                        ? collect($movieOptions ?? [])
                        : collect([]));

                $addedItemIds = $isBookType
                    ? $addedBookIds ?? []
                    : ($isMovieType
                        ? $addedMovieIds ?? []
                        : []);

                $searchValue = $isBookType
                    ? $bookSearch ?? ''
                    : ($isMovieType
                        ? $movieSearch ?? ''
                        : '');

                $itemLabel = $isBookType ? 'Books' : ($isMovieType ? 'Movies' : '');
                $itemLabelSingular = $isBookType ? 'book' : ($isMovieType ? 'movie' : '');

                $availableItems = $itemOptions
                    ->whereNotIn('id', $addedItemIds)
                    ->filter(function ($item) use ($searchValue) {
                        if (blank($searchValue)) {
                            return true;
                        }

                        return str_contains(strtolower($item['name']), strtolower($searchValue));
                    });

                $visibleItems = $availableItems->take($drawerPerPage);
                $hasMoreItems = $availableItems->count() > $drawerPerPage;

                $selectedCount = $isBookType
                    ? count($selectedBookIds ?? [])
                    : ($isMovieType
                        ? count($selectedMovieIds ?? [])
                        : count([]));

                $disableAddItems = $availableItems->isEmpty();
            @endphp

            <div>
                <h3 class="mb-3 text-base font-semibold">
                    Available {{ $itemLabel }}
                </h3>

                <div class="relative mb-3">
                    @if ($isBookType)
                        <input type="text" wire:model.live.debounce.300ms="bookSearch"
                            placeholder="Search available books..."
                            class="w-full rounded-lg border-gray-300 pr-10 focus:border-green-600 focus:ring-green-600" />

                        @if ($bookSearch)
                            <button type="button" wire:click="$set('bookSearch', '')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700"
                                title="Clear search">
                                ✕
                            </button>
                        @endif
                    @elseif ($isMovieType)
                        <input type="text" wire:model.live.debounce.300ms="movieSearch"
                            placeholder="Search available movies..."
                            class="w-full rounded-lg border-gray-300 pr-10 focus:border-green-600 focus:ring-green-600" />

                        @if ($movieSearch)
                            <button type="button" wire:click="$set('movieSearch', '')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700"
                                title="Clear search">
                                ✕
                            </button>
                        @endif
                    @endif
                </div>

                @if ($availableItems->isNotEmpty())
                    <div class="overflow-hidden rounded-lg border bg-white">
                        @foreach ($visibleItems as $item)
                            <label wire:key="available-{{ $addItemsType }}-{{ $item['id'] }}"
                                class="flex cursor-pointer items-center gap-3 border-b px-4 py-3 last:border-b-0 hover:bg-gray-50">
                                @if ($isBookType)
                                    <input type="checkbox" value="{{ $item['id'] }}" wire:model.live="selectedBookIds"
                                        class="rounded border-gray-400">
                                @elseif ($isMovieType)
                                    <input type="checkbox" value="{{ $item['id'] }}"
                                        wire:model.live="selectedMovieIds" class="rounded border-gray-400">
                                @endif

                                <span class="font-medium">
                                    {{ $item['name'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @if ($hasMoreItems)
                        <div x-data x-intersect="$wire.loadMoreDrawer()" class="py-4 text-center">
                            <div wire:loading wire:target="loadMoreDrawer" class="text-sm text-gray-500">
                                Loading more...
                            </div>

                            <div wire:loading.remove wire:target="loadMoreDrawer" class="text-sm text-gray-400">
                                Scroll down to load more
                            </div>
                        </div>
                    @endif

                    @if (!$hasMoreItems && $visibleItems->isNotEmpty())
                        <div class="py-4 text-center text-sm text-gray-400">
                            ✓ No more {{ strtolower($itemLabel) }}
                        </div>
                    @endif
                @else
                    <p class="rounded-lg border border-dashed p-4 text-sm text-gray-500">
                        No available {{ $itemLabelSingular }} to add.
                    </p>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t bg-green-50 px-6 py-5">
            <x-ui.button variant="secondary" wire:click="closeAddItemsDrawer">
                Cancel
            </x-ui.button>

            <x-ui.button variant="success" wire:click="addSelectedItems" :disabled="$disableAddItems"
                class="bg-green-600 hover:bg-green-700">
                Add Items
                @if ($selectedCount)
                    ({{ $selectedCount }})
                @endif
            </x-ui.button>
        </div>
    </div>
</div>
