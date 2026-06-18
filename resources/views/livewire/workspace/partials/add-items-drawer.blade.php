<div x-data x-show="$wire.addItemsDrawerOpen" x-cloak class="fixed inset-0 z-[9999]">
    <div class="absolute inset-0 bg-black/40" wire:click="closeAddItemsDrawer"></div>

    <div class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
        <div class="flex items-center justify-between border-b bg-green-50 px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold text-green-700">
                    Add Items to Workspace
                </h2>

                @if ($addItemsWorkspaceName)
                    <p class="mt-1 text-sm text-green-600">
                        {{ $addItemsWorkspaceName }}
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
                    class="w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600">
                    <option value="collections">Collections</option>
                </select>
            </div>

            @php
                $addedCollections = collect($collectionOptions)->whereIn('id', $addedCollectionIds);

                $availableCollections = collect($collectionOptions)
                    ->whereNotIn('id', $addedCollectionIds)
                    ->filter(function ($collection) use ($collectionSearch) {
                        if (blank($collectionSearch)) {
                            return true;
                        }

                        return str_contains(strtolower($collection['name']), strtolower($collectionSearch));
                    });

                $visibleCollections = $availableCollections->take($drawerPerPage);

                $hasMoreCollections = $availableCollections->count() > $drawerPerPage;
            @endphp

            <div>
                <h3 class="mb-3 text-base font-semibold">
                    Available Collections
                </h3>
                <div class="relative mb-3">
                    <input type="text" wire:model.live.debounce.300ms="collectionSearch"
                        placeholder="Search available collections..."
                        class="w-full rounded-lg border-gray-300 pr-10 focus:border-blue-600 focus:ring-blue-600" />

                    @if ($collectionSearch)
                        <button type="button" wire:click="$set('collectionSearch', '')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700"
                            title="Clear search">
                            ✕
                        </button>
                    @endif
                </div>
                @if ($availableCollections->isNotEmpty())
                    <div class="overflow-hidden rounded-lg border bg-white">
                        @foreach ($visibleCollections as $collection)
                            <label wire:key="available-collection-{{ $collection['id'] }}"
                                class="flex cursor-pointer items-center gap-3 border-b px-4 py-3 last:border-b-0 hover:bg-gray-50">
                                <input type="checkbox" value="{{ $collection['id'] }}"
                                    wire:model.live="selectedCollectionIds" class="rounded border-gray-400">

                                <span class="font-medium">
                                    {{ $collection['name'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    {{-- Load More Section --}}
                    @if ($hasMoreCollections)
                        <div x-data x-intersect="$wire.loadMoreDrawer()" class="py-4 text-center">
                            <div wire:loading wire:target="loadMoreDrawer" class="text-sm text-gray-500">
                                Loading more...
                            </div>

                            <div wire:loading.remove wire:target="loadMoreDrawer" class="text-sm text-gray-400">
                                Scroll down to load more
                            </div>
                        </div>
                    @endif
                    @if (!$hasMoreCollections && $visibleCollections->isNotEmpty())
                        <div class="py-4 text-center text-sm text-gray-400">
                            ✓ No more collections
                        </div>
                    @endif
                @else
                    <p class="rounded-lg border border-dashed p-4 text-sm text-gray-500">
                        No available collections to add.
                    </p>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t bg-green-50 px-6 py-5">
            <x-ui.button variant="secondary" wire:click="closeAddItemsDrawer">
                Cancel
            </x-ui.button>

            @php
                $disableAddItems = $availableCollections->isEmpty();
            @endphp

            <x-ui.button variant="success" wire:click="addSelectedItems" :disabled="$disableAddItems"
                class="bg-green-600 hover:bg-green-700">
                Add Items
                @if (count($selectedCollectionIds))
                    ({{ count($selectedCollectionIds) }})
                @endif
            </x-ui.button>
        </div>
    </div>
</div>
