<div x-data x-show="$wire.attachToDrawerOpen" x-cloak class="fixed inset-0 z-[9999]">
    <div class="absolute inset-0 bg-black/40" wire:click="closeAttachToDrawer"></div>

    <div class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
        <div class="flex items-center justify-between border-b bg-blue-50 px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold text-blue-700">
                    {{ $isBulkAttachMode ? 'Bulk Attach To' : 'Attach To' }}
                </h2>

                @if ($attachToMovieName)
                    <p class="mt-1 text-sm text-blue-600">
                        {{ $attachToMovieName }}
                    </p>
                @endif
            </div>

            <button type="button" wire:click="closeAttachToDrawer" class="text-2xl text-gray-500 hover:text-gray-800">
                ×
            </button>
        </div>

        <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
            <div>
                <h3 class="mb-3 text-base font-semibold">
                    Target Type
                </h3>

                <div class="grid grid-cols-2 gap-3">
                    <label
                        class="flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 hover:bg-gray-50
                            {{ $attachTargetType === 'workspace' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100' : 'bg-white' }}">
                        <input
                            type="radio"
                            value="workspace"
                            wire:model.live="attachTargetType"
                            class="border-gray-400 text-blue-600 focus:ring-blue-600"
                        >
                        <span class="font-medium">Workspace</span>
                    </label>

                    <label
                        class="flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 hover:bg-gray-50
                            {{ $attachTargetType === 'collection' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100' : 'bg-white' }}">
                        <input
                            type="radio"
                            value="collection"
                            wire:model.live="attachTargetType"
                            class="border-gray-400 text-blue-600 focus:ring-blue-600"
                        >
                        <span class="font-medium">Collection</span>
                    </label>
                </div>
            </div>

            @php
                $targetOptions = $attachTargetType === 'collection'
                    ? collect($collectionOptions ?? [])
                    : collect($workspaceOptions ?? []);

                $attachedTargetIds = $attachTargetType === 'collection'
                    ? ($attachedCollectionIds ?? [])
                    : ($attachedWorkspaceIds ?? []);

                $targetLabel = $attachTargetType === 'collection' ? 'Collections' : 'Workspaces';
                $targetLabelSingular = $attachTargetType === 'collection' ? 'collection' : 'workspace';

                $availableTargets = $targetOptions
                    ->whereNotIn('id', $attachedTargetIds)
                    ->filter(function ($target) use ($attachSearch) {
                        if (blank($attachSearch)) {
                            return true;
                        }

                        return str_contains(strtolower($target['name']), strtolower($attachSearch));
                    });

                $visibleTargets = $availableTargets->take($drawerPerPage);
                $hasMoreTargets = $availableTargets->count() > $drawerPerPage;
            @endphp

            <div>
                <h3 class="mb-3 text-base font-semibold">
                    Available {{ $targetLabel }}
                </h3>

                <div class="relative mb-3">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="attachSearch"
                        placeholder="Search available {{ strtolower($targetLabel) }}..."
                        class="w-full rounded-lg border-gray-300 pr-10 focus:border-blue-600 focus:ring-blue-600"
                    />

                    @if ($attachSearch)
                        <button
                            type="button"
                            wire:click="$set('attachSearch', '')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700"
                            title="Clear search"
                        >
                            ✕
                        </button>
                    @endif
                </div>

                @if ($availableTargets->isNotEmpty())
                    <div class="overflow-hidden rounded-lg border bg-white">
                        @foreach ($visibleTargets as $target)
                            <label
                                wire:key="available-{{ $attachTargetType }}-{{ $target['id'] }}"
                                class="flex cursor-pointer items-center gap-3 border-b px-4 py-3 last:border-b-0 hover:bg-gray-50"
                            >
                                @if ($attachTargetType === 'collection')
                                    <input
                                        type="checkbox"
                                        value="{{ $target['id'] }}"
                                        wire:model.live="selectedCollectionIds"
                                        class="rounded border-gray-400"
                                    >
                                @else
                                    <input
                                        type="checkbox"
                                        value="{{ $target['id'] }}"
                                        wire:model.live="selectedWorkspaceIds"
                                        class="rounded border-gray-400"
                                    >
                                @endif

                                <span class="font-medium">
                                    {{ $target['name'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @if ($hasMoreTargets)
                        <div x-data x-intersect="$wire.loadMoreDrawer()" class="py-4 text-center">
                            <div wire:loading wire:target="loadMoreDrawer" class="text-sm text-gray-500">
                                Loading more...
                            </div>

                            <div wire:loading.remove wire:target="loadMoreDrawer" class="text-sm text-gray-400">
                                Scroll down to load more
                            </div>
                        </div>
                    @endif

                    @if (! $hasMoreTargets && $visibleTargets->isNotEmpty())
                        <div class="py-4 text-center text-sm text-gray-400">
                            ✓ No more {{ strtolower($targetLabel) }}
                        </div>
                    @endif
                @else
                    <p class="rounded-lg border border-dashed p-4 text-sm text-gray-500">
                        No available {{ $targetLabelSingular }} to attach.
                    </p>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t bg-blue-50 px-6 py-5">
            <x-ui.button variant="secondary" wire:click="closeAttachToDrawer">
                Cancel
            </x-ui.button>

            @php
                $selectedCount = $attachTargetType === 'collection'
                    ? count($selectedCollectionIds ?? [])
                    : count($selectedWorkspaceIds ?? []);

                $disableUpdateAttachment = $availableTargets->isEmpty();
            @endphp

            <x-ui.button variant="primary" wire:click="attachToSelectedTargets" :disabled="$disableUpdateAttachment">
                Update Attachment
                @if ($selectedCount)
                    ({{ $selectedCount }})
                @endif
            </x-ui.button>
        </div>
    </div>
</div>
