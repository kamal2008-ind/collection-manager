<div x-data x-show="$wire.removeItemsDrawerOpen" x-cloak class="fixed inset-0 z-[9999]">
    <div class="absolute inset-0 bg-black/40" wire:click="closeRemoveItemsDrawer"></div>

    <div class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
        <div class="flex items-center justify-between border-b bg-red-50 px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold text-red-700">
                    Remove Items from Collection
                </h2>

                @if ($removeItemsCollectionName)
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

                <select wire:model.live="removeItemsType"
                    class="w-full rounded-lg border-gray-300 focus:border-red-600 focus:ring-red-600">
                    <option value="movies">Movies</option>
                    <option value="books">Books</option>
                </select>
            </div>

            @php
                $isMovieType = $removeItemsType === 'movies';
                $isBookType = $removeItemsType === 'books';

                $itemOptions = $isBookType
                    ? collect($removeBookOptions ?? [])
                    : ($isMovieType
                        ? collect($removeMovieOptions ?? [])
                        : collect([]));

                $searchValue = $isBookType
                    ? $removeBookSearch ?? ''
                    : ($isMovieType
                        ? $removeMovieSearch ?? ''
                        : '');

                $itemLabel = $isBookType ? 'Books' : ($isMovieType ? 'Movies' : '');
                $itemLabelSingular = $isBookType ? 'book' : ($isMovieType ? 'movie' : '');

                $attachedItems = $itemOptions->filter(function ($item) use ($searchValue) {
                    if (blank($searchValue)) {
                        return true;
                    }

                    return str_contains(strtolower($item['name']), strtolower($searchValue));
                });

                $visibleAttachedItems = $attachedItems->take($drawerPerPage);
                $hasMoreAttachedItems = $attachedItems->count() > $drawerPerPage;

                $selectedCount = $isBookType
                    ? count($selectedRemoveBookIds ?? [])
                    : ($isMovieType
                        ? count($selectedRemoveMovieIds ?? [])
                        : count([]));

                $disableRemoveItems = $attachedItems->isEmpty();
            @endphp

            <div>
                <h3 class="mb-3 text-base font-semibold">
                    Attached {{ $itemLabel }}
                </h3>

                <div class="relative mb-3">
                    @if ($isBookType)
                        <input type="text" wire:model.live.debounce.300ms="removeBookSearch"
                            placeholder="Search attached books..."
                            class="w-full rounded-lg border-gray-300 pr-10 focus:border-red-600 focus:ring-red-600" />

                        @if ($removeBookSearch)
                            <button type="button" wire:click="$set('removeBookSearch', '')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700"
                                title="Clear search">
                                ✕
                            </button>
                        @endif
                    @elseif ($isMovieType)
                        <input type="text" wire:model.live.debounce.300ms="removeMovieSearch"
                            placeholder="Search attached movies..."
                            class="w-full rounded-lg border-gray-300 pr-10 focus:border-red-600 focus:ring-red-600" />

                        @if ($removeMovieSearch)
                            <button type="button" wire:click="$set('removeMovieSearch', '')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700"
                                title="Clear search">
                                ✕
                            </button>
                        @endif
                    @endif
                </div>

                @if ($visibleAttachedItems->isNotEmpty())
                    <div class="overflow-hidden rounded-lg border bg-white">
                        @foreach ($visibleAttachedItems as $item)
                            <div wire:key="remove-{{ $removeItemsType }}-{{ $item['id'] }}"
                                class="flex items-center justify-between border-b px-4 py-3 last:border-b-0 hover:bg-red-50">
                                <label class="flex cursor-pointer items-center gap-3">
                                    @if ($isBookType)
                                        <input type="checkbox" value="{{ $item['id'] }}"
                                            wire:model.live.number="selectedRemoveBookIds"
                                            class="rounded border-gray-400">
                                    @elseif ($isMovieType)
                                        <input type="checkbox" value="{{ $item['id'] }}"
                                            wire:model.live.number="selectedRemoveMovieIds"
                                            class="rounded border-gray-400">
                                    @endif

                                    <span class="font-medium">
                                        {{ $item['name'] }}
                                    </span>
                                </label>
                                @if ($isBookType)
                                    <button type="button" wire:click="detachBookFromRemoveDrawer({{ $item['id'] }})"
                                        wire:confirm="Detach this book from {{ $removeItemsCollectionName }}? This will only remove the attachment. The book will not be deleted."
                                        class="rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-100"
                                        title="Detach">
                                        ⛓️‍💥
                                    </button>
                                @elseif ($isMovieType)
                                    <button type="button"
                                        wire:click="detachMovieFromRemoveDrawer({{ $item['id'] }})"
                                        wire:confirm="Detach this movie from {{ $removeItemsCollectionName }}? This will only remove the attachment. The movie will not be deleted."
                                        class="rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-100"
                                        title="Detach">
                                        ⛓️‍💥
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if ($hasMoreAttachedItems)
                        <div x-data x-intersect="$wire.loadMoreDrawer()" class="py-4 text-center">
                            <div wire:loading wire:target="loadMoreDrawer" class="text-sm text-gray-500">
                                Loading more...
                            </div>

                            <div wire:loading.remove wire:target="loadMoreDrawer" class="text-sm text-gray-400">
                                Scroll to load more
                            </div>
                        </div>
                    @endif

                    @if (!$hasMoreAttachedItems && $visibleAttachedItems->isNotEmpty())
                        <div class="py-4 text-center text-sm text-gray-400">
                            ✓ No more {{ strtolower($itemLabel) }}
                        </div>
                    @endif
                @else
                    <p class="rounded-lg border border-dashed p-4 text-sm text-gray-500">
                        No attached {{ $itemLabelSingular }} found.
                    </p>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t bg-red-50 px-6 py-5">
            <x-ui.button variant="secondary" wire:click="closeRemoveItemsDrawer">
                Cancel
            </x-ui.button>

            <x-ui.button variant="danger" wire:click="removeSelectedItems" :disabled="$disableRemoveItems"
                wire:confirm="Remove selected item(s) from this collection? This will only remove attachments. Records will not be deleted.">
                Remove Items
                @if ($selectedCount)
                    ({{ $selectedCount }})
                @endif
            </x-ui.button>
        </div>
    </div>
</div>
